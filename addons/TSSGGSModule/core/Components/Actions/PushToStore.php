<?php

namespace ModulesGarden\TSSGGSModule\Core\Components\Actions;

use ModulesGarden\TSSGGSModule\Core\Components\AbstractActionInterface;

class PushToStore extends AbstractActionInterface
{
    protected string $name;
    protected array $data;

    public function __construct(string $name, array $data)
    {
        $this->name = $name;
        $this->data = $data;
    }

    public function toArray(): array
    {
        return [
            'action' => 'pushToStore',
            'name'   => $this->name,
            'data'   => $this->data
        ];
    }
}
