<?php

namespace ModulesGarden\TTSGGSModule\Core\WHMCS\Models;

/**
 * Description of Domain
 *
 * @var id
 * @var userid
 * @var orderid
 * @var type
 * @var registrationdate
 * @var domain
 * @var firstpaymentamount
 * @var recurringamount
 * @var registrar
 * @var registrationperiod
 * @var expirydate
 * @var subscriptionid
 * @var promoid
 * @var status
 * @var nextduedate
 * @var nextinvoicedate
 * @var additionalnotes
 * @var paymentmethod
 * @var dnsmanagement
 * @var emailforwarding
 * @var idprotection
 * @var is_premium
 * @var donotrenew
 * @var reminders
 * @var synced
 * @var created_at
 * @var updated_at
 */
class Domain extends \WHMCS\Domain\Domain
{
    public function client()
    {
        return $this->belongsTo("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Client", 'userid');
    }

    public function order()
    {
        return $this->belongsTo("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Order", 'orderid');
    }

    public function invoiceItems()
    {
        return $this->hasMany("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\InvoiceItem", "relid")
            ->whereIn("type", ["DomainRegister", "DomainTransfer", "Domain", "DomainAddonDNS", "DomainAddonEMF", "DomainAddonIDP", "DomainGraceFee", "DomainRedemptionFee"]);
    }
}
