<?php

namespace TSSGGSModule\Server\core\process;

abstract class AbstractConfiguration{
    public $debug = false;

    public $systemName = false;

    public $name = false;

    public $description = false;

    public $clientareaName = false;

    public $encryptHash = false;

    public $version = false;

    public $author = '';

    public $tablePrefix = false;

    public $modelRegister = [];

    private $_customConfigs = [];

    public function __isset($name) {
        return isset($this->_customConfigs[$name]);
    }

    public function __get($name) {
        if(isset($this->_customConfigs[$name]))
        {
            return $this->_customConfigs[$name];
        }
    }

    public function __set($name, $value) {
        $this->_customConfigs[$name] = $value;
    }

    public function getAddonMenu(){
        return array();
    }

    public function getAddonWHMCSConfig(){
        return array();
    }

    public function getServerConfigController(){
        return 'configuration';
    }

    public function getServerActionsController(){
        return 'actions';
    }

    public function getServerCAController(){
        return 'home';
    }

    public function getAddonAdminController(){
        return 'actions';
    }

    public function getAddonCAController(){
        return 'home';
    }
}