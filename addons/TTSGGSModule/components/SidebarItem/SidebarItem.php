<?php

namespace ModulesGarden\TTSGGSModule\Components\SidebarItem;

use ModulesGarden\TTSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\ComponentsContainerTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\CssContainerTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\UrlTrait;
use ModulesGarden\TTSGGSModule\Core\Components\Traits\TitleTrait;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\ComponentContainerInterface;

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