<?php

namespace ModulesGarden\TSSGGSModule\App\Pages\Admin;

use ModulesGarden\TSSGGSModule\App\Kernel\Kernel;
use ModulesGarden\TSSGGSModule\App\Kernel\Page;
use ModulesGarden\TSSGGSModule\App\Kernel\Response;
use ModulesGarden\TSSGGSModule\App\Kernel\Table;
use ModulesGarden\TSSGGSModule\App\Libs\Products\CatalogueList;
use ModulesGarden\TSSGGSModule\App\Libs\Products\CatalogueSync;
use ModulesGarden\TSSGGSModule\App\Libs\Products\ProductAddons;
use ModulesGarden\TSSGGSModule\App\Libs\Products\ProductCatalog;
use ModulesGarden\TSSGGSModule\App\Libs\Products\ProductEditor;
use ModulesGarden\TSSGGSModule\App\Libs\Products\ProductImport;
use ModulesGarden\TSSGGSModule\App\Libs\Support\Edition;
use ModulesGarden\TSSGGSModule\App\Libs\Support\GeneralSettings;
use ModulesGarden\TSSGGSModule\App\Libs\Support\Helpers;
use WHMCS\Database\Capsule;

/**
 * Admin page "Products": the vendor's products, which of them are imported into WHMCS and in what state; import
 * (one or many), the configuration dialog of an imported product, hide / retire / fix options / cancel renewals /
 * sell as addon, re-sync, and the list of catalogue changes.
 * The list (#tssggs-products: message, filters, table) is what every change puts back.
 * Templates: resources/templates/admin/products.
 */
class Products extends Page
{
    const LANG  = 'admin.products';
    const TABLE = 'index.data_tables.products.array_data_provider.data_table.';
    const FORMS = 'index.data_tables.products.forms.';

    const BADGE_TONES = ['imported' => 'success', 'not_imported' => 'default', 'hidden' => 'warning', 'retired' => 'danger', 'removed' => 'danger', 'options' => 'warning'];

    /** actions of the Manage column that ask first: action => text of the question */
    const CONFIRM = ['retire' => 'confirm_retire', 'cancelRenewals' => 'confirm_cancel_renewals', 'sellAsAddon' => 'confirm_sell_as_addon'];

    public function index()
    {
        $list = $this->listData();
        if($this->request->isHtmx())
        {
            // the table reloading itself, or a filter
            return $this->request->string('table') !== ''
                ? $this->fragment('admin/partials/table', ['table' => $list['table']])
                : $this->fragment('admin/products/list', $list);
        }

        $other = Edition::connectedVendor() === '' ? Edition::otherVendor() : '';

        return $this->view('admin/products/index', $list + [
            'tab'          => $this->request->oneOf('tab', ['products', 'changes'], 'products'),
            'connect'      => $other,
            'apiSettingsUrl'      => self::pageUrl('settings', 'index', ['settings-page' => 'apiSettings']),
            'productsSettingsUrl' => self::pageUrl('settings', 'index', ['settings-page' => 'productsSettings']),
            'showAbout'    => !GeneralSettings::enabled('hideInfoMessages'),
            'aboutIntro'   => Edition::connectedVendor() !== '' ? $this->t('about_intro_connected', ['brand' => Edition::brandOf(Edition::connectedVendor())]) : $this->t('about_intro'),
            'aboutFix'     => Edition::connectedVendor() !== '' ? 'about_fix_connected' : 'about_fix',
        ] + $this->changesData());
    }

    /* ---- configuration dialog of an imported product ---- */

    /** GET id (of the vendor product): the dialog */
    public function edit()
    {
        $product = ProductEditor::load($this->request->int('id'));
        if(!$product)
        {
            return $this->fragment('admin/products/notice', ['text' => $this->t('not_imported')]);
        }

        return $this->fragment('admin/products/edit', ['product' => $product, 'error' => '', 'maxRecurring' => ProductEditor::RECURRING_MAX_MONTHS]);
    }

