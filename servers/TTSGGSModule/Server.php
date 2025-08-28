<?php

namespace ModulesGarden\Servers\TTSGGSModule;

use ModulesGarden\Servers\TTSGGSModule\core\process\AbstractMainDriver;
use ModulesGarden\Servers\TTSGGSModule\core\Lang;
use ModulesGarden\Servers\TTSGGSModule\core\Smarty;

class Server extends AbstractMainDriver {

    static function getModuleTemplatesDir($relative = false){
        
        $dir = ($relative)?'':(__DIR__.DS);
        
        $dir .= 'templates'.DS;
        
        if(self::I()->isAdmin())
        {
            return $dir.'admin';
        }
        else
        {
            $template = $GLOBALS['CONFIG']['Template'];

            if(file_exists(__DIR__.DS.'templates'.DS.'clientarea'.DS.$template))
            {
                return $dir.'clientarea'.DS.$template;
            }
            else
            {
                return $dir.'clientarea'.DS.'default';
            }
        }
    }
    
    public function getAssetsURL() {
        if($this->isAdmin())
        {
            return '../modules/servers/'.$this->configuration()->systemName.'/templates/admin/assets';
        }
        else
        {
            return 'modules/servers/'.$this->configuration()->systemName.'/'.self::getModuleTemplatesDir(true).'/assets';
        }
    }
    
    public function getType() {
        return 'server';
    }

    public static function getMainDIR(){
        return __DIR__;
    }
    
    public static function getUrl() {
        return false;
    }

    static function genJSONUrl($page){
        if($this->isAdmin())
        {
            return false;
        }
        else
        {
            return 'modules/servers/'.$this->configuration()->systemName.'/'.self::getModuleTemplatesDir(true).'/assets';
        }
    }

    static function getHTMLClientAreaPage($params, $input){
        try{
            
            self::I()->isAdmin(false);
            self::I()->setMainLangContext();
            try{
                $page   = empty($input['mg-page'])?'home':$input['mg-page']; 
                $action = empty($input['mg-action'])?'index':$input['mg-action'];

                $input['params'] = $params;

                $output = ['templatefile' => self::I()->getModuleTemplatesDir(true).'/main'];

                $vars['assetsURL'] = self::I()->getAssetsURL();
                $vars['mainJSONURL'] = 'clientarea.php?action=productdetails&id='.$params['serviceid'].'&json=true';
                
                list($content,$success,$error) = self::I()->runControler($page,$action,$input,'HTML');

                if(self::I()->isDebug())
                {
                    $html = '<div style="color: #a94442;background-color: #f2dede;border-color: #dca7a7;font-size:20px;padding:10px;"><strong>Module is under development Mode!!!!!!!!!!!!!!!</strong></div>';
                    
                    if($langs = Lang::getMissingLangs())
                    {
                        $html .= '<pre>';
                        foreach($langs as $lk => $lang)
                        {
                            $html .= $lk." = '".$lang."';\n";
                        }
                        $html .= '</pre>';
                    }
                    
                    $content = $html.$content;
                }
                $vars['content'] = $content;
                $vars['success'] = $success;
                $vars['error'] = $error;
                
            } catch (\Exception $ex) {                
                self::dump($ex);
                $vars['error'] = Lang::absoluteT('generalError');
                if(method_exists($ex, 'getToken'))
                {
                    $vars['error'] .= ' '.Lang::absoluteT('token').$ex->getToken();
                }
            }

            $output['vars'] = $vars;
            return $output;

        } catch (\Exception $ex) {

            self::dump($ex);
            return $ex->getMessage();

        }
    }

    static function getJSONClientAreaPage($params, array $input = [])
    {
        try{
            self::I()->isAdmin(false);
            self::I()->setMainLangContext();
            $content = [];

            $page   = empty($input['mg-page'])?'home':$input['mg-page'];
            $action = empty($input['mg-action'])?'index':$input['mg-action'];
            
            $input['params'] = $params;
            
            try{
                
                list($result,$success,$error) = self::I()->runControler($page,$action,$input,'JSON'); 

                if($error)
                {
                    $content['error']  = $error;
                    $content['result'] = 'error';
                }
                elseif($success)
                {
                    $content['success'] = $success;
                    $content['result']  = 'success';
                }

                if($langs = Lang::getMissingLangs())
                {
                    $html = '<pre>';
                    foreach($langs as $lk => $lang)
                    {
                        $html .= $lk." = '".$lang."';\n";
                    }
                    $html .= '</pre>';

                    $content['error']  = $html;
                    $content['result'] = 'error';
                }

                $content['data']    = $result;

            } catch (\Exception $ex) {

                self::dump($ex);
                $content['result'] = 'error';
                if($ex->getMessage()) {
                    $content['error'] = $ex->getMessage();
                } else {
                    $content['error'] = Lang::absoluteT('generalError');
                }
                if(method_exists($ex, 'getToken'))
                {
                    $content['error'] .= ' '.Lang::absoluteT('token').$ex->getToken();
                }

            }

            return json_encode($content);

        } catch (\Exception $ex) {

            self::dump($ex);
            return $ex->getMessage();

        }
    }
    

