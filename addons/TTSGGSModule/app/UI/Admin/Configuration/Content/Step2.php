<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Configuration\Content;

use ModulesGarden\TTSGGSModule\App\Models\Whmcs\Configuration;
use ModulesGarden\TTSGGSModule\Components\Alert\AlertDanger;
use ModulesGarden\TTSGGSModule\Components\Alert\AlertSuccess;
use ModulesGarden\TTSGGSModule\Components\Button\ButtonSuccess;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Core\Components\Action;
use ModulesGarden\TTSGGSModule\Core\Translation\TranslatorTrait;

class Step2 extends Widget implements AjaxComponentInterface
{
    use TranslatorTrait;

    public function loadHtml(): void
    {
        $phpVersion = phpversion();
        $whmcsVersion = '';
        $whmcsConfiguration = Configuration::where('setting', 'Version')->first()->toArray();
        if (isset($whmcsConfiguration['value']) && !empty($whmcsConfiguration['value'])) $whmcsVersion = $whmcsConfiguration['value'];
        $intlExtension = extension_loaded('intl');

        $icuVersion = false;
        if ($intlExtension !== false) {
            // check ICU
        }

        $buttonStatus = true;

        $checkDir = dirname(dirname(dirname(dirname(dirname(__DIR__))))) . DIRECTORY_SEPARATOR . 'storage';


        $this->setTitle($this->translate('step2_title'));

        if (version_compare($phpVersion, '8.1.0') >= 0)
        {
            $alert = new AlertSuccess();
            $alert->setText($this->translate('php_alert_success'));
            $this->addElement($alert);
        }
        else
        {
            $alert = new AlertDanger();
            $alert->setText($this->translate('php_alert_danger'));
            $this->addElement($alert);
            $buttonStatus = false;
        }

        $whmcsVersion = str_replace('-beta', '', $whmcsVersion);
        if (version_compare($whmcsVersion, '8.6.0') >= 0) {

            $alert = new AlertSuccess();
            $alert->setText($this->translate('whmcs_alert_success'));
            $this->addElement($alert);
        }
        else
        {
            $alert = new AlertDanger();
            $alert->setText($this->translate('whmcs_alert_danger'));
            $this->addElement($alert);
            $buttonStatus = false;
        }

//        if ($intlExtension !== false)
//        {
//            $alert = new AlertSuccess();
//            $alert->setText($this->translate('intl_alert_success'));
//            $this->addElement($alert);
//        }
//        else
//        {
//            $alert = new AlertDanger();
//            $alert->setText($this->translate('intl_alert_danger'));
//            $this->addElement($alert);
//            $buttonStatus = false;
//        }
//
//        if($icuVersion !== false)
//        {
//            $alert = new AlertSuccess();
//            $alert->setText($this->translate('icu_alert_success'));
//            $this->addElement($alert);
//        }
//        else
//        {
//            $alert = new AlertDanger();
//            $alert->setText($this->translate('icu_alert_danger'));
//            $this->addElement($alert);
//            $buttonStatus = false;
//        }

        if (!is_writable($checkDir))
        {
            $alert = new AlertDanger();
            $alert->setText($this->translate('config_folder_alert_danger'));
            $this->addElement($alert);
            $buttonStatus = false;
        }
        else
        {
            $alert = new AlertSuccess();
            $alert->setText($this->translate('config_folder_alert_success'));
            $this->addElement($alert);
        }





        $nextStep = new ButtonSuccess();
        $nextStep->setCss('lu-btn lu-btn--success mt30_custom');
        $nextStep->onClick(Action::redirect('addonmodules.php?module=TTSGGSModule&mg-page=configuration&mg-action=step3'));
        $nextStep->setTitle($this->translate('next_step_btn'));
        if($buttonStatus === false)
        {
            $nextStep->setDisabled();
        }

        $this->addElement($nextStep);


    }
}