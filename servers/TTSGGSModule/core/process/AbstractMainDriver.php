<?php

namespace ModulesGarden\Servers\TTSGGSModule\core\process;

use ModulesGarden\Servers\TTSGGSModule\core\Lang;
use ModulesGarden\Servers\TTSGGSModule\core\Smarty;

abstract class AbstractMainDriver{

    static private $_instance;


    public $_debug = false;


    public $isLoaded;


    private $_configuration;


    private $_isAdmin = false;


    private $encryptSecureKey;


    private $_mainNamespace;

    private $_mainDIR;

    private function __construct() {;}
    private function __clone() {;}

    public static function I($force = false, $configs = []){
        if(empty(self::$_instance) || $force)
        {
            $class = get_called_class();

            MainInstance::setInstanceName($class);

            self::$_instance = new $class();
            self::$_instance->_mainNamespace = substr(__NAMESPACE__,0,  strpos(__NAMESPACE__, '\core'));
            self::$_instance->_mainDIR = call_user_func([$class,'getMainDIR']);
            $class= self::$_instance->_mainNamespace.'\Configuration';

            self::$_instance->_configuration = new $class();

            foreach($configs as $name => $value)
            {
                self::$_instance->_configuration->{$name} = $value;
            }

            self::$_instance->isLoaded = true;

            if(!empty(self::$_instance->_configuration->debug))
            {
                self::$_instance->_debug = true;
            }

            //main\mgLibs\MySQL\Query::useCurrentConnection();
            Lang::getInstance(self::$_instance->_mainDIR.DS.'langs');
        }

        return self::$_instance;
    }

    function configuration(){
        return $this->_configuration;
    }

    public function isAdmin($status = null){
        if($status !== null)
        {
            $this->_isAdmin = $status;
        }
        return $this->_isAdmin;
    }

    public function isDebug(){
        return $this->_debug;
    }

    function getMainNamespace(){
        return $this->_mainNamespace;
    }

    public function getEncryptKey(){

        if(empty($this->encryptSecureKey))
        {
            $this->encryptSecureKey = hash('sha256', $GLOBALS['cc_encryption_hash'].$this->configuration()->encryptHash,TRUE);
        }

        return $this->encryptSecureKey;
    }

    function setMainLangContext(){
        Lang::setContext($this->getType().($this->isAdmin()?'AA':'CA'));
    }

    function runControler($controller,$action = 'index',$input = [], $type = 'HTML'){
        try{
            $className = $this->getMainNamespace()."\\app\\controllers\\".$this->getType()."\\".($this->_isAdmin?'admin':'clientarea')."\\".$controller;

            if(!class_exists($className))
            {
                throw new \Exception("Unable to find page");
            }

            $controllerOBJ = new $className($input);

            if(method_exists($controllerOBJ, "isActive") && !$controllerOBJ->{"isActive"}())
                throw new \Exception("No access to this page");

            if(!method_exists($controllerOBJ, $action.$type))
            {
                throw new \Exception("Unable to find Action: ".$action.$type);
            }

            Lang::stagCurrentContext('generate'.$controller);

            Lang::addToContext(lcfirst ($controller));

            Smarty::I()->setTemplateDir(self::I()->getModuleTemplatesDir().DS.'pages'.DS.lcfirst ($controller));

            $result = $controllerOBJ->{$action.$type}($input);

            switch ($type)
            {
                case 'HTML':
                    if(empty($result['tpl']))
                    {
                        throw new \Exception("Provide Template Name");
                    }

                    $success = isset($result['vars']['success'])?$result['vars']['success']:false;
                    $error = isset($result['vars']['error'])?$result['vars']['error']:false;
                    $result = Smarty::I()->view($result['tpl'], $result['vars']);

                    break;
                default:
                    $success = isset($result['success'])?$result['success']:false;
                    $error = isset($result['error'])?$result['error']:false;
            }

            Lang::unstagContext('generate'.$controller);

            return [$result,$success,$error];

        } catch (\Exception $ex) {

            Lang::unstagContext('generate'.$controller);
            throw $ex;
            return false;

        }
    }

    static function dump($input)
    {
        if(self::I()->isDebug())
        {
            echo "<pre>";
            print_r($input);
            echo "</pre>";
        }
    }

    abstract public function getAssetsURL();
    abstract public function getType();

    public static function getMainDIR(){
        return false;
    }
    public static function getUrl(){
        return false;
    }

    public function isActive(){
        return true;
    }
}