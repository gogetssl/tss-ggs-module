<?php

namespace TSSGGSModule\App\Pages\Admin;

use TSSGGSModule\App\Kernel\Page;
use TSSGGSModule\App\Kernel\Response;
use TSSGGSModule\App\Libs\Billing\Markups;
use TSSGGSModule\App\Libs\Billing\PricingService;
use TSSGGSModule\App\Libs\Support\GeneralSettings;
use TSSGGSModule\App\Libs\Support\Helpers;
use TSSGGSModule\App\Repositories\Whmcs\ProductRepository;
use TSSGGSModule\App\Libs\Dashboard\NextSteps;

/**
 * Admin page "Pricing": one editable grid per currency (product + SAN options x 1/2/3 years) with the reseller
 * cost, margin and a below-cost highlight; apply margin, CSV export / import with preview.
 * Every change is a form post that comes back to the page (the whole grid changes). Templates:
 * resources/templates/admin/pricing.
 */
class Pricing extends Page
{
    const LANG       = 'admin.pricing_grid';
    const BODY_CLASS = 'tssggs-migration tssggs-pricing';
    const SESSION    = 'tssggs_pricing';

    public function index()
    {
        $service    = new PricingService();
        $currencies = [];
        foreach(PricingService::currencies() as $currency)
        {
            $currencies[] = ['id' => (int)$currency->id, 'code' => (string)$currency->code];
        }
        $currencyId = $this->request->int('currency') ?: ($currencies[0]['id'] ?? 0);

        $session = $_SESSION[self::SESSION] ?? [];
        unset($_SESSION[self::SESSION]['notice'], $_SESSION[self::SESSION]['error']);
        $notices = [];
        foreach((array)($session['notice'] ?? []) as $key => $count)
        {
            $notices[] = $this->t('notice_' . $key, ['count' => (int)$count]);
        }

        return $this->view('admin/pricing/index', [
            'error'      => (string)($session['error'] ?? ''),
            'notices'    => $notices,
            'markupNote' => $this->markupNote(),
            'showHelp'   => !GeneralSettings::enabled('hideInfoMessages'),
            'preview'    => !empty($session['preview']) ? $this->previewData($session['preview']) : null,
            'currencies' => $currencies,
            'currencyId' => $currencyId,
            'terms'      => array_keys(PricingService::TERMS),
            'rows'       => $this->rows($service, $currencyId),
        ]);
    }

    /** POST price[pid][item][term], auto[pid], currency */
    public function postSave()
    {
        return $this->run(function() {
            $service    = new PricingService();
            $products   = $service->products();
            $currencyId = $this->request->int('currency');
            $changed    = 0;
            $invalid    = 0;

            foreach((array)$this->request->input('price', []) as $pid => $items)
            {
                if(!isset($products[(int)$pid]))
                {
                    continue;
                }
                foreach((array)$items as $item => $terms)
                {
                    foreach((array)$terms as $term => $value)
                    {
                        $value = trim((string)$value);
                        if(!in_array($item, PricingService::ITEMS, true) || !isset(PricingService::TERMS[(int)$term]))
                        {
                            continue;
                        }
                        // anything that is not a plain amount is left as it is - it must never turn into a price of 0.00
                        if($value !== '' && !preg_match('/^\d+([.,]\d{1,4})?$/', $value))
                        {
                            $invalid++;
                            continue;
                        }
                        $new = $value === '' ? ($item === 'base' ? -1 : 0) : round((float)str_replace(',', '.', $value), 2);
                        $old = $service->price($products[(int)$pid], $item, (int)$term, $currencyId);
                        if(($old === null && $new < 0) || ($old !== null && $new >= 0 && abs($old - $new) < 0.005))
                        {
                            continue;
                        }
                        $changed += $service->setPrice($products[(int)$pid], $item, (int)$term, $currencyId, $new) ? 1 : 0;
                    }
                }
            }

            // only the toggled "Auto" checkboxes are posted
            $repository = new ProductRepository();
            foreach((array)$this->request->input('auto', []) as $pid => $auto)
            {
                if(isset($products[(int)$pid]))
                {
                    $repository->updateProductConfiguration((int)$pid, ['price_auto' => $auto === '1' ? 'on' : 'off']);
                    $changed++;
                }
            }

            NextSteps::markDone(NextSteps::STEP_PRICING);

            return ['saved' => $changed] + ($invalid ? ['invalid' => $invalid] : []);
        });
    }

    /** POST margin, scope = currency|all */
    public function postApplyMargin()
    {
        return $this->run(function() {
            $margin = (float)str_replace(',', '.', $this->request->string('margin'));
            if($margin <= -100)
            {
                throw new \RuntimeException('The margin must be greater than -100 %');
            }
            $currencyId = $this->request->string('scope') === 'all' ? null : $this->request->int('currency');

            NextSteps::markDone(NextSteps::STEP_PRICING);

            return ['margin_applied' => (new PricingService())->applyMargin($margin, $currencyId)];
        });
    }

