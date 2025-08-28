<?php

namespace ModulesGarden\TTSGGSModule\Components\AppFooter;

use ModulesGarden\TTSGGSModule\Core\Components\AbstractComponent;

class AppFooter extends AbstractComponent
{
    public const COMPONENT = 'AppFooter';

    public function __construct()
    {
        parent::__construct();

        $this->setTranslations([
            'module_version',
        ]);
    }

    public function setModuleName($name): self
    {
        $this->setSlot('moduleName', $name);

        return $this;
    }

    public function setModuleVersion($version): self
    {
        $this->setSlot('moduleVersion', $version);

        return $this;
    }

    public function hideModuleVersion(bool $hideModuleVersion = true): self
    {
        $this->setSlot('hideModuleVersion', $hideModuleVersion);

        return $this;
    }
}