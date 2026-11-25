<?php

namespace TSSGGSModule\App\Pages\Admin;

use TSSGGSModule\App\Kernel\Kernel;
use TSSGGSModule\App\Kernel\Page;
use TSSGGSModule\App\Libs\Billing\Markups as MarkupRules;
use TSSGGSModule\App\Libs\Support\GeneralSettings;
use WHMCS\Database\Capsule;

/**
 * Admin page "Markups": the % the reseller adds to the price per client and product.
 * Templates: resources/templates/admin/markups.
 */
class Markups extends Page
{
    const LANG       = 'admin.markups';
    const BODY_CLASS = 'tssggs-migration tssggs-markups';

    public function index()
    {
        $clients = [];
        foreach(Capsule::table('tblclients')->orderBy('firstname')->orderBy('lastname')->limit(5000)->get(['id', 'firstname', 'lastname', 'companyname']) as $client)
        {
            $clients[] = ['id' => (int)$client->id, 'name' => self::clientName($client)];
        }
        $products = [];
        foreach(Capsule::table('tblproducts')->where('servertype', Kernel::module())->orderBy('name')->get(['id', 'name']) as $product)
        {
            $products[] = ['id' => (int)$product->id, 'name' => (string)$product->name];
        }

        return $this->view('admin/markups/index', $this->listData() + [
            'clients'   => $clients,
            'products'  => $products,
            'showIntro' => !GeneralSettings::enabled('hideInfoMessages'),
        ]);
    }

    /** POST client_id, product_id, markup: adds the rule or changes the one for that client and product */
    public function postSave()
    {
        $this->attempt(function() {
            MarkupRules::set($this->request->int('client_id'), $this->request->int('product_id'), str_replace(',', '.', $this->request->string('markup')));
        }, $this->t('notice_saved'));

        return $this->changed();
    }

    /** POST id */
    public function postDelete()
    {
        $this->attempt(function() {
            MarkupRules::delete($this->request->int('id'));
        }, $this->t('notice_deleted'));

        return $this->changed();
    }

    protected function changed()
    {
        return $this->request->isHtmx() ? $this->fragment('admin/markups/list', $this->listData()) : $this->redirect();
    }

    protected function listData(): array
    {
        $rules = [];
        foreach(MarkupRules::all() as $rule)
        {
            $rules[] = [
                'id'        => (int)$rule->id,
                'clientId'  => (int)$rule->client_id,
                'client'    => self::clientName($rule),
                'productId' => (int)$rule->product_id,
                'product'   => $rule->product ?: '#' . $rule->product_id,
                'markup'    => ((float)$rule->markup > 0 ? '+' : '') . rtrim(rtrim((string)$rule->markup, '0'), '.'),
                'updated'   => (string)$rule->created_at,
            ];
        }

        return ['rules' => $rules, 'flash' => $this->takeFlash()];
    }

    protected static function clientName($row): string
    {
        return trim($row->firstname . ' ' . $row->lastname) . ($row->companyname ? ' (' . $row->companyname . ')' : '');
    }
}
