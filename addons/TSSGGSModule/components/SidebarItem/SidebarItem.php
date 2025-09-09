<?php

namespace ModulesGarden\TSSGGSModule\Components\SidebarItem;

use ModulesGarden\TSSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\ComponentsContainerTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\CssContainerTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\UrlTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\TitleTrait;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\ComponentContainerInterface;

class SidebarItem extends AbstractComponent implements ComponentContainerInterface
{
    use CssContainerTrait;
    use ComponentsContainerTrait;
    use TitleTrait;
    use UrlTrait;

    public const COMPONENT = 'SidebarItem';

    public function __construct(string $title = "", string $url = "")
    {
        parent::__construct();
        $this->setTitle($title);
        $this->setUrl($url);
    }

    public function setActive(bool $active): self
    {
        $this->setSlot('active', $active);

        return $this;
    }

    public function getUrl():string
    {
        return $this->getSlot('url');
    }

    public function setClass(string $class)
    {
        $this->setSlot('class', $class);
    }

}