<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Pricing\Index;

use ModulesGarden\TSSGGSModule\App\Http\Admin\Pricing as PricingController;
use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\Libs\PricingService;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Translation\TranslatorTrait;

/**
 * Admin page "Pricing": one editable grid per currency (product + SAN options x 1/2/3 years) with the reseller cost,
 * margin and a below-cost highlight; apply margin, CSV export / import with preview.
 */
class PricingContainer extends Container implements AdminAreaInterface
{
    use TranslatorTrait;

    const BASE = 'addonmodules.php?module=TSSGGSModule&mg-page=pricing';

    public function loadHtml(): void
    {
        $this->setCss('tssggs-migration tssggs-pricing');

        $service    = new PricingService();
        $currencies = PricingService::currencies();
        $currencyId = (int)($_GET['currency'] ?? 0) ?: (int)$currencies->first()->id;
        $token      = function_exists('generate_token') ? \generate_token('plain') : '';
        $session    = $_SESSION[PricingController::SESSION] ?? [];

        // notes sit right under the page header, the grid in its own card
        $notes = new Text();
        $notes->setCss('tssggs-html tssggs-notes');
        $notes->setText($this->notices($session) . '<div class="tssggs-note">' . $this->e($this->t('grid_help')) . '</div>');
        $this->addElement($notes);
        unset($_SESSION[PricingController::SESSION]['notice'], $_SESSION[PricingController::SESSION]['error']);

        $html = '';
        if(!empty($session['preview']))
        {
            $html .= $this->preview($session['preview'], $token);
        }

        $html .= $this->toolbar($currencies, $currencyId, $token) . $this->grid($service, $currencyId, $token);

        $text = new Text();
        $text->setCss('tssggs-html');
        $text->setText($html);
        $this->addElement($text);
    }

    protected function notices(array $session)
    {
        $html = '';
        if(!empty($session['error']))
        {
            $html .= '<div class="tssggs-note is-bad">' . $this->e($session['error']) . '</div>';
        }
        foreach((array)($session['notice'] ?? []) as $key => $count)
        {
            $html .= '<div class="tssggs-note is-ok">' . $this->e($this->t('notice_' . $key, ['count' => (int)$count])) . '</div>';
        }

        return $html;
    }

    protected function toolbar($currencies, $currencyId, $token)
    {
        $html = '<div style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;margin-bottom:14px;">'
            . '<form method="get" action="addonmodules.php"><input type="hidden" name="module" value="TSSGGSModule"><input type="hidden" name="mg-page" value="pricing">'
            . '<label style="display:block;font-weight:600">' . $this->e($this->t('currency')) . '</label><select name="currency" class="form-control" onchange="this.form.submit()">';
        foreach($currencies as $currency)
        {
            $html .= '<option value="' . (int)$currency->id . '"' . ((int)$currency->id === $currencyId ? ' selected' : '') . '>' . $this->e($currency->code) . '</option>';
        }
        $html .= '</select></form>';

        $html .= '<form method="post" action="' . self::BASE . '&mg-action=applyMargin" style="display:flex;gap:6px;align-items:flex-end;" onsubmit="return confirm(' . $this->e(json_encode($this->t('confirm_margin'))) . ')">'
            . '<input type="hidden" name="token" value="' . $this->e($token) . '"><input type="hidden" name="currency" value="' . $currencyId . '">'
            . '<div><label style="display:block;font-weight:600">' . $this->e($this->t('margin')) . '</label><input class="form-control" name="margin" style="width:90px" required placeholder="20"></div>'
            . '<select name="scope" class="form-control" style="width:auto"><option value="currency">' . $this->e($this->t('scope_currency')) . '</option><option value="all">' . $this->e($this->t('scope_all')) . '</option></select>'
            . '<button class="lu-btn lu-btn--primary" type="submit">' . $this->e($this->t('apply_margin')) . '</button></form>';

        $html .= '<span style="flex:1"></span>'
            . '<a class="lu-btn lu-btn--default" href="' . self::BASE . '&mg-action=export&token=' . $this->e($token) . '">' . $this->e($this->t('export')) . '</a>'
            . '<form method="post" enctype="multipart/form-data" action="' . self::BASE . '&mg-action=importPreview" style="display:flex;gap:6px;align-items:center;">'
            . '<input type="hidden" name="token" value="' . $this->e($token) . '"><input type="hidden" name="currency" value="' . $currencyId . '">'
            . '<input type="file" name="csv" accept=".csv,text/csv" required style="max-width:220px">'
            . '<button class="lu-btn lu-btn--default" type="submit">' . $this->e($this->t('import')) . '</button></form>'
            . '</div>';

        return $html;
    }