    public function postSave()
    {
        $id   = $this->request->int('id');
        $form = [];
        foreach(['productName', 'description', 'autoSetup', 'payType', 'months', 'margin', 'processingGuide', 'issuedMessage', 'includedSan', 'includedWildcard'] as $field)
        {
            if($this->request->input($field) !== null)
            {
                $form[$field] = (string)$this->request->input($field);
            }
        }
        foreach(['hidden', 'enableSan', 'enableWildcard'] as $switch)
        {
            $form[$switch] = $this->request->string($switch) === 'on';
        }

        try
        {
            ProductEditor::save($id, $form);
        }
        catch(\Throwable $e)
        {
            $error   = $e->getMessage() === 'monthsRecurringTooLong'
                ? $this->t('index.data_tables.products.providers.configuration_provider.monthsRecurringTooLong', ['max' => ProductEditor::RECURRING_MAX_MONTHS])
                : ($e->getMessage() === 'notImported' ? $this->t('not_imported') : $e->getMessage());
            $product = ProductEditor::load($id);
            if(!$product)
            {
                return $this->fragment('admin/products/notice', ['text' => $error])->intoDialog();
            }

            // the dialog again, with what was typed
            return $this->fragment('admin/products/edit', ['product' => $form + $product, 'error' => $error, 'maxRecurring' => ProductEditor::RECURRING_MAX_MONTHS])->intoDialog();
        }
        $this->flash($this->t(self::FORMS . 'configuration_form.update_success'));

        return $this->changed()->closesDialog();
    }

    /* ---- import ---- */

    /** GET ids[]: the import dialog for the ticked products */
    public function import()
    {
        $ids = $this->ids();
        if(!$ids)
        {
            return $this->fragment('admin/products/notice', ['text' => $this->t('select_first')]);
        }

        return $this->fragment('admin/products/import', $this->importData($ids, ''));
    }

    /** POST ids (comma separated), productGroup, productGroupName, rate, percent, profitMargin */
    public function postImport()
    {
        $ids   = $this->ids();
        $group = $this->request->string('productGroup');
        try
        {
            if(!$ids)
            {
                throw new \RuntimeException($this->t('select_first'));
            }
            if($group === '' || ($group === 'new' && $this->request->string('productGroupName') === ''))
            {
                throw new \RuntimeException($this->t('choose_group'));
            }
            $margin = $this->request->string('percent') === 'on' ? (float)str_replace(',', '.', $this->request->string('profitMargin')) : 0.0;
            ProductImport::run($ids, $group === 'new' ? 'new' : (int)$group, $this->request->string('productGroupName'),
                (float)str_replace(',', '.', $this->request->string('rate', '1')) ?: 1.0, $margin);
        }
        catch(\Throwable $e)
        {
            $key   = self::FORMS . 'import_form.' . $e->getMessage();
            $error = $this->lang->has(static::LANG . '.' . $key) ? $this->t($key) : $e->getMessage();

            return $this->fragment('admin/products/import', $this->importData($ids, $error))->intoDialog();
        }
        $this->flash($this->t(self::FORMS . 'import_form.create_success'));

        return $this->changed()->closesDialog();
    }

    public function postResync()
    {
        $this->attempt(function() {
            Helpers::reSyncProducts(true);
        }, $this->t(self::FORMS . 're_sync_form.create_success'));

        return $this->changed();
    }

    /* ---- the Manage column (POST pid) ---- */

    public function postHide()
    {
        return $this->manage('hidden', function($pid) {
            ProductCatalog::setHidden($pid, true);
        });
    }

    public function postShow()
    {
        return $this->manage('shown', function($pid) {
            ProductCatalog::setHidden($pid, false);
        });
    }

    public function postRetire()
    {
        return $this->manage('retired', function($pid) {
            ProductCatalog::setRetired($pid, true);
        });
    }

    public function postUnretire()
    {
        return $this->manage('unretired', function($pid) {
            ProductCatalog::setRetired($pid, false);
        });
    }

    public function postFixOptions()
    {
        return $this->manage('options_fixed', function($pid) {
            $product = Capsule::table('tblproducts')->where('id', $pid)->first(['configoption1', 'configoption2']);
            $remote  = $product ? Capsule::table(Edition::table('RemoteProducts'))->where('vendor', $product->configoption1)->where('remoteId', $product->configoption2)->value('rawData') : null;
            if(!$remote)
            {
                throw new \RuntimeException('The ' . Edition::brandOf($product->configoption1 ?? '') . ' product of this product is not synchronized');
            }

            return ProductCatalog::fixConfigurableOptions($pid, json_decode($remote, true) ?: []);
        });
    }

    public function postCancelRenewals()
    {
        return $this->manage('renewals_cancelled', function($pid) {
            return ProductCatalog::cancelAllRenewals($pid, 'Product discontinued');
        });
    }

    /**
     * Sells the product as a WHMCS product addon: the addon is created and opened in WHMCS, where the admin picks
     * the products it is offered with
     */
    public function postSellAsAddon()
    {
        $addonId  = 0;
        $response = $this->manage('', function($pid) use (&$addonId) {
            if(ProductAddons::addonsOf($pid))
            {
                throw new \RuntimeException('This product is already sold as a product addon');
            }
            $refused = ProductAddons::refusal($pid);
            if($refused !== '')
            {
                throw new \RuntimeException($refused);
            }
            $addonId = ProductAddons::create($pid);
        });

        // WHMCS's addon page opens next
        return $addonId ? Response::redirect('configaddons.php?action=manage&id=' . $addonId) : $response;
    }

