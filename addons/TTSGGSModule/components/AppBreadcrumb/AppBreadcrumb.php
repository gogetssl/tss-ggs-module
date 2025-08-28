<?php

namespace ModulesGarden\TTSGGSModule\Components\AppBreadcrumb;

use ModulesGarden\TTSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TTSGGSModule\Core\UI\Breadcrumbs\Item;

/**
 * Class Form
 */
class AppBreadcrumb extends AbstractComponent
{
    public const COMPONENT = 'AppBreadcrumb';
    protected $items = [];

    public function __construct()
    {
        parent::__construct();
        $this->setSlot('items');
    }

    /**
     * @param string $name
     * @param string $url
     * @param bool $isActive
     * @return $this
     */
    public function addItem(Item $item): self
    {
        $this->items[] = $item;

        return $this;
    }

    public function setItems(array $items): self
    {
        $this->items = $items;

        return $this;
    }
}
