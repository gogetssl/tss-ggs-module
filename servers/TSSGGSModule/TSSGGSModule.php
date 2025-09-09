<?php

use ModulesGarden\Servers\TSSGGSModule\app\controllers\server\admin\Actions;
use ModulesGarden\Servers\TSSGGSModule\Server;

if(!defined('DS'))define('DS',DIRECTORY_SEPARATOR);

require_once __DIR__.DS.'Loader.php';
new \ModulesGarden\Servers\TSSGGSModule\Loader();
\ModulesGarden\Servers\TSSGGSModule\Server::I();



if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function TSSGGSModule_MetaData() {
    return array(
        'DisplayName' => 'The SSL Store & GoGetSSL Module',
        'APIVersion' => '1.0',
    );
}

function TSSGGSModule_ConfigOptions() {
    return (new Actions())->ConfigOptions();
}

function TSSGGSModule_CreateAccount($params) {
    return (new Actions())->CreateAccount($params);
}

function TSSGGSModule_SuspendAccount($params) {
    return (new Actions())->SuspendAccount($params);
}

function TSSGGSModule_UnsuspendAccount($params) {
    return (new Actions())->UnsuspendAccount($params);
}

function TSSGGSModule_SSLStepOne($params) {
    return (new Actions())->SSLStepOne($params);
}

function TSSGGSModule_SSLStepTwo($params) {
    return (new Actions())->SSLStepTwo($params);
}

function TSSGGSModule_SSLStepThree($params) {
    return (new Actions())->SSLStepThree($params);
}

function TSSGGSModule_TerminateAccount($params) {
    return (new Actions())->TerminateAccount($params);
}

function TSSGGSModule_AdminCustomButtonArray() {
    //return (new Actions())->AdminCustomButtonArray();
}

function TSSGGSModule_SSLAdminResendApproverEmail($params) {
    return (new Actions())->ResendApproverEmail($params);
}

function TSSGGSModule_SSLAdminResendCertificate($params) {
    return (new Actions())->ResendCertificate($params);
}

function TSSGGSModule_Renew($params) {
    return (new Actions())->Renew($params);
}

function TSSGGSModule_AdminServicesTabFields(array $params) {
    return (new Actions())->AdminServicesTabFields($params);
}

function TSSGGSModule_SSLAdminGetCertificate($params) {
    return (new Actions())->GetCertificate($params);
}

function TSSGGSModule_ClientAreaCustomReissueCertificate($params) {
    return (new Actions())->ReissueCertificate($params);
}

function TSSGGSModule_ClientAreaCustomContactDetails($params) {
    return (new Actions())->ContactDetails($params);
}

function TSSGGSModule_ClientArea(array $params) {
    
    if(!empty($_REQUEST['json']))
    {
        header('Content-Type: text/plain');
        echo Server::getJSONClientAreaPage($params, $_REQUEST);
        die();
    }

    return Server::getHTMLClientAreaPage($params, $_REQUEST);
}
