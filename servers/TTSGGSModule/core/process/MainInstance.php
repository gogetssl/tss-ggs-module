<?php

namespace ModulesGarden\Servers\TTSGGSModule\core\process;

class MainInstance {
    static private $_instanceName;

    public static function setInstanceName($instance){
        self::$_instanceName = $instance;
    }

    public static function __callStatic($name, $arguments) {
        return call_user_func(array(self::$_instanceName,$name),$arguments);
    }

    static function I(){
        if(empty(self::$_instanceName))
        {
            throw new \Exception('Instance is not set');
        }
        return call_user_func(array(self::$_instanceName,'I'));
    }
}

