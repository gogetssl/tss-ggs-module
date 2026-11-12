<?php

namespace ModulesGarden\TSSGGSModule\App\Http\Admin;

use ModulesGarden\TSSGGSModule\App\Libs\PricingService;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\ProductRepository;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets\NextStepsWidget;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Pricing\Index\PricingContainer;
use ModulesGarden\TSSGGSModule\Core\Contracts\Controllers\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Http\AbstractController;
use ModulesGarden\TSSGGSModule\Core\Helper;

/**
 * Pricing grid: save all, apply margin, CSV export / import (preview, confirm).
 * Mutating actions are POSTed with the WHMCS admin token.
 */
class Pricing extends AbstractController implements AdminAreaInterface
{
    const SESSION = 'tssggs_pricing';

    public function index()
    {
        return Helper\view()
            ->addElement(PricingContainer::class);
    }

    /** POST price[pid][item][term], auto[pid], currency */
    public function save()
    {
        $this->run(function() {
            $service    = new PricingService();
            $products   = $service->products();
            $currencyId = (int)($_POST['currency'] ?? 0);
            $changed    = 0;
            $invalid    = 0;

            foreach((array)($_POST['price'] ?? []) as $pid => $items)
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
            foreach((array)($_POST['auto'] ?? []) as $pid => $auto)
            {
                if(isset($products[(int)$pid]))
                {
                    $repository->updateProductConfiguration((int)$pid, ['price_auto' => $auto === '1' ? 'on' : 'off']);
                    $changed++;
                }
            }

            NextStepsWidget::markDone(NextStepsWidget::STEP_PRICING);

            return ['saved' => $changed] + ($invalid ? ['invalid' => $invalid] : []);
        });
    }

    /** POST margin, scope = currency|all */
    public function applyMargin()
    {
        $this->run(function() {
            $margin = (float)str_replace(',', '.', (string)($_POST['margin'] ?? ''));
            if($margin <= -100)
            {
                throw new \RuntimeException('The margin must be greater than -100 %');
            }
            $currencyId = ($_POST['scope'] ?? '') === 'all' ? null : (int)($_POST['currency'] ?? 0);

            NextStepsWidget::markDone(NextStepsWidget::STEP_PRICING);

            return ['margin_applied' => (new PricingService())->applyMargin($margin, $currencyId)];
        });
    }

    /** GET token: CSV of all prices and costs */
    public function export()
    {
        $this->requireToken();
        $csv = (new PricingService())->exportCsv();
        while(ob_get_level() > 0)
        {
            ob_end_clean();
        }
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="ssl-pricing-' . date('Y-m-d') . '.csv"');
        echo $csv;
        exit;
    }

    /** POST file csv: parses it and keeps the preview in the session */
    public function importPreview()
    {
        $this->run(function() {
            if(empty($_FILES['csv']['tmp_name']) || !is_uploaded_file($_FILES['csv']['tmp_name']))
            {
                throw new \RuntimeException('Choose a CSV file');
            }
            $_SESSION[self::SESSION]['preview'] = (new PricingService())->previewCsv(file_get_contents($_FILES['csv']['tmp_name']));

            return [];
        });
    }

    /** POST: applies the accepted rows of the preview */
    public function importConfirm()
    {
        $this->run(function() {
            $preview = $_SESSION[self::SESSION]['preview'] ?? null;
            unset($_SESSION[self::SESSION]['preview']);
            if(!$preview)
            {
                throw new \RuntimeException('The import preview has expired, upload the file again');
            }

            NextStepsWidget::markDone(NextStepsWidget::STEP_PRICING);

            return ['imported' => (new PricingService())->applyCsv($preview['accepted'])];
        });
    }

    public function importCancel()
    {
        unset($_SESSION[self::SESSION]['preview']);

        return Helper\redirect('pricing', 'index');
    }

    protected function run(callable $action)
    {
        try
        {
            $this->requireToken();
            $_SESSION[self::SESSION]['notice'] = $action();
        }
        catch(\Throwable $e)
        {
            $_SESSION[self::SESSION]['error'] = $e->getMessage();
        }

        $currency = (int)($_POST['currency'] ?? 0);
        header('Location: addonmodules.php?module=TSSGGSModule&mg-page=pricing' . ($currency ? '&currency=' . $currency : ''));
        exit;
    }

    protected function requireToken(): void
    {
        if(!\ModulesGarden\TSSGGSModule\App\Libs\CsrfToken::valid($_REQUEST['token'] ?? ''))
        {
            throw new \RuntimeException('Invalid or expired security token. Reload the page and try again.');
        }
    }
}
