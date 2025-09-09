<?php

namespace ModulesGarden\TSSGGSModule\Core\Components\Actions;

use ModulesGarden\TSSGGSModule\Core\Components\AbstractActionInterface;

/**
 * @deprecated - use Click::byClass() instead
 */
class ClickByClass extends AbstractActionInterface
{
    protected string $className;

    public function __construct(string $className)
    {
        $this->className = $className;
    }

    public function toArray(): array
    {
        return [
            'action'    => 'clickByClass',
            'className' => $this->className
        ];
    }
}
