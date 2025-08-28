<?php

namespace ModulesGarden\TTSGGSModule\App\Helpers;

use ModulesGarden\TTSGGSModule\Packages\ModuleSettings\Models\ModuleSettings as ModuleSettingsModel;

/**
 * ModuleSettings - wrapper for module settings
 */
class ModuleSettings
{
    protected $settings = [];

    public function __construct()
    {
        $this->loadSettings();
    }

    protected function loadSettings()
    {
        $model = new ModuleSettingsModel();

        $this->settings = $model->pluck('value', 'setting')->all();
    }

    public function getSettings()
    {
        return $this->settings;
    }

    public function getSetting($key, $defaultValue = null)
    {
        return isset($this->settings[$key]) ? $this->settings[$key] : $defaultValue;
    }

    public function refreshSettingsData()
    {
        $this->loadSettings();

        return $this;
    }
}
