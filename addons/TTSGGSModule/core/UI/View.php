<?php

namespace ModulesGarden\TTSGGSModule\Core\UI;

use ModulesGarden\TTSGGSModule\Components\AppNavBar\Breadcrumb;
use ModulesGarden\TTSGGSModule\Components\OverlayComponents\OverlayComponents;
use ModulesGarden\TTSGGSModule\Core\Helper\BuildUrl;
use ModulesGarden\TTSGGSModule\Core\Helper\RandomStringGenerator;
use ModulesGarden\TTSGGSModule\Core\ModuleConstants;
use ModulesGarden\TTSGGSModule\Core\UI\View\AlertsBuilder;
use ModulesGarden\TTSGGSModule\Core\UI\View\BreadcrumbsBuilder;
use ModulesGarden\TTSGGSModule\Core\UI\View\NavBarBuilder;
use function ModulesGarden\TTSGGSModule\Core\Helper\isAdmin;

/**
 *
 */
class View extends AbstractPartialView
{
    public function __construct()
    {
        $this->initDefaultComponents();
    }

    protected function initDefaultComponents()
    {
        $this->addElement(OverlayComponents::class);
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->buildRootElements($this->elements);
    }
}