    static function accountAction($action, $input)
    {
        try{
            self::I()->isAdmin(true);
            self::I()->setMainLangContext();
            
            try{
                self::I()->runControler('actions',$action,['params' => $input],'Action');
                return 'success';
            } 
            catch (\Exception $ex)
            {
                if(self::I()->isDebug())
                {
                    self::dump($ex);
                    die();
                }

                $message = $ex->getMessage();
                if(method_exists($ex, 'getToken'))
                {
                    $message .= ' '.Lang::absoluteT('token').$ex->getToken();
                }
                return $message;
            }

        } catch (\Exception $ex) {

            self::dump($ex);
            return $ex->getMessage();

        }
    }

    static function getHTMLProductConfigPage($input){
        try{     
            self::I()->isAdmin(true);
            self::I()->setMainLangContext();
            
            $page   = empty($input['mg-page'])?'configuration':$input['mg-page']; 
            $action = empty($input['mg-action'])?'index':$input['mg-action'];
            
            $input['params'] = ['pid' => $input['id']];
            
            $vars = [
                'mainURL'      => 'configproducts.php?action=edit&id='.$input['id'].'&tab=2',
                'mainName'     => self::I()->configuration()->name,
                'mainJSONURL'  => 'configproducts.php?action=edit&id='.$input['id'].'&json=1',
                'assetsURL'    => self::I()->getAssetsURL()
            ];
            
            foreach(self::I()->configuration()->getServerMenu() as $pageName => $pageConfig){
                $pageConf = [
                    'title' => Lang::absoluteT('pages',$pageName,'title'),
                    'url'  => 'configproducts.php?action=edit&id='.$input['id'].'&tab=2&mg-page='.$pageName
                ];
                
                if(!empty($pageConfig['icon']))
                {
                    $pageConf['icon'] = $pageConfig['icon'];
                }

                $vars['menu'][$pageName] = $pageConf;
            }

            try{

                if(self::I()->isDebug())
                {
                    self::I()->configuration()->activate();
                }
                
                list($content,$success,$error) = self::I()->runControler($page,$action,$input,'HTML');
                
                $vars['content'] = $content;
                $vars['success'] = $success;
                $vars['error'] = $error;

            } catch (\Exception $ex) {

                self::dump($ex);
                $vars['error'] = $ex->getMessage();
                if(method_exists($ex, 'getToken'))
                {
                    $vars['error'] .= '. '.Lang::absoluteT('token').$ex->getToken();
                }
            }

            Smarty::I()->setTemplateDir(self::I()->getModuleTemplatesDir());
            $content = Smarty::I()->view('main',$vars);
            
            if(self::I()->isDebug())
            {
                $html = '<div style="color: #a94442;background-color: #f2dede;border-color: #dca7a7;font-size:20px;padding:10px;"><strong>Module is under development Mode!!!!!!!!!!!!!!!</strong></div>';

                if($langs = Lang::getMissingLangs())
                {
                    $html .= '<pre>';
                    foreach($langs as $lk => $lang)
                    {
                        $html .= $lk." = '".$lang."';\n";
                    }
                    $html .= '</pre>';
                }

                $content = $html.$content;
            }
            
            return $content;
            
        } catch (\Exception $ex) {

            self::dump($ex);
            return $ex->getMessage();

        }
    }

    static function getJSONProductConfigPage($input){
        try{
            self::I()->isAdmin(true);
            self::I()->setMainLangContext();
            
            $content = array();

            $page   = empty($input['mg-page'])?'configuration':$input['mg-page']; 
            $action = empty($input['mg-action'])?'index':$input['mg-action'];

            $input['params'] = ['pid' => $input['id']];
            
            try{

                list($result,$success,$error) = self::I()->runControler($page,$action,$input,'JSON');
                
                if($error)
                {
                    $content['error']  = $error;
                    $content['result'] = 'error';
                }
                elseif($success)
                {
                    $content['success'] = $success;
                    $content['result']  = 'success';
                }

                if($langs = Lang::getMissingLangs())
                {
                    $html = '<pre>';
                    foreach($langs as $lk => $lang)
                    {
                        $html .= $lk." = '".$lang."';\n";
                    }
                    $html .= '</pre>';

                    $content['error']  = $html;
                    $content['result'] = 'error';
                }
            
                $content['data']    = $result;
            } catch (\Exception $ex) {

                self::dump($ex);
                $content['result'] = 'error';
                $content['error']  = $ex->getMessage();
                if(method_exists($ex, 'getToken'))
                {
                    $content['error'] .= ' '.Lang::absoluteT('token').$ex->getToken();
                }

            }

            return '<JSONRESPONSE#'.json_encode($content).'#ENDJSONRESPONSE>';

        } catch (\Exception $ex) {

            self::dump($ex);
            return $ex->getMessage();

        }
    }

    static function configArray(){        
        if(method_exists(self::I()->configuration(), 'getServerWHMCSConfig'))
        {
            $config = self::I()->configuration()->getServerWHMCSConfig();
            if(is_array($config))
            {
                return array_fill_keys($config, ["Type" => "text"]);
            }
        }
        return [];
    }
}