    /** One action on an imported product: $notice names the message (notice_<name>), '' for none */
    protected function manage(string $notice, callable $action): Response
    {
        $this->attempt(function() use ($notice, $action) {
            $pid  = $this->request->int('pid');
            $name = (string)Capsule::table('tblproducts')->where('id', $pid)->where('servertype', Kernel::module())->value('name');
            if($name === '')
            {
                throw new \RuntimeException('Product not found');
            }
            $result = $action($pid);
            if($notice !== '')
            {
                $this->flash($this->t('index.products_container.notice_' . $notice, ['product' => $name, 'detail' => is_array($result) ? implode(', ', $result) : (string)$result]));
            }
        });

        return $this->changed();
    }

    /** After a change: the list again (htmx) or back to the page */
    protected function changed(): Response
    {
        return $this->request->isHtmx() ? $this->fragment('admin/products/list', $this->listData()) : $this->redirect();
    }

    /** @return int[] the ticked vendor products: ids[] of the list, or ids as one comma separated value (the dialog) */
    protected function ids(): array
    {
        $ids = $this->request->input('ids', []);
        $ids = is_array($ids) ? $ids : explode(',', (string)$ids);

        return array_values(array_unique(array_filter(array_map('intval', $ids))));
    }

    protected function importData(array $ids, string $error): array
    {
        $groups = [];
        foreach(Capsule::table('tblproductgroups')->orderBy('order')->get(['id', 'name']) as $group)
        {
            $groups[(int)$group->id] = (string)$group->name;
        }

        return [
            'ids'             => implode(',', $ids),
            'count'           => count($ids),
            'groups'          => $groups,
            'group'           => $this->request->string('productGroup'),
            'groupName'       => $this->request->string('productGroupName'),
            'percent'         => $this->request->string('percent') === 'on',
            'margin'          => $this->request->string('profitMargin', '1'),
            'rate'            => $this->request->string('rate'),
            // without a USD currency in WHMCS the admin gives the rate from USD to the default currency
            'needsRate'       => !Helpers::getCurrencyIdByCode('USD'),
            'defaultCurrency' => Helpers::getDefaultCurrencyCode(),
            'error'           => $error,
        ];
    }

    /** The message, the two filters with their counts and the table */
    protected function listData(): array
    {
        $show      = $this->request->oneOf('show', CatalogueList::SHOW);
        $type      = $this->request->oneOf('type', CatalogueList::TYPES);
        $catalogue = CatalogueList::catalogue();

        $groups = [
            'show' => ['' => 'show_all', 'imported' => 'show_imported', 'not_imported' => 'show_not_imported'],
            'type' => ['' => 'type_all', 'dv' => 'type_dv', 'ov' => 'type_ov', 'ev' => 'type_ev', 'other' => 'type_other'],
        ];
        $filters = [];
        foreach($groups as $group => $choices)
        {
            $filters[$group] = ['label' => $this->t($group . '_label'), 'choices' => []];
            foreach($choices as $value => $key)
            {
                $value = (string)$value;
                // each count follows the other group's current choice
                $count = 0;
                foreach($catalogue as [$remoteProduct, $whmcsProduct])
                {
                    $count += CatalogueList::passes($remoteProduct, $whmcsProduct, $group === 'show' ? $value : $show, $group === 'type' ? $value : $type) ? 1 : 0;
                }
                $current = $value === ($group === 'show' ? $show : $type);
                if($value === 'other' && !$count && !$current)
                {
                    continue;
                }
                $filters[$group]['choices'][] = [
                    'text'    => $this->t($key),
                    'count'   => $count,
                    'current' => $current,
                    // a link keeps the other group's choice
                    'url'     => $this->url('index', ['show' => $group === 'show' ? $value : $show, 'type' => $group === 'type' ? $value : $type]),
                ];
            }
        }

        $rows = [];
        foreach($catalogue as [$remoteProduct, $whmcsProduct])
        {
            if(!CatalogueList::passes($remoteProduct, $whmcsProduct, $show, $type))
            {
                continue;
            }
            $badges = [];
            foreach(ProductCatalog::badges((array)$remoteProduct->rawData, $whmcsProduct) as $badge)
            {
                $badges[] = ['text' => $this->t(self::TABLE . 'badge_' . $badge), 'tone' => self::BADGE_TONES[$badge] ?? 'default'];
            }
            $rows[] = [
                'id'          => (int)$remoteProduct->id,
                'productName' => html_entity_decode((string)$remoteProduct->name, ENT_QUOTES, 'UTF-8'),
                'brand'       => html_entity_decode((string)$remoteProduct->brand, ENT_QUOTES, 'UTF-8'),
                'validation'  => (string)$remoteProduct->validation,
                'provider'    => Helpers::vendorToDisplay($remoteProduct->vendor),
                'billing'     => $whmcsProduct ? $this->t(self::TABLE . 'billing_' . ($whmcsProduct->paytype ?: 'recurring')) : '-',
                'status'      => implode(' ', array_column($badges, 'text')),
                'badges'      => $badges,
                'imported'    => (bool)$whmcsProduct,
                'pid'         => $whmcsProduct ? (int)$whmcsProduct->id : 0,
                'actions'     => $whmcsProduct ? $this->actions($whmcsProduct, (bool)ProductCatalog::optionProblems((int)$whmcsProduct->id, (array)$remoteProduct->rawData)) : [],
                'addons'      => $whmcsProduct ? ProductAddons::addonsOf((int)$whmcsProduct->id) : [],
            ];
        }

        $t     = function($key) {
            return $this->t(self::TABLE . $key);
        };
        $table = Table::make($this->request, 'products')->perPage([10, 25])
            ->column('id', $t('id'), true, Table::SEARCH_INT)
            ->column('productName', $t('productName'), true, Table::SEARCH_TEXT)
            ->column('brand', $t('brand'), true, Table::SEARCH_TEXT)
            ->column('validation', $t('validation'), true, Table::SEARCH_TEXT)
            ->column('provider', $t('provider'), true, Table::SEARCH_TEXT)
            ->column('billing', $t('billing'), true, Table::SEARCH_TEXT)
            ->column('status', $t('status'), true, Table::SEARCH_TEXT)
            ->column('manage', $t('manage'))
            ->rowTemplate('admin/products/row', true)->toolbar('admin/products/toolbar')->selectable('admin/products/mass')
            ->keep(['show' => $show, 'type' => $type])
            ->rows($rows);

        return ['table' => $table, 'filters' => $filters, 'flash' => $this->takeFlash(), 'show' => $show, 'type' => $type];
    }

