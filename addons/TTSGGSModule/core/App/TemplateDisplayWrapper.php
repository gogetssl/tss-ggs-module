<?php

namespace ModulesGarden\TTSGGSModule\Core\App;

use ModulesGarden\TTSGGSModule\Core\Support\Facades\Smarty;
use ModulesGarden\TTSGGSModule\Core\Traits\Lang;

/**
 * Template Display Wrapper
 */
class TemplateDisplayWrapper
{
    use Lang;

    protected $templateDir = null;
    protected $templateName = null;
    protected $vars = [];

    public function __construct($templateName = null, $templateDir = null, $vars = [], $lang = null)
    {
        $this->setTemplate($templateName, $templateDir);
        $this->setVars($vars);
        $this->setLang($lang);
    }

    public function setTemplate($templateName = null, $templateDir = null)
    {
        if (file_exists($templateDir . DIRECTORY_SEPARATOR . $templateName . '.tpl'))
        {
            $this->templateName = $templateName;
            $this->templateDir  = $templateDir;
        }
    }

    public function setVars($vars = [])
    {
        if (is_array($vars))
        {
            $this->vars = $vars;
        }
    }

    public function setLang($lang = null)
    {
        if ($lang instanceof Lang)
        {
            $this->lang = $lang;
        }

    }

    public function toHtml()
    {
        $pageContent = Smarty::setLang($this->lang)
            ->setTemplateDir($path)
            ->view($fileName, $vars);

        return $pageContent;
    }
}
