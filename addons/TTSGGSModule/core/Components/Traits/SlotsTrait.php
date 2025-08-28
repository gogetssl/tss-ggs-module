<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Traits;

use ModulesGarden\TTSGGSModule\Core\Support\Arr;
use ModulesGarden\TTSGGSModule\Core\Support\Text;

trait SlotsTrait
{
    /**
     * @var string[]
     */
    private array $slots = [];

    /**
     * @param $name
     * @return string|null
     */
    public function getSlot(string $name, $default = null)
    {
        return Arr::get($this->slots, $name, $default);
    }

    public function getSlots(): array
    {
        return $this->slots;
    }

    protected function pushToSlot(string $name, $value, $key = null): self
    {
        $items   = Arr::get($this->slots, $name, []);
        $key ? $items[$key] = $value :  $items[] = $value;

        $this->setSlot($name, $items);

        return $this;
    }

    /**
     * @param string $name
     * @param $value
     * @return self
     */
    protected function setSlot(string $name, $value = null): self
    {
        Arr::set($this->slots, $name, $value);

        return $this;
    }

    /**
     * Collect information about slots
     * @return array
     */
    private function prepareSlots(): array
    {
        $out = [];

        //automatic slots based on method
        foreach (get_class_methods($this) as $method)
        {
            $pos = strpos($method, 'SlotBuilder');
            if ($pos === false || $pos < 1)
            {
                continue;
            }

            $slot                      = substr($method, 0, $pos);
            $value                     = $this->$method();
            $out[Text::toKebab($slot)] = $value;
        }

        //manually created slots
        foreach ($this->slots as $slotName => $slotValue)
        {
            if (array_key_exists($slotName, $out))
            {
                continue;
            }

            if (property_exists($this, $slotName))
            {
                $out[$slotName] = $this->$slotName;
            }
            else
            {
                $out[$slotName] = $slotValue;
            }
        }

        //filter values
        $out = array_filter($out, function($value) {
            return !is_null($value);
        });

        return $out;
    }
}
