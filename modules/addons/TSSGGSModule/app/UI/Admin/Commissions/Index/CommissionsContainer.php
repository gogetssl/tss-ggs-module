<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Commissions\Index;

use ModulesGarden\TSSGGSModule\App\Http\Admin\Commissions as CommissionsController;
use ModulesGarden\TSSGGSModule\App\Libs\Commissions;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Translation\TranslatorTrait;
use WHMCS\Database\Capsule;

/**
 * Admin page "Commissions": price adjustment % per client and product
 */
class CommissionsContainer extends Container implements AdminAreaInterface
{
    use TranslatorTrait;

    const BASE = 'addonmodules.php?module=TSSGGSModule&mg-page=commissions';

    public function loadHtml(): void
    {
        $this->setCss('tssggs-migration tssggs-commissions');
        $token   = function_exists('generate_token') ? \generate_token('plain') : '';
        $session = $_SESSION[CommissionsController::SESSION] ?? [];
        unset($_SESSION[CommissionsController::SESSION]);

        $html = '';
        if(!empty($session['error']))
        {
            $html .= '<div class="tssggs-note is-bad">' . $this->e($session['error']) . '</div>';
        }
        if(!empty($session['notice']))
        {
            $html .= '<div class="tssggs-note is-ok">' . $this->e($this->t('notice_' . $session['notice'])) . '</div>';
        }

        $html .= '<p class="tssggs-muted">' . $this->e($this->t('intro')) . '</p>' . $this->form($token) . $this->table($token);

        $text = new Text();
        $text->setCss('tssggs-html');
        $text->setText($html);
        $this->addElement($text);
    }

    protected function form($token)
    {
        $clients = Capsule::table('tblclients')->orderBy('firstname')->orderBy('lastname')->limit(5000)->get(['id', 'firstname', 'lastname', 'companyname']);
        $html    = '<form method="post" action="' . self::BASE . '&mg-action=save" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;margin-bottom:18px;">'
            . '<input type="hidden" name="token" value="' . $this->e($token) . '">'
            . '<div><label style="display:block;font-weight:600">' . $this->e($this->t('client')) . '</label><select name="client_id" class="form-control" style="width:260px"><option value="0">' . $this->e($this->t('all_clients')) . '</option>';
        foreach($clients as $client)
        {
            $html .= '<option value="' . (int)$client->id . '">#' . (int)$client->id . ' ' . $this->e(trim($client->firstname . ' ' . $client->lastname) . ($client->companyname ? ' (' . $client->companyname . ')' : '')) . '</option>';
        }
        $html .= '</select></div><div><label style="display:block;font-weight:600">' . $this->e($this->t('product')) . '</label><select name="product_id" class="form-control" style="width:300px"><option value="0">' . $this->e($this->t('all_products')) . '</option>';
        foreach(Capsule::table('tblproducts')->where('servertype', 'TSSGGSModule')->orderBy('name')->get(['id', 'name']) as $product)
        {
            $html .= '<option value="' . (int)$product->id . '">' . $this->e($product->name) . '</option>';
        }

        return $html . '</select></div><div><label style="display:block;font-weight:600">' . $this->e($this->t('commission')) . '</label><input name="commission" class="form-control" style="width:110px" placeholder="10 or -5" required></div>'
            . '<button class="lu-btn lu-btn--success" type="submit">' . $this->e($this->t('save')) . '</button></form>';
    }

    protected function table($token)
    {
        $rules = Commissions::all();
        $html  = '<table class="table table-striped"><thead><tr><th>' . $this->e($this->t('client')) . '</th><th>' . $this->e($this->t('product')) . '</th><th style="width:130px">'
            . $this->e($this->t('commission')) . '</th><th style="width:150px">' . $this->e($this->t('updated')) . '</th><th style="width:90px"></th></tr></thead><tbody>';
        foreach($rules as $rule)
        {
            $client = $rule->client_id ? '<a href="clientssummary.php?userid=' . (int)$rule->client_id . '">#' . (int)$rule->client_id . ' ' . $this->e(trim($rule->firstname . ' ' . $rule->lastname) . ($rule->companyname ? ' (' . $rule->companyname . ')' : '')) . '</a>'
                : '<em>' . $this->e($this->t('all_clients')) . '</em>';
            $html  .= '<tr><td>' . $client . '</td><td>' . ($rule->product_id ? $this->e($rule->product ?: '#' . $rule->product_id) : '<em>' . $this->e($this->t('all_products')) . '</em>') . '</td>'
                . '<td>' . ((float)$rule->commission > 0 ? '+' : '') . $this->e(rtrim(rtrim($rule->commission, '0'), '.')) . ' %</td><td>' . $this->e($rule->created_at) . '</td>'
                . '<td><a href="' . self::BASE . '&mg-action=delete&id=' . (int)$rule->id . '&token=' . $this->e($token) . '" onclick="return confirm(' . $this->e(json_encode($this->t('confirm_delete'))) . ')">' . $this->e($this->t('delete')) . '</a></td></tr>';
        }
        if(!count($rules))
        {
            $html .= '<tr><td colspan="5" style="text-align:center">' . $this->e($this->t('empty')) . '</td></tr>';
        }

        return $html . '</tbody></table>';
    }

    protected function e($value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES);
    }

    protected function t(string $key, array $replace = []): string
    {
        return (string)$this->translate($key, $replace, ['admin.commissions']);
    }
}
