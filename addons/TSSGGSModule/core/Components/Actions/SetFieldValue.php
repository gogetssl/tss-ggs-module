<?php

namespace ModulesGarden\TSSGGSModule\Core\Components\Actions;

use ModulesGarden\TSSGGSModule\Core\Components\AbstractActionInterface;

class SetFieldValue extends AbstractActionInterface
{
    protected string $name;

    protected string $value;

    public function __construct(string $name, string $value)
    {
        $this->name  = $name;
        $this->value = $value;
    }

    public function toArray(): array
    {
        return [
            'action' => 'setFieldValue',
            'name'   => $this->name,
            'value'  => $this->value
        ];
    }
}
