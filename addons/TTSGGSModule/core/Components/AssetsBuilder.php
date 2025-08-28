<?php

namespace ModulesGarden\TTSGGSModule\Core\Components;

use ModulesGarden\TTSGGSModule\Core\Components\Builder\FileFinder;
use ModulesGarden\TTSGGSModule\Core\ModuleConstants;

class AssetsBuilder
{
    protected $components = [];
    protected $htmlContent = '';
    protected $jsContent = '';

    /**
     * Builder constructor.
     */
    public function __construct()
    {
        $files = scandir(ModuleConstants::getFullPath('components'));

        foreach ($files as $file)
        {
            if (in_array($file, ['.', '..']))
            {
                continue;
            }

            $this->components[] = '\ModulesGarden\TTSGGSModule\Components\\' . $file . '\\' . $file;
        }
    }

    /**
     * @return $this
     */
    public function build()
    {
        $this->jsContent   = '';
        $this->htmlContent = 'let templateContent = [];' . PHP_EOL;

        $failLoadedComponents = [];

        foreach ($this->components as $component)
        {
            try
            {
                $htmlContent    = $this->buildHtml($component);
                $jsContent      = $this->buildJs($component);
            }
            catch (\Throwable $ex)
            {
                $failLoadedComponents[] = [
                    "component" => $component,
                    "message" => $ex->getMessage()
                ];

                break;

                continue;
            }

            $this->htmlContent .= $htmlContent;
            $this->jsContent   .= $jsContent;
        }

        $this->printFailLoadedComponents($failLoadedComponents);

        return $this;
    }

    /**
     * @param string $component
     * @return string
     */
    protected function buildHtml(string $component):string
    {
        $htmlContent = (new FileFinder($component))->getHtml();

        if (empty($htmlContent))
        {
            throw new \Exception('emptyHtmlContent');
        }

        return sprintf('templateContent["%s"] = `%s`', $this->getTemplateName($component), $htmlContent) . PHP_EOL;
    }

    /**
     * @param string $component
     * @return string
     */
    protected function getTemplateName(string $component): string
    {
        return $component::getComponentTemplateName();
    }

    /**
     * @param string $component
     * @return string
     */
    protected function buildJs(string $component):string
    {
        $js = ((new FileFinder($component))->getJs());
        $js = str_replace("'#template-name#'", sprintf('templateContent["%s"]', $this->getTemplateName($component)), $js);
        $js = str_replace('var component =', '', $js);

        if (empty($js))
        {
            throw new \Exception('emptyJsContent');
        }

        return PHP_EOL . sprintf("vueComponents['%s'] = %s;", $this->getTemplateName($component), $js) . PHP_EOL;
    }

    /**
     * @return string
     */
    public function getHtmlContent()
    {
        return $this->htmlContent;
    }

    /**
     * @return string
     */
    public function getJsContent()
    {
        return $this->jsContent;
    }

    protected function printFailLoadedComponents(array $failLoadedComponents = []):void
    {
        if (empty($failLoadedComponents)) //&& Config::get('configuration.debug', false)) - narazie wali błędem
        {
            return;
        }

        $message    = 'Components loading failed. See incorrectly loaded components below.';
        $components = json_encode($failLoadedComponents);

        $this->jsContent .= "$( document ).ready(function() {
            console.groupCollapsed('$message');
            console.error($components);
            console.groupEnd();
        });";
    }
}
