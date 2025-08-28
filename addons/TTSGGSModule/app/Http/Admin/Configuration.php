<?php

namespace ModulesGarden\TTSGGSModule\App\Http\Admin;

use ModulesGarden\TTSGGSModule\App\UI\Admin\Configuration\Pages\ConfigurationStep1;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Configuration\Pages\ConfigurationStep2;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Configuration\Pages\ConfigurationStep3;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Configuration\Pages\ConfigurationStep4;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Configuration\Pages\ConfigurationStep5;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Configuration\Pages\ConfigurationStep6;
use ModulesGarden\TTSGGSModule\App\UI\Admin\Configuration\Pages\ConfigurationStep7;
use ModulesGarden\TTSGGSModule\Core\Contracts\Controllers\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Http\AbstractController;
use ModulesGarden\TTSGGSModule\Core\Helper;

class Configuration extends AbstractController implements AdminAreaInterface
{
    public function index()
    {
        return Helper\redirect('configuration', 'step1');
    }

    public function step1()
    {
        return Helper\view()
            ->addElement(ConfigurationStep1::class);
    }

    public function step2()
    {
        return Helper\view()
            ->addElement(ConfigurationStep2::class);
    }
    public function step3()
    {
        return Helper\view()
            ->addElement(ConfigurationStep3::class);
    }

    public function step4()
    {
        return Helper\view()
            ->addElement(ConfigurationStep4::class);
    }

    public function step5()
    {
        return Helper\view()
            ->addElement(ConfigurationStep5::class);
    }

    public function step6()
    {
        return Helper\view()
            ->addElement(ConfigurationStep6::class);
    }

    public function step7()
    {
        return Helper\view()
            ->addElement(ConfigurationStep7::class);
    }


}
