<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;

use ModulesGarden\TSSGGSModule\App\Models\Request;
use WHMCS\Database\Capsule;

/**
 * Client's SSL orders for the client area summary (home panel, sidebar) and the module's client pages
 */
class ClientSslSummary
{
    const TYPES = ['total', 'unpaid', 'processing', 'expires_soon'];

    protected $userId;
    protected $services;

    public function __construct($userId)
    {
        $this->userId = (int)$userId;
    }

    /**
     * Module services of the client with their SSL order state, newest first
     */
    public function services()
    {
        if($this->services !== null)
        {
            return $this->services;
        }

        $rows = Capsule::table('tblhosting')
            ->join('tblproducts', 'tblproducts.id', '=', 'tblhosting.packageid')
            ->leftJoin('tblsslorders', 'tblsslorders.serviceid', '=', 'tblhosting.id')
            ->where('tblhosting.userid', $this->userId)
            ->where('tblproducts.servertype', 'TSSGGSModule')
            ->orderBy('tblhosting.id', 'desc')
            ->get([
                'tblhosting.id', 'tblhosting.domain', 'tblhosting.domainstatus', 'tblhosting.regdate', 'tblhosting.nextduedate',
                'tblhosting.billingcycle', 'tblproducts.name as product', 'tblsslorders.status as ssl_status', 'tblsslorders.remoteid',
            ]);

        $ids         = [];
        foreach($rows as $row)
        {
            $ids[] = (int)$row->id;
        }
        $orderStatus = $ids ? Request::whereIn('serviceid', $ids)->where('name', 'certificate')->pluck('status', 'serviceid')->all() : [];
        $unpaid      = $ids ? Capsule::table('tblinvoiceitems')
            ->join('tblinvoices', 'tblinvoices.id', '=', 'tblinvoiceitems.invoiceid')
            ->where('tblinvoiceitems.type', 'Hosting')
            ->whereIn('tblinvoiceitems.relid', $ids)
            ->where('tblinvoices.status', 'Unpaid')
            ->pluck('tblinvoices.id', 'tblinvoiceitems.relid')->all() : [];

        $soon           = date('Y-m-d', strtotime('+' . max(1, (int)GeneralSettings::get('expiresSoonDays')) . ' days'));
        $this->services = [];
        foreach($rows as $row)
        {
            $status   = isset($orderStatus[$row->id]) ? (string)$orderStatus[$row->id] : '';
            $issued   = $status === 'active';
            $live     = in_array($row->domainstatus, ['Active', 'Pending', 'Suspended'], true);
            $expiring = $issued && $row->domainstatus === 'Active' && $row->nextduedate >= date('Y-m-d') && $row->nextduedate <= $soon;

            $this->services[] = [
                'id'             => (int)$row->id,
                'domain'         => (string)$row->domain,
                'product'        => (string)$row->product,
                'service_status' => (string)$row->domainstatus,
                'ssl_status'     => (string)$row->ssl_status,
                'order_status'   => $status,
                'regdate'        => (string)$row->regdate,
                'nextduedate'    => (string)$row->nextduedate,
                'unpaid_invoice' => isset($unpaid[$row->id]) ? (int)$unpaid[$row->id] : 0,
                'types'          => array_keys(array_filter([
                    'total'        => $live,
                    'unpaid'       => $live && isset($unpaid[$row->id]),
                    'processing'   => $row->domainstatus === 'Active' && !$issued && !in_array($status, AutoInstallHelpers::FINAL_ORDER_STATUSES, true),
                    'expires_soon' => $expiring,
                ])),
            ];
        }

        return $this->services;
    }

    public function counts()
    {
        $counts = array_fill_keys(self::TYPES, 0);
        foreach($this->services() as $service)
        {
            foreach($service['types'] as $type)
            {
                $counts[$type]++;
            }
        }

        return $counts;
    }

    public function filter($type)
    {
        $type = in_array($type, self::TYPES, true) ? $type : 'total';

        return array_values(array_filter($this->services(), function($service) use ($type) {
            return in_array($type, $service['types'], true);
        }));
    }

    /**
     * Every domain (common name + SANs) of the client's certificates with the certificates issued for it, newest first
     */
    public function domains()
    {
        $domains = [];
        foreach($this->services() as $service)
        {
            $stored = CertificateService::stored($service['id']);
            $order  = isset($stored['orderData']['orderData']) ? (array)$stored['orderData']['orderData'] : [];
            $names  = [];
            if(!empty($order['common_name']['name']))
            {
                $names[] = strtolower($order['common_name']['name']);
            }
            foreach(isset($order['alternative_names']) ? (array)$order['alternative_names'] : [] as $san)
            {
                if(!empty($san['name']))
                {
                    $names[] = strtolower($san['name']);
                }
            }
            if(!$names && $service['domain'] !== '')
            {
                $names[] = strtolower($service['domain']);
            }

            $certificate = [
                'service_id'   => $service['id'],
                'product'      => $service['product'],
                'status'       => $service['order_status'] !== '' ? $service['order_status'] : ($service['ssl_status'] ?: $service['service_status']),
                'valid_from'   => isset($stored['orderFiles']['validity']['begin']) ? substr($stored['orderFiles']['validity']['begin'], 0, 10) : '',
                'valid_till'   => isset($stored['orderFiles']['validity']['end']) ? substr($stored['orderFiles']['validity']['end'], 0, 10) : '',
                'ordered'      => $service['regdate'],
                'service_live' => in_array('total', $service['types'], true),
            ];

            foreach(array_unique($names) as $name)
            {
                $domains[$name][] = $certificate;
            }
        }

        ksort($domains);
        foreach($domains as $name => $certificates)
        {
            usort($certificates, function($a, $b) {
                return [$b['ordered'], $b['service_id']] <=> [$a['ordered'], $a['service_id']];
            });
            $domains[$name] = $certificates;
        }

        return $domains;
    }
}
