<?php

namespace ModulesGarden\TTSGGSModule\App\Events;

use ModulesGarden\TTSGGSModule\Core\Events\Event;

/**
 * Class MyTestEvent
 */
class MyTestEvent extends Event
{
    protected $data = null;

    /**
     * MyTestEvent constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @return null
     */
    public function getData()
    {
        return $this->data;
    }
}
