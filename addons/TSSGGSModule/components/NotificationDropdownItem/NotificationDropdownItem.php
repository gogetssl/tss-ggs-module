<?php

namespace ModulesGarden\TSSGGSModule\Components\NotificationDropdownItem;

use ModulesGarden\TSSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\ActionOnClickTrait;
use ModulesGarden\TSSGGSModule\Core\Components\Traits\TitleTrait;

class NotificationDropdownItem extends AbstractComponent
{
    use TitleTrait;
    use ActionOnClickTrait;

    public const COMPONENT = 'NotificationDropdownItem';

    public function setDate(string $date)
    {
        $this->setSlot('date', $date);

        return $this;
    }

    public function setDescription(string $description)
    {
        $this->setSlot('description', $description);

        return $this;
    }

    public function setReadFlag(bool $read = true)
    {
        $this->setSlot('read', $read);

        return $this;
    }

    public function setItemId($itemId)
    {
        $this->setSlot('itemId', $itemId);

        return $this;
    }

}