    /** @return array[] the links of the Manage column: action, text, question to ask first ('' = none) */
    protected function actions($whmcsProduct, bool $optionsNeedFix): array
    {
        $names = [];
        if(!$whmcsProduct->retired)
        {
            $names[] = $whmcsProduct->hidden ? 'show' : 'hide';
        }
        $names[] = $whmcsProduct->retired ? 'unretire' : 'retire';
        if($optionsNeedFix)
        {
            // the "Check options" badge: only then is there something to fix
            $names[] = 'fixOptions';
        }
        $names[] = 'cancelRenewals';
        // sold as a WHMCS product addon: a link per such addon (row.tpl), or the action that creates one
        if(!ProductAddons::addonsOf((int)$whmcsProduct->id) && !$whmcsProduct->retired && ProductAddons::refusal((int)$whmcsProduct->id) === '')
        {
            $names[] = 'sellAsAddon';
        }

        $actions = [];
        foreach($names as $name)
        {
            $actions[] = [
                'action'  => $name,
                'text'    => $this->t(self::TABLE . 'action_' . $name),
                'confirm' => isset(self::CONFIRM[$name]) ? $this->t(self::TABLE . self::CONFIRM[$name]) : '',
            ];
        }

        return $actions;
    }

    /** The "Catalogue changes" tab: what changed in the vendor's product list, newest first */
    protected function changesData(): array
    {
        $labels  = CatalogueSync::typeLabels();
        $week    = date('Y-m-d H:i:s', strtotime('-7 days'));
        $recent  = 0;
        $changes = [];
        foreach(CatalogueSync::recent() as $row)
        {
            $recent   += $row->created_at >= $week ? 1 : 0;
            $changes[] = [
                'date'    => substr((string)$row->created_at, 0, 16),
                'type'    => (string)$row->type,
                'label'   => (string)($labels[$row->type] ?? $row->type),
                'product' => (string)($row->product_name !== '' ? $row->product_name : $row->remote_id),
                'pid'     => (int)$row->whmcs_pid,
                'vendor'  => (string)$row->vendor,
                'field'   => $row->field !== '' ? (string)CatalogueSync::fieldLabel($row->field) : '',
                'before'  => (string)$row->old_value,
                'now'     => (string)$row->new_value,
                'action'  => (string)$row->action,
            ];
        }

        return [
            'changes'       => $changes,
            'recentChanges' => $recent,
            'changeDays'    => CatalogueSync::TAB_DAYS,
            'syncMode'      => $this->lang->get('admin.settings.index.providers.products_settings_provider.catalogueSync_' . CatalogueSync::mode()),
        ];
    }
}
