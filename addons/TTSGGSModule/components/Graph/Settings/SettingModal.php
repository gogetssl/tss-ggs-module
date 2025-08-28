<?php

namespace ModulesGarden\TTSGGSModule\Core\UI\Widget\Graphs\Settings;

use ModulesGarden\TTSGGSModule\Core\UI\Widget\Modals\BaseEditModal;

/**
 * Description of SettingModal
 */
class SettingModal extends BaseEditModal
{
    protected $configFields = [];
    protected $id = 'settingModal';
    protected $name = 'settingModal';
    protected $title = 'settingModal';

    public function initContent()
    {
        $form = new SettingForm();
        $form->setConfigFields($this->configFields);
        $this->addForm($form);
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
