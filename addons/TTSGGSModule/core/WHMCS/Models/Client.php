<?php

namespace ModulesGarden\TTSGGSModule\Core\WHMCS\Models;

/**
 * Description of Client
 *
 * @var int id
 * @var string uuid
 * @var string firstname
 * @var string lastname
 * @var string companyname
 * @var string email
 * @var string address1
 * @var string address2
 * @var string city
 * @var string state
 * @var string postcode
 * @var string country
 * @var string phonenumber
 * @var string password
 * @var string authmodule
 * @var string authdata
 * @var int currency
 * @var string defaultgateway
 * @var float credit
 * @var int taxexempt
 * @var int latefeeoveride
 * @var int overideduenotices
 * @var int separateinvoices
 * @var int disableautocc
 * @var datetime datecreated
 * @var string notes
 * @var int billingcid
 * @var int securityqid
 * @var string securityqans
 * @var int groupid
 * @var text cardtype
 * @var text cardlastfour
 * @var string cardnum
 * @var string startdate
 * @var string expdate
 * @var string issuenumber
 * @var string bankname
 * @var string banktype
 * @var string bankcode
 * @var string bankacct
 * @var string gatewayid
 * @var datetime lastlogin
 * @var string ip
 * @var string host
 * @var enum('Active', 'Inactive', 'Closed') status
 * @var string language
 * @var string pwresetkey
 * @var int emailoptout
 * @var int overrideautoclose
 * @var int allow_sso
 * @var int email_verified
 * @var timestamp created_at
 * @var timestamp updated_at
 * @var timestamp pwresetexpiry
 */
class Client extends \WHMCS\User\Client
{
    public function group()
    {
        return $this->belongsTo("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\ClientGroup", 'groupid');
    }

    public function services()
    {
        return $this->hasMany("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Hosting", 'userid');
    }

    public function invoices()
    {
        return $this->hasMany("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Invoice", 'userid');
    }

    public function orders()
    {
        return $this->hasMany("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Order", 'userid');
    }

    public function transactions()
    {
        return $this->hasMany("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Transaction", 'userid');
    }

    public function domains()
    {
        return $this->hasMany("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Service\Domain", "userid");
    }

    public function addons()
    {
        return $this->hasMany("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Service\Addon", "userid");
    }

    public function contacts()
    {
        return $this->hasMany("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Contact", "userid");
    }

    public function billingContact()
    {
        return $this->hasOne("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Contact", "id", "billingcid");
    }

    public function currencyObj()
    {
        return $this->hasOne("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Currency", 'id', 'currency');
    }

    /**
     * @deprecated - use services
     */
    public function hostings()
    {
        return $this->hasMany("ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Hosting", 'userid');
    }

    public function getFirstnameAttribute()
    {
        return preg_replace_callback('/(&#[0-9]+;)/', function($m) {
            return mb_convert_encoding($m[1], 'UTF-8', 'HTML-ENTITIES');
        }, html_entity_decode($this->attributes['firstname']));
    }

    public function getLastnameAttribute()
    {
        return preg_replace_callback('/(&#[0-9]+;)/', function($m) {
            return mb_convert_encoding($m[1], 'UTF-8', 'HTML-ENTITIES');
        }, html_entity_decode($this->attributes['lastname']));
    }
}
