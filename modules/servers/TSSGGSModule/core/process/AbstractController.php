<?php

namespace TSSGGSModule\Server\core\process;

use TSSGGSModule\Server\core\Lang;
use TSSGGSModule\Server\core\Smarty;

abstract class AbstractController {
    public $modToken = null;
    private $registredValidationErros = [];

    function __construct($input = null) {
        if(isset($input['mod-token']))
        {
            $this->modToken = $input['mod-token'];
        }
    }

    function genToken(){
        return md5(time());
    }

    function checkToken($token = null){
        if($token === null)
        {
            $token = $this->modToken;
        }

        if($_SESSION['mod-token'] === $token)
        {
            return false;
        }

        $_SESSION['mod-token'] = $token;

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