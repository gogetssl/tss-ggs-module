<?php

namespace ModulesGarden\TTSGGSModule\Core\Translation;

use ModulesGarden\TTSGGSModule\Core\Support\Facades\Session;
use ModulesGarden\TTSGGSModule\Core\WHMCS\Config\Config;

class Selector
{
    protected ?string $language = null;

    public function getLanguage(): string
    {
        $this->checkForAdminLanguage();
        $this->checkForClientLanguage();
        $this->checkForDefaultLanguage();

        return strtolower($this->language);
    }

    /**
     * Find language of currently logged in administrator
     * @return void
     */
    protected function checkForAdminLanguage(): void
    {
        if (!defined('ADMINAREA'))
        {
            return;
        }

        if ($admin = (new \WHMCS\Authentication\CurrentUser)->admin())
        {
            $this->language = $admin->language;
        }
    }

    /**
     * Find languiage of currently logged in client
     * @return void
     */
    protected function checkForClientLanguage(): void
    {
        if (!defined('CLIENTAREA'))
        {
            return;
        }

        $this->language = Session::get('Language', (new \WHMCS\Authentication\CurrentUser)->client()->language);
    }

    /**
     * Load default language from WHMCS settings
     * @return void
     */
    protected function checkForDefaultLanguage()
    {
        if (!$this->language)
        {
            $this->language = (new Config)->get('Language');
        }
    }
}
