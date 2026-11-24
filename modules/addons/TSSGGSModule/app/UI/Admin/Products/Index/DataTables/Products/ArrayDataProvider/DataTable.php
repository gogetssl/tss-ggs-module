<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Products\Index\DataTables\Products\ArrayDataProvider;


use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\Libs\ProductCatalog;

use ModulesGarden\TSSGGSModule\App\Models\RemoteProduct;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Products\Index\DataTables\Products\Modals\ImportModal;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Products\Index\DataTables\Products\Modals\ConfigurationModal;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Products\Index\DataTables\Products\Modals\ReSyncModal;
use ModulesGarden\TSSGGSModule\Components\Button\Button;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonPrimary;
use ModulesGarden\TSSGGSModule\Components\Button\ButtonSuccess;
use ModulesGarden\TSSGGSModule\Components\DataTable\Column;
use ModulesGarden\TSSGGSModule\Components\IconButton\IconButton;
use ModulesGarden\TSSGGSModule\Components\IconButton\IconButtonEdit;
use ModulesGarden\TSSGGSModule\Components\Label\LabelSuccess;
use ModulesGarden\TSSGGSModule\Components\VisibilityWrapper\VisibilityWrapper;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\ModalLoad;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Core\DataProviders\ArrayDataProvider;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Product;
use WHMCS\User\Client;

class DataTable extends \ModulesGarden\TSSGGSModule\Components\DataTable\DataTable implements AdminAreaInterface, AjaxComponentInterface
{
    public function loadHtml(): void
    {
        $this->setId('productsDataTable');

        $this->addColumn((new Column('id'))
                             ->setTitle($this->translate('id'))
                             ->setSortable()
                             ->setSearchable(true, Column::TYPE_INT))
             ->addColumn((new Column('productName'))
                             ->setTitle($this->translate('productName'))
                             ->setSortable(true)
                             ->setSearchable(true))
             ->addColumn((new Column('brand'))
                             ->setTitle($this->translate('brand'))
                             ->setSortable(true)
                             ->setSearchable(true))
             ->addColumn((new Column('validation'))
                             ->setTitle($this->translate('validation'))
                             ->setSortable(true)
                             ->setSearchable(true))
             ->addColumn((new Column('provider'))
                             ->setTitle($this->translate('provider'))
                             ->setSortable(true)
                             ->setSearchable(true))
             ->addColumn((new Column('billing'))
                             ->setTitle($this->translate('billing'))
                             ->setSortable(true)
                             ->setSearchable(true))
             ->addColumn((new Column('status'))
                             ->setTitle($this->translate('status'))
                             ->setSortable(true)
                             ->setSearchable(true));

        if(($_REQUEST['mg-page'] ?? '') != 'configuration')
        {
            $this->addColumn((new Column('manage'))->setTitle($this->translate('manage')));
        }

        $this->setRecordsPerPageOptions([10, 25]);

        $visibilityWrapper = new VisibilityWrapper((new IconButtonEdit())->onClick(new ModalLoad(new ConfigurationModal())));
        $visibilityWrapper->hideWhen('hidePricing', true);
        $this->addActionButton($visibilityWrapper);

        /*
                $visibilityWrapper = new VisibilityWrapper((new IconButton())->setTitle($this->translate('import'))->setIcon('import')->onClick(new ModalLoad(new ImportModal())));
                $visibilityWrapper->hideWhen('hideImport', true);
                $this->addActionButton($visibilityWrapper);
        */

        $this->addMassActionButton(
            (new ButtonSuccess())->setTitle($this->translate('import'))->setIcon('import')->onClick(new ModalLoad(new ImportModal()))
        );

        if($_REQUEST['mg-page'] != 'configuration')
        {
            $reSyncButton = new ButtonPrimary();
            $reSyncButton->setTitle($this->translate('reSync'));
            $reSyncButton->onClick(new ModalLoad(new ReSyncModal()));
            $this->addToToolbar($reSyncButton);
        }

    }

    /** the filter of the list: '' (all), 'imported' or 'not_imported' (the show= parameter of the page) */
    const SHOW = ['imported', 'not_imported'];

    public static function show(): string
    {
        $show = (string)($_GET['show'] ?? '');

        return in_array($show, self::SHOW, true) ? $show : '';
    }

    /** the validation filter: '' (all), 'dv', 'ov', 'ev' or 'other' (no validation level: code signing, S/MIME, LEI...) */
    const TYPES = ['dv', 'ov', 'ev', 'other'];

    public static function type(): string
    {
        $type = (string)($_GET['type'] ?? '');

        return in_array($type, self::TYPES, true) ? $type : '';
    }

    /** dv / ov / ev / other for a remote product */
    public static function typeOf($remoteProduct): string
    {
        $validation = strtolower(trim((string)$remoteProduct->validation));

        return in_array($validation, ['dv', 'ov', 'ev'], true) ? $validation : 'other';
    }

    /** true when a product passes the show / type filters ('' = no filter) */
    public static function passes($remoteProduct, $whmcsProduct, string $show, string $type): bool
    {
        if(($show === 'imported' && !$whmcsProduct) || ($show === 'not_imported' && $whmcsProduct))
        {
            return false;
        }

        return $type === '' || self::typeOf($remoteProduct) === $type;
    }

