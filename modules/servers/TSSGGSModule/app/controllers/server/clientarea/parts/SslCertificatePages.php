<?php

namespace ModulesGarden\Servers\TSSGGSModule\app\controllers\server\clientarea\parts;

use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\Client;
use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\Service;
use ModulesGarden\Servers\TSSGGSModule\app\models\whmcs\SSL;
use ModulesGarden\TSSGGSModule\Core\WHMCS\Models\Invoice;

/**
 * SSL certificate of a service: status pages, downloads, reissue, validation, renewal invoice, CSR tools.
 * Part of the client area controller (clientarea\home): the methods keep their names, so the page URLs are unchanged.
 */
trait SslCertificatePages
{
    use SslStatusPages;
    use SslDownloadActions;
    use SslReissueActions;
    use SslValidationActions;
    use SslServiceActions;


}
