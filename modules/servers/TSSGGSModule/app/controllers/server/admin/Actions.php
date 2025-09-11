<?php

namespace ModulesGarden\Servers\TSSGGSModule\app\controllers\server\admin;


use ModulesGarden\Servers\TSSGGSModule\app\services\AdminServicesTabFields;
use ModulesGarden\Servers\TSSGGSModule\app\services\CreateAccount;
use ModulesGarden\Servers\TSSGGSModule\app\services\Renew;
use ModulesGarden\Servers\TSSGGSModule\app\services\SSLStepOne;
use ModulesGarden\Servers\TSSGGSModule\app\services\SSLStepTwo;
use ModulesGarden\Servers\TSSGGSModule\app\services\SSLStepThree;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;

class Actions {

    public function ConfigOptions(){

        if($_POST['action'] == 'save')
        {
            return [];
        }

        $data['mode'] = "advanced";
        $data['content'] = '<div class="errorbox"><strong><span class="title">Module Information</span></strong><br>This module can only be managed from the TSSGGSModule addon module.</div>';
        ob_clean();
        header('Content-Type: application/json');

        echo json_encode($data);
        die();
    }

    public function CreateAccount($params)
    {
        try {

            return (new CreateAccount())->run($params);

        } catch (\Exception $e) {

            return $e->getMessage();

        }
    }

    public function SuspendAccount($params)
    {
        return 'success';
    }

    public function UnsuspendAccount($params)
    {
        return 'success';
    }

    public function TerminateAccount($params)
    {
        return 'success';
    }

    public function Renew($params)
    {
        try {

            return (new Renew())->run($params);

        } catch (\Exception $e) {

            return $e->getMessage();

        }
    }

    public function SSLStepOne($params)
    {
        try {

            return (new SSLStepOne())->run($params);

        } catch (\Exception $e) {

            return $e->getMessage();

        }
    }

    public function SSLStepTwo($params)
    {
        try {

            return (new SSLStepTwo())->run($params);

        } catch (\Exception $e) {

            return $e->getMessage();

        }
    }

    public function SSLStepThree($params)
    {
        try {

            $params = array_merge($_POST, $params);
            return (new SSLStepThree())->run($params);

        } catch (\Exception $e) {

            return $e->getMessage();

        }
    }

    public function AdminServicesTabFields($params)
    {
        try {

            return (new AdminServicesTabFields())->run($params);

        } catch (\Exception $e) {

            return $e->getMessage();

        }
    }

}

