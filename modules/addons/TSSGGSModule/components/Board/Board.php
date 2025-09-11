<?php

namespace ModulesGarden\TSSGGSModule\Components\Board;

use ModulesGarden\TSSGGSModule\Components\BoardColumn\BoardColumn;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Core\Components\DataBuilder;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\AjaxDataProviderTrait;
use ModulesGarden\TSSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TSSGGSModule\Core\Components\Response\Response;

class Board extends Container
{
    public const COMPONENT = 'Board';

    use AjaxDataProviderTrait;

    public function __construct()
    {
        parent::__construct();

        $this->providerAction = CrudProvider::ACTION_READ;
    }

    protected function processReadAction(string $providerAction)
    {
        return new Response(
            (new DataBuilder($this))
                ->withHtml()
                ->withData()
                ->toArray()
        );
    }

    public function addColumn(BoardColumn $column): self
    {
        $this->addComponent('columns', $column);

        return $this;
    }
}