    /**
     * The products the page lists: [[remote product, WHMCS product or null], ...] in the display order - the edition's
     * own vendor; a product the module can not sell yet only when it was imported earlier
     */
    public static function catalogue(): array
    {
        if(!RemoteProduct::isSynchronized())
        {
            RemoteProduct::synchronize();
        }

        $brandWeights = [
            'Digicert' => 1,
            'RapidSSL' => 2,
            'GeoTrust' => 3,
            'Sectigo'  => 99,
        ];

        // only the edition's own vendor (a catalogue copied from the other edition is not offered)
        $vendors        = array_map('strtoupper', \ModulesGarden\TSSGGSModule\App\Libs\Edition::vendors());
        $remoteProducts = RemoteProduct::whereIn('vendor', $vendors)->get()->sortBy(function($product) use ($brandWeights) {
            return ($brandWeights[$product->brand] ?? 50) . $product->name;
        });

        $list = [];
        foreach($remoteProducts as $remoteProduct)
        {
            $whmcsProduct = $remoteProduct->getWhmcsProduct();
            if(!$whmcsProduct && !\ModulesGarden\TSSGGSModule\App\Libs\ProductCatalog::importable($remoteProduct))
            {
                continue;
            }
            $list[] = [$remoteProduct, $whmcsProduct];
        }

        return $list;
    }

    public function loadData(): void
    {
        $rows = [];
        $show = self::show();
        $type = self::type();

        foreach(self::catalogue() as [$remoteProduct, $whmcsProduct])
        {
            if(!self::passes($remoteProduct, $whmcsProduct, $show, $type))
            {
                continue;
            }

            $rows[] = [
                'id'             => $remoteProduct->id,
                'whmcsProductId' => $whmcsProduct ? $whmcsProduct->id : null,
                'productName'    => htmlspecialchars((string)$remoteProduct->name, ENT_QUOTES, 'UTF-8', false),
                'brand'          => htmlspecialchars((string)$remoteProduct->brand, ENT_QUOTES, 'UTF-8', false),
                'validation'     => htmlspecialchars((string)$remoteProduct->validation, ENT_QUOTES, 'UTF-8', false),
                'provider'       => Helpers::vendorToDisplay($remoteProduct->vendor),
                'billing'        => $whmcsProduct ? $this->translate('billing_' . ($whmcsProduct->paytype ?: 'recurring')) : '-',
                'status'         => $this->badges(ProductCatalog::badges((array)$remoteProduct->rawData, $whmcsProduct)),
                'manage'         => $whmcsProduct ? $this->manageLinks($whmcsProduct, (bool)ProductCatalog::optionProblems((int)$whmcsProduct->id, (array)$remoteProduct->rawData)) : '',
                'hidePricing'    => !$whmcsProduct,
                'hideImport'     => (bool)$whmcsProduct,
            ];
        }

        $dataProv = new ArrayDataProvider($rows);
        //$dataProv->setDefaultSorting('id', 'ASC');
        $this->setDataProvider($dataProv);
    }


    protected function parseDataSetRecords(): void
    {
        $this->dataSet->modifyRecords();
    }

    const BADGE_CLASSES = [
        'imported' => 'lu-label--success', 'not_imported' => 'lu-label--default', 'hidden' => 'lu-label--warning',
        'retired'  => 'lu-label--danger', 'removed' => 'lu-label--danger', 'options' => 'lu-label--warning',
    ];

    protected function badges(array $badges)
    {
        $html = '';
        foreach($badges as $badge)
        {
            $html .= '<span class="lu-label lu-label--sm ' . (self::BADGE_CLASSES[$badge] ?? 'lu-label--default') . '" style="margin:0 3px 3px 0">'
                . htmlspecialchars($this->translate('badge_' . $badge)) . '</span>';
        }

        return $html;
    }

    /**
     * Hide / show, retire / un-retire, fix configurable options, cancel all renewals (App\Http\Admin\Products)
     */
    protected function manageLinks($whmcsProduct, $optionsNeedFix = false)
    {
        $base    = 'addonmodules.php?module=TSSGGSModule&mg-page=products&pid=' . (int)$whmcsProduct->id . '&token=' . urlencode(function_exists('generate_token') ? \generate_token('plain') : '') . '&mg-action=';
        $actions = [];
        if(!$whmcsProduct->retired)
        {
            $actions[$whmcsProduct->hidden ? 'show' : 'hide'] = '';
        }
        $actions[$whmcsProduct->retired ? 'unretire' : 'retire'] = $whmcsProduct->retired ? '' : 'confirm_retire';
        if($optionsNeedFix)
        {
            // the "Check options" badge: only then is there something to fix
            $actions['fixOptions'] = '';
        }
        $actions['cancelRenewals'] = 'confirm_cancel_renewals';

        // sold as a WHMCS product addon (App\Libs\ProductAddons): one link per such addon, or the action that creates one
        $addons = \ModulesGarden\TSSGGSModule\App\Libs\ProductAddons::addonsOf((int)$whmcsProduct->id);
        if(!$addons && !$whmcsProduct->retired && \ModulesGarden\TSSGGSModule\App\Libs\ProductAddons::refusal((int)$whmcsProduct->id) === '')
        {
            $actions['sellAsAddon'] = 'confirm_sell_as_addon';
        }

        $links = [];
        foreach($actions as $action => $confirm)
        {
            $links[] = '<a href="' . htmlspecialchars($base . $action) . '"' . ($confirm ? ' onclick="return confirm(' . htmlspecialchars(json_encode($this->translate($confirm))) . ')"' : '') . '>'
                . htmlspecialchars($this->translate('action_' . $action)) . '</a>';
        }
        foreach($addons as $addonId)
        {
            $links[] = '<a href="configaddons.php?action=manage&amp;id=' . (int)$addonId . '">' . htmlspecialchars($this->translate('product_addon', ['id' => $addonId])) . '</a>';
        }

        return '<span style="white-space:nowrap;font-size:12px">' . implode(' · ', $links) . '</span>';
    }
}
