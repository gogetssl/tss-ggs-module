<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Traits;

trait OptionsTrait
{
    /**
     * @var callable
     */
    protected $buildSearchCallback = null;

    /**
     * Available options to choose
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options): self
    {
        $this->setSlot('options', $this->convertOptions($options));

        return $this;
    }

    /**
     * Take first value from provided options and set it as default.
     * @return $this
     */
    public function setDefaultValueAsFirstOption(): self
    {
        $this->setSlot('setDefaultValueAsFirstOption', true);

        return $this;
    }

    public function setBuildSearchCallback(callable $callback):self
    {
        $this->buildSearchCallback = $callback;

        return $this;
    }

    protected function convertOptions(array $options): array
    {
        $converted = [];
        $first = current($options);

        if (!empty($first['value']))
        {
            return array_values($options);
        }

        foreach ($options as $value => $name)
        {
            $converted[] = [
                'name'      => $name,
                'search'    => is_callable($this->buildSearchCallback) ? call_user_func($this->buildSearchCallback, $name, $value) : $name,
                'value'     => $value,
            ];
        }

        return $converted;
    }
}