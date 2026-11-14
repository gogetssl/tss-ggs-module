<?php

use ModulesGarden\Servers\TSSGGSModule\app\controllers\server\admin\Actions;
use ModulesGarden\Servers\TSSGGSModule\Server;

if(!defined('DS'))define('DS',DIRECTORY_SEPARATOR);

require_once __DIR__.DS.'Loader.php';
new \ModulesGarden\Servers\TSSGGSModule\Loader();

// Edition name (GoGetSSL Module / TheSSLStore Plugin) - loaded directly, without booting the addon framework
if(file_exists(dirname(__DIR__, 2).DS.'addons'.DS.'TSSGGSModule'.DS.'app'.DS.'Libs'.DS.'Edition.php'))
{
    require_once dirname(__DIR__, 2).DS.'addons'.DS.'TSSGGSModule'.DS.'app'.DS.'Libs'.DS.'Edition.php';
}

\ModulesGarden\Servers\TSSGGSModule\Server::I();



if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/** A certificate kept as a record of the legacy module (App\Libs\RecordOnly): the answer for it, null for any other */
function TSSGGSModule_recordOnlyGuard($function, $params) {
    try {
        return class_exists('\ModulesGarden\TSSGGSModule\App\Libs\RecordOnly') ? \ModulesGarden\TSSGGSModule\App\Libs\RecordOnly::guard($function, $params) : null;
    } catch(\Throwable $e) {
        return null;
    }
}

function TSSGGSModule_MetaData() {
    return array(
        'DisplayName' => class_exists('\ModulesGarden\TSSGGSModule\App\Libs\Edition', false) ? \ModulesGarden\TSSGGSModule\App\Libs\Edition::name() : 'GoGetSSL Module',
        'APIVersion' => '1.0',
    );
}

function TSSGGSModule_ConfigOptions() {
    return (new Actions())->ConfigOptions();
}

function TSSGGSModule_CreateAccount($params) {
    if(($recordOnly = TSSGGSModule_recordOnlyGuard('CreateAccount', $params)) !== null) return $recordOnly;
    return (new Actions())->CreateAccount($params);
}

function TSSGGSModule_SuspendAccount($params) {
    if(($recordOnly = TSSGGSModule_recordOnlyGuard('SuspendAccount', $params)) !== null) return $recordOnly;
    return (new Actions())->SuspendAccount($params);
}

function TSSGGSModule_UnsuspendAccount($params) {
    if(($recordOnly = TSSGGSModule_recordOnlyGuard('UnsuspendAccount', $params)) !== null) return $recordOnly;
    return (new Actions())->UnsuspendAccount($params);
}

function TSSGGSModule_ChangePackage($params) {
    if(($recordOnly = TSSGGSModule_recordOnlyGuard('ChangePackage', $params)) !== null) return $recordOnly;
    return (new Actions())->ChangePackage($params);
}

function TSSGGSModule_SSLStepOne($params) {
    if(($recordOnly = TSSGGSModule_recordOnlyGuard('SSLStepOne', $params)) !== null) return $recordOnly;
    return (new Actions())->SSLStepOne($params);
}

function TSSGGSModule_SSLStepTwo($params) {
    if(($recordOnly = TSSGGSModule_recordOnlyGuard('SSLStepTwo', $params)) !== null) return $recordOnly;
    return (new Actions())->SSLStepTwo($params);
}

function TSSGGSModule_SSLStepThree($params) {
    if(($recordOnly = TSSGGSModule_recordOnlyGuard('SSLStepThree', $params)) !== null) return $recordOnly;
    return (new Actions())->SSLStepThree($params);
}

function TSSGGSModule_TerminateAccount($params) {
    if(($recordOnly = TSSGGSModule_recordOnlyGuard('TerminateAccount', $params)) !== null) return $recordOnly;
    return (new Actions())->TerminateAccount($params);
}

function TSSGGSModule_AdminCustomButtonArray($params = []) {
    if(($recordOnly = TSSGGSModule_recordOnlyGuard('AdminCustomButtonArray', $params)) !== null) return $recordOnly;
    return (new Actions())->AdminCustomButtonArray($params);
}

function TSSGGSModule_RecheckValidation($params) {
    if(($recordOnly = TSSGGSModule_recordOnlyGuard('RecheckValidation', $params)) !== null) return $recordOnly;
    return (new Actions())->RecheckValidation($params);
}

