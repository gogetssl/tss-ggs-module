<?php

namespace ModulesGarden\TTSGGSModule\Core\Traits;

/**
 * @deprecated
 */
trait Template
{
    protected $templateDir = null;
    protected $templateName = null;

    /**
     * @return string|null
     * @deprecated
     */
    public function getTemplateDir()
    {
        return $this->templateDir;
    }

    /**
     * @deprecated
     */
    public function setTemplateDir($templateDir)
    {
        $this->templateDir = $templateDir;

        return $this;
    }

    /**
     * @return string|null
     * @deprecated
     */
    public function getTemplateName()
    {
        return $this->templateName;
    }

    /**
     * @deprecated
     */
    public function setTemplateName($templateName)
    {
        $this->templateName = $templateName;

        return $this;
    }
}
