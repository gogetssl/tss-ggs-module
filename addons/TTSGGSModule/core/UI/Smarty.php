<?php

namespace ModulesGarden\TTSGGSModule\Core\UI;

use ModulesGarden\TTSGGSModule\Core\ModuleConstants;
use ModulesGarden\TTSGGSModule\Core\ServiceLocator;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\LogActivity;

/**
 * Smarty Wrapper
 *
 *
 * @SuppressWarnings(PHPMD)
 */
class Smarty
{
    private $lang;
    private $smarty;
    private $templateDIR;

    public function __construct()
    {
        $this->smarty = new \Smarty();
    }

    public function setLang($land)
    {
        $this->lang = $land;

        return $this;
    }

    /**
     * Set Tempalte Dir
     *
     *
     * @param string $dir
     */
    public function setTemplateDir($dir)
    {
        if (is_array($dir))
        {
            LogActivity::error("Wrong Template Path :" . $dir);
        }
        $this->templateDIR = $dir;

        return $this;
    }

    /**
     * Parse Template File
     *
     *
     * @param string $template
     * @param array $vars
     * @param string $customDir
     * @return string
     * @throws exceptions\System
     * @global string $templates_compiledir
     */
    public function view($template, $vars = [], $customDir = false)
    {
        if (is_array($customDir))
        {
            throw new \Exception("Wrong template path: " . $customDir);
        }

        global $templates_compiledir;
        if ($customDir)
        {
            $this->smarty->template_dir = $customDir;
        }
        else
        {
            $this->smarty->template_dir = $this->templateDIR;
        }

        $this->smarty->compile_dir   = $templates_compiledir;
        $this->smarty->force_compile = 1;
        $this->smarty->caching       = 0;

        $this->clear();

        $this->smarty->assign('MGLANG', $this->lang);

        if (is_array($vars))
        {
            foreach ($vars as $key => $val)
            {
                $this->smarty->assign($key, $val);
            }
        }

        if (is_array($this->smarty->template_dir))
        {
            $file = $this->smarty->template_dir[0] . DIRECTORY_SEPARATOR . $template . '.tpl';
        }
        else
        {
            $file = $this->smarty->template_dir . DIRECTORY_SEPARATOR . $template . '.tpl';
        }

        if (!file_exists($file))
        {
            throw new \Exception('Unable to find Template: ' . $file);
        }

        if (isset($vars['isError']) && $vars['isError'] === false || !isset($vars['isError']) || ServiceLocator::$isDebug === false)
        {
            return $this->smarty->fetch($template . '.tpl', uniqid());
        }
        else
        {
            $template = ModuleConstants::getResourcesDir() . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'ui' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'default' . DIRECTORY_SEPARATOR;

            return $this->smarty->fetch($template . 'errorComponent.tpl', uniqid());
        }
    }

    protected function clear()
    {
        if (method_exists($this->smarty, 'clearAllAssign'))
        {
            $this->smarty->clearAllAssign();
        }
        elseif (method_exists($this->smarty, 'clear_all_assign'))
        {
            $this->smarty->clear_all_assign();
        }
    }

    public function fetch($templateStr, $vars = [])
    {

        global $templates_compiledir;
        $this->smarty->compile_dir   = $templates_compiledir;
        $this->smarty->force_compile = 1;
        $this->smarty->caching       = 0;
        $this->clear();
        $this->smarty->assign('MGLANG', $this->lang);

        if (is_array($vars))
        {
            foreach ($vars as $key => $val)
            {
                $this->smarty->assign($key, $val);
            }
        }
        return $this->smarty->fetch('string:' . $templateStr, uniqid());
    }
}
