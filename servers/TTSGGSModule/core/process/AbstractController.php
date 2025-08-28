<?php

namespace ModulesGarden\Servers\TTSGGSModule\core\process;

use ModulesGarden\Servers\TTSGGSModule\core\Lang;
use ModulesGarden\Servers\TTSGGSModule\core\Smarty;

abstract class AbstractController {
    public $mgToken = null;
    private $registredValidationErros = [];

    function __construct($input = null) {
        if(isset($input['mg-token']))
        {
            $this->mgToken = $input['mg-token'];
        }
    }

    function genToken(){
        return md5(time());
    }

    function checkToken($token = null){
        if($token === null)
        {
            $token = $this->mgToken;
        }

        if($_SESSION['mg-token'] === $token)
        {
            return false;
        }

        $_SESSION['mg-token'] = $token;

        return true;
    }

    function dataTablesParseRow($template,$data){
        $row = Smarty::I()->view($template,$data);

        $output = array();

        if(preg_match_all('/\<td\>(?P<col>.*?)\<\/td\>/s', $row, $result))
        {
            foreach($result['col'] as $col)
            {
                $output[] = $col;
            }
        }

        return $output;
    }

    function registerErrors($errors){
        $this->registredValidationErros = $errors;
    }

    function getFieldError($field,$langspace='validationErrors'){
        if(!isset($this->registredValidationErros[$field]))
        {
            return false;
        }

        $message = array();
        foreach($this->registredValidationErros[$field] as $type)
        {
            $message[] = Lang::absoluteT($langspace,$type);
        }

        return implode(',',$message);
    }

    public function isActive(){
        return true;
    }
}