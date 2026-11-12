<?php

namespace ModulesGarden\TSSGGSModule\App\Libs;


use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\SSL;
use ModulesGarden\Servers\TSSGGSModule\core\Lang;
use ModulesGarden\TSSGGSModule\App\Models\AcmeSubscription;
use ModulesGarden\TSSGGSModule\App\Models\RemoteProduct;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Product;
use ModulesGarden\TSSGGSModule\Packages\Product\Services\ConfigurableOptions;
use WHMCS\Database\Capsule;

class AcmeHelpers
{
    /** the GoGetSSL id of the CaaS product: a fallback, ACME products are recognised by the gateway category */
    const PRODUCT_IDS = [300];

    /** category the gateway gives to ACME (CaaS) products */
    const CATEGORY = 'caas';

    protected static $remoteIds;

    public static function isAcmeWhmcsProduct($whmcsProductId)
    {
        $product = Product::find($whmcsProductId);

        return $product && self::isAcmeRemoteProduct($product->configoption2);
    }

    public static function isAcmeRemoteProduct($remoteProductId)
    {
        return $remoteProductId !== null && $remoteProductId !== '' && in_array((string)$remoteProductId, array_map('strval', self::getProductIds()), true);
    }

    /**
     * Remote ids of the ACME products: everything the gateway lists in the CaaS category (so the TSS codes
     * sectigo_caas_dv / positivessl_caas_dv are recognised too), plus the known GoGetSSL id
     */
    public static function getProductIds()
    {
        if(self::$remoteIds === null)
        {
            self::$remoteIds = self::PRODUCT_IDS;
            try
            {
                foreach(RemoteProduct::whereRaw('LOWER(category) = ?', [self::CATEGORY])->pluck('remoteId') as $id)
                {
                    if($id !== '' && !in_array($id, self::$remoteIds, false))
                    {
                        self::$remoteIds[] = $id;
                    }
                }
            }
            catch(\Throwable $e)
            {
                // products not synchronised yet: only the known id
            }
        }

        return self::$remoteIds;
    }

    public static function getAcmeNextInvoiceDate($renewalDate, $daysBefore)
    {
        if(empty($renewalDate) || !is_numeric($daysBefore))
        {
            return null;
        }

        $renewalTimestamp = strtotime((string)$renewalDate);
        if($renewalTimestamp === false)
        {
            return null;
        }

        return date('Y-m-d', strtotime('-' . (int)$daysBefore . ' days', $renewalTimestamp));
    }


    /**
     * Renewal invoice of the subscription period already exists: an unpaid one, or any not cancelled one
     * due on the period's renewal date (not only one created today - a cron run on another day must not add one)
     */
    public static function hasAcmeSubscriptionRenewalInvoice($serviceId, $renewalDate = null)
    {
        return Capsule::table('tblinvoiceitems')
                      ->join('tblinvoices', 'tblinvoices.id', '=', 'tblinvoiceitems.invoiceid')
                      ->where('tblinvoiceitems.type', 'Hosting')
                      ->where('tblinvoiceitems.relid', (int)$serviceId)
                      ->where('tblinvoiceitems.description', 'LIKE', '%ACME Subscription Renewal')
                      ->whereNotIn('tblinvoices.status', ['Cancelled', 'Refunded'])
                      ->where(function($query) use ($renewalDate) {
                          $query->whereIn('tblinvoices.status', ['Unpaid', 'Payment Pending', 'Draft'])
                                ->orWhere('tblinvoices.date', date('Y-m-d'));
                          if($renewalDate)
                          {
                              $query->orWhere('tblinvoices.duedate', date('Y-m-d', strtotime((string)$renewalDate)));
                          }
                      })
                      ->exists();
    }

    public static function createAcmeSubscriptionRenewalInvoice($service, $product, $subscriptionData)
    {
        $lock = 'tssggs_renewal_invoice_' . (int)$service->id;
        $row  = Capsule::select('SELECT GET_LOCK(?, 10) AS l', [$lock]);
        if(empty($row) || (int)$row[0]->l !== 1)
        {
            return 0;
        }

        try
        {
            return self::createAcmeSubscriptionRenewalInvoiceLocked($service, $product, $subscriptionData);
        }
        finally
        {
            Capsule::select('SELECT RELEASE_LOCK(?)', [$lock]);
        }
    }

