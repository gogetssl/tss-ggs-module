<?php

namespace ModulesGarden\TTSGGSModule\Components\DatePicker\Traits;

trait DisableTimeTrait
{
    public function disableBeforeTime(\DateTime $date):self
    {
        $this->setSlot('disableBeforeTime', $date->format('Y-m-d H:i:s'));

        return $this;
    }

    public function disableAfterTime(\DateTime $date):self
    {
        $this->setSlot('disableAfterTime', $date->format('Y-m-d H:i:s'));

        return $this;
    }


}