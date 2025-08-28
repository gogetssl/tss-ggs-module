<?php

namespace ModulesGarden\TTSGGSModule\Core\UI\Widget\Graphs\Settings;

use ModulesGarden\TTSGGSModule\Core\UI\Widget\Buttons\ButtonDatatableModalContextLang;

/**
 * Description of SettingButton
 */
class SettingButton extends ButtonDatatableModalContextLang
{
    protected $configFields = [];
    protected $icon = 'mdi-pencil';
    protected $id = 'settingButton';
    protected $name = 'settingButton';
    protected $title = 'settingButton';

    public function addNamespaceScope($namespaceScope = null)
    {
        $this->namespaceScope = $namespaceScope;

        return $this;
    }

    public function getNamespace()
    {
        if ($this->namespaceScope)
        {
            return $this->namespaceScope;
        }

        return parent::getNamespace();
    }

    public function initContent()
    {
        $modal = new SettingModal();
        $modal->setConfigFields($this->configFields);
        $this->initLoadModalAction($modal);
    }

    public function setConfigFields($fieldsList = [])
    {
        if ($fieldsList)
        {
            $this->configFields = $fieldsList;
        }

        return $this;
    }
}