    protected static function createAcmeSubscriptionRenewalInvoiceLocked($service, $product, $subscriptionData)
    {
        if(self::hasAcmeSubscriptionRenewalInvoice((int)$service->id, $subscriptionData->renewal_date ?? null))
        {
            return 0;
        }

        // the recurring amount: the first payment can include a setup fee or a promotion that must not be renewed
        $amount      = isset($service->amount) ? (float)$service->amount : (isset($service->firstpaymentamount) ? (float)$service->firstpaymentamount : 0.00);
        $dateInvoice = date('Y-m-d');
        $periodStart = !empty($subscriptionData->renewal_date)
            ? date('Y-m-d', strtotime((string)$subscriptionData->renewal_date))
            : $dateInvoice;

        $periodEndDate = new \DateTime($periodStart);
        $periodEndDate->add(new \DateInterval('P12M'));
        $periodEndDate->sub(new \DateInterval('P1D'));

        $description = $product->name
            . ($service->domain ? ' - ' . $service->domain : '')
            . ' (1 Year) - ACME Subscription Renewal';

        $postData = [
            'userid'           => $service->userid,
            'sendinvoice'      => true,
            'date'             => $dateInvoice,
            'duedate'          => (new \DateTime($subscriptionData->renewal_date))->format('Y-m-d'),
            'itemdescription1' => $description,
            'itemamount1'      => $amount,
            'itemtaxed1'       => isset($product->tax) ? $product->tax : 0,
        ];

        $results = localAPI('CreateInvoice', $postData);

        if(!isset($results['result']) || $results['result'] !== 'success' || empty($results['invoiceid']))
        {
            \logActivity('TSSGGS: Failed to create ACME subscription renewal invoice for service #' . (int)$service->id . '.');
            return 0;
        }

        $invoiceId = (int)$results['invoiceid'];

        Capsule::table('tblinvoiceitems')
               ->where('invoiceid', '=', $invoiceId)
               ->update([
                            'type'  => 'Hosting',
                            'relid' => $service->id,
                        ]);

        \logActivity('TSSGGS: ACME subscription renewal invoice #' . $invoiceId . ' created for service #' . (int)$service->id . '.');

        return 1;
    }

    public static function refreshAcmeCertificateDetails($serviceId)
    {
        $subscription = AcmeSubscription::getByServiceId($serviceId);

        if(!$subscription || (int)$subscription->api_order_id <= 0)
        {
            throw new \Exception(Lang::absoluteT('subscription_was_not_created_yet'));
        }

        $apiOrderId = (int)$subscription->api_order_id;
        $api        = Helpers::getApiByServiceId($serviceId);

        $itemId  = (int)$subscription->acme_id > 0 ? (int)$subscription->acme_id : $apiOrderId;
        $details = $api->getCertificateDetails('caas', $itemId);

        $orderStatus      = isset($details['order']['status']) ? $details['order']['status'] : (isset($details['status']) ? $details['status'] : $subscription->status);
        $items            = isset($details['items']) && is_array($details['items']) ? $details['items'] : array();
        $firstItem        = isset($items[0]) && is_array($items[0]) ? $items[0] : array();
        $acmeId           = isset($firstItem['id']) ? (int)$firstItem['id'] : (isset($subscription->acme_id) ? (int)$subscription->acme_id : 0);
        $account          = isset($firstItem['account']) && is_array($firstItem['account']) ? $firstItem['account'] : array();
        $subscriptionData = isset($firstItem['subscription']) && is_array($firstItem['subscription']) ? $firstItem['subscription'] : array();
        $periodStart      = isset($subscriptionData['begin']) ? $subscriptionData['begin'] : $subscription->period_start;
        $periodEnd        = isset($subscriptionData['end']) ? $subscriptionData['end'] : $subscription->period_end;
        $renewalDate      = isset($subscriptionData['next_renewal']) ? $subscriptionData['next_renewal'] : $subscription->renewal_date;
        $autoRenew        = isset($subscriptionData['next_renewal']) ? 1 : (int)$subscription->auto_renew;

        $subscription->status          = $orderStatus;
        $subscription->acme_id         = $acmeId > 0 ? $acmeId : null;
        $subscription->acme_account_id = isset($account['id']) ? (string)$account['id'] : (string)$subscription->acme_account_id;
        $subscription->eab_kid         = isset($account['eab_mac_id']) ? (string)$account['eab_mac_id'] : (string)$subscription->eab_kid;
        $subscription->eab_hmac_key    = isset($account['eab_mac_key']) ? (string)$account['eab_mac_key'] : (string)$subscription->eab_hmac_key;
        $subscription->server_url      = isset($account['server_url']) ? (string)$account['server_url'] : (string)$subscription->server_url;
        $subscription->period_start    = $periodStart;
        $subscription->period_end      = $periodEnd;
        $subscription->renewal_date    = $renewalDate;
        $subscription->auto_renew      = $autoRenew;
        $subscription->save();

        $sslOrder = SSL::getByServiceId($serviceId);

        if($sslOrder)
        {
            $sslOrder->remoteid   = $apiOrderId;
            $sslOrder->status     = 'Completed';
            $sslOrder->configdata = json_encode([
                                                    'subscription' => true,
                                                    'ssl_status'   => $orderStatus,
                                                    'begin_date'   => $periodStart,
                                                    'end_date'     => $periodEnd,
                                                    'renewal_date' => $renewalDate,
                                                ]);
            $sslOrder->save();
        }
    }
}