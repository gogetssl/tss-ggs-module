<?php

namespace ModulesGarden\TSSGGSModule\Components\Form;

use Exception;
use ModulesGarden\TSSGGSModule\Components\HiddenField\HiddenField;
use ModulesGarden\TSSGGSModule\Components\Form\Builder\Builder;
use ModulesGarden\TSSGGSModule\Components\Form\Data\Propagator;
use ModulesGarden\TSSGGSModule\Components\Form\Data\Validate;
use ModulesGarden\TSSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TSSGGSModule\Core\Components\Response\Response;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\ActionsTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\AjaxDataProviderTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\ComponentsContainerTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\CssContainerTrait;
use ModulesGarden\TSSGGSModule\Core\Contracts\ResponseInterface;
use ModulesGarden\TSSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Request;

abstract class AbstractForm extends AbstractComponent
{
    use ActionsTrait;
    use ComponentsContainerTrait;
    use AjaxDataProviderTrait;
    use CssContainerTrait;

    public const COMPONENT          = 'Form';
    public const ACTION_RELOAD_FORM = 'reload';

    protected ?string $providerDefaultAction = CrudProvider::ACTION_READ;
    protected array $providerActionsToValidate = [
        CrudProvider::ACTION_CREATE,
        CrudProvider::ACTION_UPDATE,
    ];

    /**
     * @var Builder
     */
    protected Builder $builder;

    public function __construct()
    {
        parent::__construct();

        //we need this to find form in popup
        $this->setSlot('ref', 'form');
    }

    /**
     * @param $section
     * @return $this
     */
    public function addSection($section): self
    {
        $this->addComponent('components', $section);

        return $this;
    }

    public function onSubmit(\ModulesGarden\TSSGGSModule\Core\Contracts\Components\ActionInterface $action): self
    {
        $this->addAction('onSubmit', $action);

        return $this;
    }


    /**
     * Use this if you don't want to create "form" tag. This is usefully when you have to create form field in form that already exists.
     * @param string $tag
     * @return $this
     */
    public function setContainerTag(string $tag = 'form'): self
    {
        $this->setSlot('container', $tag);

        return $this;
    }

    protected function reload()
    {
        $this->buildHtml();
    }

    public function setAction(string $action): self
    {
        $this->setSlot('action', $action);

        return $this;
    }

    public function setMethod(string $action): self
    {
        $this->setSlot('method', $action);

        return $this;
    }

    public function setTarget(string $target): self
    {
        $this->setSlot('target', $target);

        return $this;
    }


    private function validate(string $action)
    {
        if (in_array($action, $this->providerActionsToValidate))
        {
            (new Validate())->run($this->getSlot('elements', []));
        }
    }

    /**
     * @param $providerAction
     * @return mixed
     * @throws Exception
     */
    private function processDefaultAction(string $providerAction)
    {
        $this->buildHtml();;
        $this->validate($providerAction);

        return $this->runProviderAction($providerAction);
    }

//    private function processCreateAction(string $providerAction)
//    {
//        $this->preLoadHtml();
//        $this->loadHtml();
//        $this->postLoadHtml();
//        $this->validate($providerAction);
//
//        return $this->runProviderAction($providerAction);
//    }

    public function preLoadHtml(): void
    {
        if ($this->providerDefaultAction && in_array(Request::get('providerAction', false), [
                $this->providerDefaultAction,
                self::ACTION_RELOAD_FORM,
                false
            ]))
        {
            $this->runProviderAction($this->providerDefaultAction);
        }
    }

    public function postLoadHtml(): void
    {
        (new Propagator($this->provider()))->propagate($this->getSlots());
    }

    /**
     * @param $providerAction
     * @return ResponseInterface
     * @throws Exception
     */
    private function processReloadAction(string $providerAction): ResponseInterface
    {
        $this->$providerAction();
        $this->validate($providerAction);

        return (new Response([
            'slots' => $this->toArray()['slots'],
        ]));
    }
}
