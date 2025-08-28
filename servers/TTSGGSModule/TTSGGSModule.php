<?php

use ModulesGarden\Servers\TTSGGSModule\app\controllers\server\admin\Actions;
use ModulesGarden\Servers\TTSGGSModule\Server;

if(!defined('DS'))define('DS',DIRECTORY_SEPARATOR);

require_once __DIR__.DS.'Loader.php';
new \ModulesGarden\Servers\TTSGGSModule\Loader();
\ModulesGarden\Servers\TTSGGSModule\Server::I();



if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function TTSGGSModule_MetaData() {
    return array(
        'DisplayName' => 'The SSL Store & GoGetSSL Module',
        'APIVersion' => '1.0',
    );
}

function TTSGGSModule_ConfigOptions() {
    return (new Actions())->ConfigOptions();
}

function TTSGGSModule_CreateAccount($params) {
    return (new Actions())->CreateAccount($params);
}

function TTSGGSModule_SuspendAccount($params) {
    return (new Actions())->SuspendAccount($params);
}

function TTSGGSModule_UnsuspendAccount($params) {
    return (new Actions())->UnsuspendAccount($params);
}

function TTSGGSModule_SSLStepOne($params) {
    return (new Actions())->SSLStepOne($params);
}

function TTSGGSModule_SSLStepTwo($params) {
    return (new Actions())->SSLStepTwo($params);
}

function TTSGGSModule_SSLStepThree($params) {
    return (new Actions())->SSLStepThree($params);
}

function TTSGGSModule_TerminateAccount($params) {
    return (new Actions())->TerminateAccount($params);
}

function TTSGGSModule_AdminCustomButtonArray() {
    //return (new Actions())->AdminCustomButtonArray();
}

function TTSGGSModule_SSLAdminResendApproverEmail($params) {
    return (new Actions())->ResendApproverEmail($params);
}

function TTSGGSModule_SSLAdminResendCertificate($params) {
    return (new Actions())->ResendCertificate($params);
}

function TTSGGSModule_Renew($params) {
    return (new Actions())->Renew($params);
}

function TTSGGSModule_AdminServicesTabFields(array $params) {
    return (new Actions())->AdminServicesTabFields($params);
}

function TTSGGSModule_SSLAdminGetCertificate($params) {
    return (new Actions())->GetCertificate($params);
}

function TTSGGSModule_ClientAreaCustomReissueCertificate($params) {
    return (new Actions())->ReissueCertificate($params);
}

function TTSGGSModule_ClientAreaCustomContactDetails($params) {
    return (new Actions())->ContactDetails($params);
}

function TTSGGSModule_ClientArea(array $params) {
    
    if(!empty($_REQUEST['json']))
    {
        header('Content-Type: text/plain');
        echo Server::getJSONClientAreaPage($params, $_REQUEST);
        die();
    }

    return Server::getHTMLClientAreaPage($params, $_REQUEST);
}
