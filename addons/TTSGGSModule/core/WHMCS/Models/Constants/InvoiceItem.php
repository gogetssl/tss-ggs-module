<?php

namespace ModulesGarden\TTSGGSModule\Core\WHMCS\Models\Constants;

class InvoiceItem
{
    public const DOMAIN_TYPES         = ['domainregister', 'domaintransfer', 'domainrenew'];
    public const TYPE_ADDON           = 'addon';
    public const TYPE_DOMAIN          = 'domainpricing'; //used only when domain type is not specified! - this type does NOT exits in database
    public const TYPE_DOMAIN_REGISTER = 'domainregister';
    public const TYPE_DOMAIN_RENEW    = 'domainrenew';
    public const TYPE_DOMAIN_TRANSFER = 'domaintransfer';
    public const TYPE_PRODUCT         = 'product';
}
