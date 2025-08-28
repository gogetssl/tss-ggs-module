<?php

namespace ModulesGarden\TTSGGSModule\Core\UI\View;

use ModulesGarden\TTSGGSModule\Components\AppFooter\AppFooter;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Config;

class FooterBuilder
{
    protected AppFooter $appFooter;
    public function __construct()
    {
        $this->appFooter = new AppFooter();
    }

    public function create(): AppFooter
    {
        $this->configureFooter();

        return  $this->appFooter;
    }

    public function configureFooter(): void
    {
        $this->appFooter->setModuleName(Config::get('configuration.systemName'));
        $this->appFooter->setModuleVersion(Config::get('configuration.version'));
        $this->appFooter->hideModuleVersion(Config::get('configuration.hideModuleVersion', false));
    }
}