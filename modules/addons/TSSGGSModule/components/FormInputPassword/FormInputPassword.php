<?php

namespace ModulesGarden\TSSGGSModule\Components\FormInputPassword;

use ModulesGarden\TSSGGSModule\Components\FormInputText\FormInputText;

/**
 * Class IconButton
 */
class FormInputPassword extends FormInputText
{
    public const COMPONENT = 'FormInputText';

    public function __construct()
    {
        parent::__construct();

        $this->setType('password');
        // module password fields hold API tokens, never the administrator's login: without this the browser fills in
        // the saved WHMCS password, which a save would then store as the token
        $this->setAutocomplete('new-password');
    }
}
