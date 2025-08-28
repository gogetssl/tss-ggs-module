<?php

namespace ModulesGarden\TTSGGSModule\Components\Form\Data;

use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AvailableOptionsInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\ComponentInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\ValueInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\CrudProviderInterface;

class Propagator
{
    protected $provider;

    public function __construct(CrudProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    public function propagate($elements)
    {
        foreach ($elements as $element)
        {
            if ($element instanceof ComponentInterface)
            {
                $this->setValue($element);
                $this->setAvailableValues($element);
                $this->propagateChildComponent($element);
            }
            elseif (is_array($element))
            {
                $this->propagate($element);
            }
        }
    }

    protected function setValue($element): void
    {
        if (!$element instanceof ValueInterface)
        {
            return;
        }

        $value = $this->provider->getValueById($element->getName());
        
        if ($value !== null)
        {
            $element->setValue($value);
        }
    }

    protected function setAvailableValues($element): void
    {
        if ($element instanceof AvailableOptionsInterface && $values = $this->provider->getAvailableValuesById($element->getName()))
        {
            $element->setOptions($values);
        }
    }

    protected function propagateChildComponent($element): void
    {
        $this->propagate($element->getSlots());
    }
}
