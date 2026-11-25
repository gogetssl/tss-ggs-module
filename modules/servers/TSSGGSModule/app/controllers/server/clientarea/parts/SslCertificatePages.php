<?php

namespace TSSGGSModule\Server\app\controllers\server\clientarea\parts;

use TSSGGSModule\Server\app\models\whmcs\Client;
use TSSGGSModule\Server\app\models\whmcs\Service;
use TSSGGSModule\Server\app\models\whmcs\SSL;
use TSSGGSModule\App\Models\Whmcs\Invoice;

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
