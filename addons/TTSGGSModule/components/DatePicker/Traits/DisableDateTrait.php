<?php

namespace ModulesGarden\TTSGGSModule\Components\DatePicker\Traits;

trait DisableDateTrait
{
    public function disableBeforeDate(\DateTime $date):self
    {
        $this->setSlot('disableBeforeDate', $date->format('Y-m-d'));

        return $this;
    }

    public function disableAfterDate(\DateTime $date):self
    {
        $this->setSlot('disableAfterDate', $date->format('Y-m-d'));

        return $this;
    }
}