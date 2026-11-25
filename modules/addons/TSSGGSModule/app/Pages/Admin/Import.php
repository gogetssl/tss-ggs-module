<?php

namespace ModulesGarden\TSSGGSModule\App\Pages\Admin;

use ModulesGarden\TSSGGSModule\App\Kernel\Page;
use ModulesGarden\TSSGGSModule\App\Kernel\Response;
use ModulesGarden\TSSGGSModule\App\Libs\Certificates\OrderImport;
use ModulesGarden\TSSGGSModule\App\Libs\Support\GeneralSettings;
use ModulesGarden\TSSGGSModule\App\Libs\Support\Helpers;

/**
 * Admin page "Import": certificates ordered outside WHMCS become services of a client - one order by its ID, or
 * many from a CSV file (App\Libs\Certificates\OrderImport). Plain form posts (a file is uploaded).
 * Templates: resources/templates/admin/import.
 */
class Import extends Page
{
    const LANG       = 'admin.import';
    const BODY_CLASS = 'tssggs-import';

    public function index()
    {
        return $this->view('admin/import/index', [
            'flash'      => $this->takeFlash(),
            'showAbout'  => !GeneralSettings::enabled('hideInfoMessages'),
            'tab'        => $this->request->oneOf('tab', ['single', 'csv'], 'single'),
            'clients'    => Helpers::getClientOptions(),
            'payMethods' => Helpers::getPayMethodOptions(),
        ]);
    }

    /** POST mode=single: remoteOrderId, clientId, payMethod, serviceId (optional) / mode=csv: file csv; generateInvoice */
    public function postImport()
    {
        $csv = $this->request->string('mode') === 'csv';
        $this->attempt(function() use ($csv) {
            if($csv)
            {
                if(empty($_FILES['csv']['tmp_name']) || !is_uploaded_file($_FILES['csv']['tmp_name']))
                {
                    throw new \RuntimeException($this->t('choose_file'));
                }
                $orders = OrderImport::fromCsv($_FILES['csv']['tmp_name']);
            }
            else
            {
                $orderId = $this->request->string('remoteOrderId');
                if($orderId === '' || !$this->request->int('clientId') || $this->request->string('payMethod') === '')
                {
                    throw new \RuntimeException($this->t('fill_required'));
                }
                $orders = [$orderId => [
                    'remoteOrderId' => $orderId,
                    'clientId'      => $this->request->int('clientId'),
                    'payMethod'     => $this->request->string('payMethod'),
                    'serviceId'     => $this->request->int('serviceId'),
                ]];
            }

            try
            {
                $failed = OrderImport::run($orders, $this->request->string('generateInvoice') === 'on');
            }
            catch(\Throwable $e)
            {
                // the library names two of its errors by a key
                throw new \RuntimeException($e->getMessage() === 'apiNotConfigured' ? $this->t('api_not_configured') : $e->getMessage());
            }
            if($failed)
            {
                throw new \RuntimeException($this->t('index.forms.import_form.productImportErrors'));
            }
        }, $this->t('index.forms.import_form.create_success'));

        return $this->redirect('index', ['tab' => $csv ? 'csv' : '']);
    }

    /** The CSV file to fill in */
    public function template()
    {
        return Response::download(OrderImport::csvTemplate(), 'template.csv');
    }
}
