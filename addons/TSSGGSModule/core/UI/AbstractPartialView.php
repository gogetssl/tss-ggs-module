<?php

namespace ModulesGarden\TSSGGSModule\Core\UI;

use Exception;
use ModulesGarden\TSSGGSModule\Core\Components\DataBuilder;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\ClientAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\ComponentInterface;
use ModulesGarden\TSSGGSModule\Core\DependencyInjection;
use ModulesGarden\TSSGGSModule\Core\UI\Helpers\TemplateConstants;
use function ModulesGarden\TSSGGSModule\Core\Helper\isAdmin;

abstract class AbstractPartialView
{
    protected array $elements = [];

    /**
     * @param $element
     * @return $this
     * @throws Exception
     * @todo - refactor me
     */
    public function addElement($element): self
    {
        if (is_string($element))
        {
            $element = DependencyInjection::create($element);
        }

        if (!$element instanceof ComponentInterface)
        {
            throw new Exception('Class ' . get_class($element) . ' must implements ' . ComponentInterface::class);
        }

        $this->elements[] = $element;

        return $this;
    }


    public function getElements()
    {
        return $this->elements;
    }

    protected function buildRootElements(array $rootElements)
    {
        return array_map(function($element) {
            return (new DataBuilder($element))
                ->withHtml()
                ->toArray();
        }, $rootElements);
    }
}