    protected function grid(PricingService $service, $currencyId, $token)
    {
        $rows = $service->grid($currencyId);
        if(!$rows)
        {
            return '<p>' . $this->e($this->t('no_products')) . '</p>';
        }

        // Only changed fields are posted (a large catalogue would exceed max_input_vars)
        $html = '<form method="post" action="' . self::BASE . '&mg-action=save" onsubmit="this.querySelectorAll(\'input[name^=price]\').forEach(function(i){ if(i.value === i.defaultValue){ i.disabled = true; } });">'
            . '<input type="hidden" name="token" value="' . $this->e($token) . '"><input type="hidden" name="currency" value="' . $currencyId . '">'
            . '<table class="table table-condensed tssggs-pricing__grid"><thead><tr><th>' . $this->e($this->t('product')) . '</th><th style="width:70px">' . $this->e($this->t('auto')) . '</th>';
        foreach(array_keys(PricingService::TERMS) as $term)
        {
            $html .= '<th style="width:190px">' . $this->e($this->t('term_' . $term)) . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        foreach($rows as $row)
        {
            $product = $row['product'];
            $isBase  = $row['item'] === 'base';
            $html   .= '<tr' . ($isBase ? ' style="border-top:2px solid #ddd"' : '') . '><td>'
                . ($isBase
                    ? '<strong>' . $this->e($product['name']) . '</strong> <small class="tssggs-muted">#' . $product['id'] . ' · ' . $this->e(Helpers::vendorToDisplay($product['vendor'])) . ($product['remote'] === null ? ' · ' . $this->e($this->t('not_in_catalogue')) : '') . '</small>'
                    : '<span style="padding-left:16px">+ ' . $this->e($this->t('item_' . $row['item'])) . '</span>')
                . '</td><td>' . ($isBase ? '<input type="checkbox" value="1"' . ($product['auto'] ? ' checked' : '') . ' title="' . $this->e($this->t('auto_title')) . '"'
                    . ' onchange="var h = this.nextElementSibling; h.disabled = false; h.value = this.checked ? 1 : 0;">'
                    . '<input type="hidden" name="auto[' . $product['id'] . ']" value="" disabled>' : '') . '</td>';

            foreach($row['cells'] as $term => $cell)
            {
                if(!$cell['available'] && $cell['price'] === null)
                {
                    $html .= '<td class="tssggs-muted">-</td>';
                    continue;
                }
                $style = $cell['below_cost'] ? 'border-color:#a8322d;background:#f9e3e1;' : '';
                $html .= '<td><input class="form-control input-sm" style="width:100px;display:inline-block;' . $style . '" name="price[' . $product['id'] . '][' . $row['item'] . '][' . $term . ']" value="'
                    . ($cell['price'] === null ? '' : number_format($cell['price'], 2, '.', '')) . '" placeholder="' . $this->e($this->t('disabled')) . '">'
                    . '<br><small style="color:' . ($cell['below_cost'] ? '#a8322d;font-weight:600' : '#5b6673') . '">'
                    . ($cell['cost'] === null ? $this->e($this->t('no_cost')) : $this->e($this->t('cost', ['cost' => number_format($cell['cost'], 2)])) . ($cell['margin'] !== null ? ' · ' . $this->e($cell['margin']) . ' %' : ''))
                    . ($cell['below_cost'] ? ' · ' . $this->e($this->t('below_cost')) : '') . '</small></td>';
            }
            $html .= '</tr>';
        }

        return $html . '</tbody></table><button class="lu-btn lu-btn--success" type="submit">' . $this->e($this->t('save_all')) . '</button></form>';
    }

    protected function preview(array $preview, $token)
    {
        $html = '<div class="tssggs-note" style="border:1px solid #d9dee5;padding:12px;margin-bottom:16px;"><h4 style="margin-top:0">' . $this->e($this->t('preview_title')) . '</h4>'
            . '<p>' . $this->e($this->t('preview_summary', ['accepted' => count($preview['accepted']), 'rejected' => count($preview['rejected']), 'unchanged' => (int)$preview['unchanged']])) . '</p>';

        if($preview['accepted'])
        {
            $html .= '<table class="table table-condensed"><thead><tr><th>' . $this->e($this->t('product')) . '</th><th>' . $this->e($this->t('item')) . '</th><th>' . $this->e($this->t('term')) . '</th><th>'
                . $this->e($this->t('currency')) . '</th><th>' . $this->e($this->t('old_price')) . '</th><th>' . $this->e($this->t('new_price')) . '</th><th>' . $this->e($this->t('reseller_cost')) . '</th></tr></thead><tbody>';
            foreach($preview['accepted'] as $row)
            {
                $html .= '<tr' . ($row['below_cost'] ? ' style="background:#f9e3e1"' : '') . '><td>' . $this->e($row['product']) . '</td><td>' . $this->e($this->t('item_' . $row['item'])) . '</td><td>' . (int)$row['term'] . '</td><td>'
                    . $this->e($row['currency']) . '</td><td>' . ($row['old'] === null ? $this->e($this->t('disabled')) : number_format($row['old'], 2)) . '</td><td><strong>'
                    . ($row['new'] === null ? $this->e($this->t('disabled')) : number_format($row['new'], 2)) . '</strong>' . ($row['below_cost'] ? ' <span style="color:#a8322d">' . $this->e($this->t('below_cost')) . '</span>' : '') . '</td><td>'
                    . ($row['cost'] === null ? '-' : number_format($row['cost'], 2)) . '</td></tr>';
            }
            $html .= '</tbody></table>';
        }

        if($preview['rejected'])
        {
            $html .= '<h5>' . $this->e($this->t('rejected_title')) . '</h5><table class="table table-condensed"><thead><tr><th>' . $this->e($this->t('line')) . '</th><th>' . $this->e($this->t('data')) . '</th><th>'
                . $this->e($this->t('reason')) . '</th></tr></thead><tbody>';
            foreach($preview['rejected'] as $row)
            {
                $html .= '<tr><td>' . (int)$row['line'] . '</td><td><code>' . $this->e($row['data']) . '</code></td><td style="color:#a8322d">' . $this->e($row['reason']) . '</td></tr>';
            }
            $html .= '</tbody></table>';
        }

        $html .= '<form method="post" action="' . self::BASE . '&mg-action=importConfirm" style="display:inline"><input type="hidden" name="token" value="' . $this->e($token) . '">'
            . '<button class="lu-btn lu-btn--success" type="submit"' . ($preview['accepted'] ? '' : ' disabled') . '>' . $this->e($this->t('import_confirm', ['count' => count($preview['accepted'])])) . '</button></form> '
            . '<a class="lu-btn lu-btn--default" href="' . self::BASE . '&mg-action=importCancel">' . $this->e($this->t('import_cancel')) . '</a></div>';

        return $html;
    }

    protected function e($value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES);
    }

    protected function t(string $key, array $replace = []): string
    {
        return (string)$this->translate($key, $replace, ['admin.pricing_grid']);
    }
}
