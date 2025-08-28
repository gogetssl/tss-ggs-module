<?php

namespace ModulesGarden\TTSGGSModule\Core\Hook;

use ModulesGarden\TTSGGSModule\Core\App\Controllers\Instances\Http\Integration;
use ModulesGarden\TTSGGSModule\Core\DependencyInjection;
use ModulesGarden\TTSGGSModule\Core\Traits\Lang;
use ModulesGarden\TTSGGSModule\Core\UI\View;
use ModulesGarden\TTSGGSModule\Core\UI\ViewAjax;

/**
 *  class HookIntegratorView
 *  Prepares a views basing on /App/Integrations/Admin/ & /App/Integrations/Client controlers
 *  to be injected on WHMCS subpages
 */
class HookIntegratorView
{
    use Lang;

    /**
     * @var null|string
     * HTML integration code
     */
    protected $HTMLData = null;

    /**
     * @var null|string|View
     * integration data
     */
    protected $view = null;

    public function __construct($view)
    {
        $this->view = $view;
    }

    /**
     * returns string/HTML integration code
     */
    public function getHTML()
    {
        $this->viewToHtml();

        return $this->HTMLData;
    }

    /**
     * transforms integration data to string to be integrated in WHMCS template
     */
    protected function viewToHtml()
    {
        if ($this->view instanceof View || $this->view instanceof ViewAjax  )
        {
            $resp                      = $this->view->getResponse();
            $integrationPageController = DependencyInjection::call(Integration::class);
            $integrationPageController->setControllerResult($resp);

            $this->HTMLData = $integrationPageController->execute();

            return true;
        }

        if (is_string($this->view))
        {
            $this->HTMLData = $this->view;

            return true;
        }

        $this->HTMLData = '';
    }
}