function TSSGGSModule_CancelOrder($params) {
    if(($recordOnly = TSSGGSModule_recordOnlyGuard('CancelOrder', $params)) !== null) return $recordOnly;
    return (new Actions())->CancelOrder($params);
}

function TSSGGSModule_DownloadAuthFile($params) {
    if(($recordOnly = TSSGGSModule_recordOnlyGuard('DownloadAuthFile', $params)) !== null) return $recordOnly;
    return (new Actions())->DownloadAuthFile($params);
}

function TSSGGSModule_ClientAreaCustomButtonArray($params = []) {
    if(($recordOnly = TSSGGSModule_recordOnlyGuard('ClientAreaCustomButtonArray', $params)) !== null) return $recordOnly;
    return (new Actions())->ClientAreaCustomButtonArray($params);
}

function TSSGGSModule_ClientAreaAllowedFunctions() {
    return (new Actions())->ClientAreaAllowedFunctions();
}

function TSSGGSModule_ClientAreaCustomDownloadCertificate($params) {
    if(($recordOnly = TSSGGSModule_recordOnlyGuard('ClientAreaCustomDownloadCertificate', $params)) !== null) return $recordOnly;
    return (new Actions())->DownloadCertificate($params);
}

function TSSGGSModule_SyncCertificate($params) {
    if(($recordOnly = TSSGGSModule_recordOnlyGuard('SyncCertificate', $params)) !== null) return $recordOnly;
    return (new Actions())->SyncCertificate($params);
}

function TSSGGSModule_SSLAdminResendApproverEmail($params) {
    if(($recordOnly = TSSGGSModule_recordOnlyGuard('SSLAdminResendApproverEmail', $params)) !== null) return $recordOnly;
    return (new Actions())->ResendApproverEmail($params);
}

function TSSGGSModule_SSLAdminResendCertificate($params) {
    if(($recordOnly = TSSGGSModule_recordOnlyGuard('SSLAdminResendCertificate', $params)) !== null) return $recordOnly;
    return (new Actions())->ResendCertificate($params);
}

function TSSGGSModule_Renew($params) {
    if(($recordOnly = TSSGGSModule_recordOnlyGuard('Renew', $params)) !== null) return $recordOnly;
    return (new Actions())->Renew($params);
}

function TSSGGSModule_AdminServicesTabFields(array $params) {
    if(($recordOnly = TSSGGSModule_recordOnlyGuard('AdminServicesTabFields', $params)) !== null) return $recordOnly;
    return (new Actions())->AdminServicesTabFields($params);
}

function TSSGGSModule_AdminServicesTabFieldsSave(array $params) {
    if(($recordOnly = TSSGGSModule_recordOnlyGuard('AdminServicesTabFieldsSave', $params)) !== null) return $recordOnly;
    (new \ModulesGarden\Servers\TSSGGSModule\app\services\AdminServicesTabFields())->save($params);
}

function TSSGGSModule_SSLAdminGetCertificate($params) {
    if(($recordOnly = TSSGGSModule_recordOnlyGuard('SSLAdminGetCertificate', $params)) !== null) return $recordOnly;
    return (new Actions())->GetCertificate($params);
}

function TSSGGSModule_ClientAreaCustomReissueCertificate($params) {
    if(($recordOnly = TSSGGSModule_recordOnlyGuard('ClientAreaCustomReissueCertificate', $params)) !== null) return $recordOnly;
    return (new Actions())->ReissueCertificate($params);
}

function TSSGGSModule_ClientAreaCustomContactDetails($params) {
    if(($recordOnly = TSSGGSModule_recordOnlyGuard('ClientAreaCustomContactDetails', $params)) !== null) return $recordOnly;
    return (new Actions())->ContactDetails($params);
}

function TSSGGSModule_ClientArea(array $params) {
    if(($recordOnly = TSSGGSModule_recordOnlyGuard('ClientArea', $params)) !== null) return $recordOnly;
    
    if(!empty($_REQUEST['json']))
    {
        // Response is always a json_encode()d string (see Server::getJSONClientAreaPage), never HTML
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        $jsonResponse = Server::getJSONClientAreaPage($params, $_REQUEST);
        echo $jsonResponse;
        die();
    }

    return Server::getHTMLClientAreaPage($params, $_REQUEST);
}
