<?php

namespace ModulesGarden\TTSGGSModule\Components\Board;

use ModulesGarden\TTSGGSModule\Components\BoardColumn\BoardColumn;
use ModulesGarden\TTSGGSModule\Components\Container\Container;
use ModulesGarden\TTSGGSModule\Core\Components\DataBuilder;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\AjaxDataProviderTrait;
use ModulesGarden\TTSGGSModule\Core\DataProviders\CrudProvider;
use ModulesGarden\TTSGGSModule\Core\Components\Response\Response;

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