    /** GET token: CSV of all prices and costs (a link, so the token comes in the address) */
    public function export()
    {
        if(!\TSSGGSModule\App\Libs\Support\CsrfToken::valid($this->request->string('token')))
        {
            $_SESSION[self::SESSION]['error'] = $this->lang->get('admin.kernel.tokenExpired');

            return $this->redirect();
        }

        return Response::download((new PricingService())->exportCsv(), 'ssl-pricing-' . date('Y-m-d') . '.csv');
    }

    /** POST file csv: parses it and keeps the preview in the session */
    public function postImportPreview()
    {
        return $this->run(function() {
            if(empty($_FILES['csv']['tmp_name']) || !is_uploaded_file($_FILES['csv']['tmp_name']))
            {
                throw new \RuntimeException('Choose a CSV file');
            }
            $_SESSION[self::SESSION]['preview'] = (new PricingService())->previewCsv(file_get_contents($_FILES['csv']['tmp_name']));

            return [];
        });
    }

    /** POST: applies the accepted rows of the preview */
    public function postImportConfirm()
    {
        return $this->run(function() {
            $preview = $_SESSION[self::SESSION]['preview'] ?? null;
            unset($_SESSION[self::SESSION]['preview']);
            if(!$preview)
            {
                throw new \RuntimeException('The import preview has expired, upload the file again');
            }

            NextSteps::markDone(NextSteps::STEP_PRICING);

            return ['imported' => (new PricingService())->applyCsv($preview['accepted'])];
        });
    }

    public function postImportCancel()
    {
        unset($_SESSION[self::SESSION]['preview']);

        return $this->redirect();
    }

    /** Runs a change, keeps its result for the next view and goes back to the grid of the same currency */
    protected function run(callable $action)
    {
        try
        {
            $_SESSION[self::SESSION]['notice'] = $action();
        }
        catch(\Throwable $e)
        {
            $_SESSION[self::SESSION]['error'] = $e->getMessage();
        }

        return $this->redirect('index', ['currency' => $this->request->int('currency')]);
    }

    /** Markups are added on top of the base prices at order time; tell the admin they exist */
    protected function markupNote(): string
    {
        $rules = Markups::all();
        if(!count($rules))
        {
            return '';
        }
        $global = null;
        $other  = 0;
        foreach($rules as $rule)
        {
            if(!(int)$rule->client_id && !(int)$rule->product_id)
            {
                $global = (float)$rule->markup;
            }
            else
            {
                $other++;
            }
        }

        return $this->t('markup_note', ['global' => $global === null ? '-' : self::rate($global) . ' %', 'count' => $other]);
    }

    /** @return array[] rows of the grid, ready for the template */
    protected function rows(PricingService $service, int $currencyId): array
    {
        $rows = [];
        foreach($service->grid($currencyId) as $row)
        {
            $product = $row['product'];
            $isBase  = $row['item'] === 'base';
            $markup  = $isBase ? Markups::forProduct(0, (int)$product['id']) : null;
            $cells   = [];
            foreach($row['cells'] as $term => $cell)
            {
                $cells[] = [
                    'term'        => (int)$term,
                    'none'        => !$cell['available'] && $cell['price'] === null,
                    'price'       => $cell['price'] === null ? '' : number_format($cell['price'], 2, '.', ''),
                    'cost'        => $cell['cost'] === null ? '' : number_format($cell['cost'], 2),
                    'margin'      => $cell['margin'] === null ? '' : (string)$cell['margin'],
                    'below'       => (bool)$cell['below_cost'],
                    // what a client without a client-specific rule pays: the price with the "all clients" markup
                    'clientPrice' => $markup !== null && $cell['price'] !== null ? number_format(Markups::apply($cell['price'], $markup), 2) : '',
                    'clientRate'  => $markup !== null ? self::rate($markup) : '',
                ];
            }
            $rows[] = [
                'isBase'  => $isBase,
                'id'      => (int)$product['id'],
                'name'    => (string)$product['name'],
                'vendor'  => Helpers::vendorToDisplay($product['vendor']),
                'missing' => $product['remote'] === null,
                'auto'    => !empty($product['auto']),
                'item'    => (string)$row['item'],
                'cells'   => $cells,
            ];
        }

        return $rows;
    }

    protected function previewData(array $preview): array
    {
        $accepted = [];
        foreach($preview['accepted'] as $row)
        {
            $accepted[] = [
                'product'  => (string)$row['product'],
                'item'     => (string)$row['item'],
                'term'     => (int)$row['term'],
                'currency' => (string)$row['currency'],
                'old'      => $row['old'] === null ? '' : number_format($row['old'], 2),
                'new'      => $row['new'] === null ? '' : number_format($row['new'], 2),
                'cost'     => $row['cost'] === null ? '-' : number_format($row['cost'], 2),
                'below'    => (bool)$row['below_cost'],
            ];
        }

        return ['accepted' => $accepted, 'rejected' => $preview['rejected'], 'unchanged' => (int)$preview['unchanged']];
    }

    /** "+10", "-5", "2.5" */
    protected static function rate(float $rate): string
    {
        return ($rate > 0 ? '+' : '') . rtrim(rtrim(number_format($rate, 2, '.', ''), '0'), '.');
    }
}
