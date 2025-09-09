<?php

namespace ModulesGarden\TSSGGSModule\Core\UI;

use ModulesGarden\TSSGGSModule\Components\AppNavBar\Breadcrumb;
use ModulesGarden\TSSGGSModule\Components\OverlayComponents\OverlayComponents;
use ModulesGarden\TSSGGSModule\Core\Helper\BuildUrl;
use ModulesGarden\TSSGGSModule\Core\Helper\RandomStringGenerator;
use ModulesGarden\TSSGGSModule\Core\ModuleConstants;
use ModulesGarden\TSSGGSModule\Core\UI\View\AlertsBuilder;
use ModulesGarden\TSSGGSModule\Core\UI\View\BreadcrumbsBuilder;
use ModulesGarden\TSSGGSModule\Core\UI\View\NavBarBuilder;
use function ModulesGarden\TSSGGSModule\Core\Helper\isAdmin;

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
