<?php

namespace ModulesGarden\TTSGGSModule\Components\Grid;

use ModulesGarden\TTSGGSModule\Components\Container\Container;
use ModulesGarden\TTSGGSModule\Components\Row\Row;
use ModulesGarden\TTSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TTSGGSModule\Core\Components\Decorator\Decorator;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\AjaxTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\ComponentsContainerTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\CssContainerTrait;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\ComponentContainerInterface;

/**
 * Class Form
 */
class Grid extends AbstractComponent implements ComponentContainerInterface
{
    use AjaxTrait;
    use ComponentsContainerTrait
    {
        addElement as protected baseAddElement;
    }
    use CssContainerTrait;

    public const COMPONENT = 'Grid';

    public function setRows(array $rows)
    {
        foreach ($rows as $items)
        {
            $row = new Row();
            foreach ($items as $item)
            {
                $realItem  = is_array($item) ? $item[0] : $item;
                $container = new Container();
                $container->addElement($realItem);

                !empty($item[1]) ? (new Decorator($container))->columns()->byColumnsNumber($item[1]) : (new Decorator($container))->columns()->default();

                $row->addElement($container);
            }

            $this->addElement($row);
        }
    }
}
