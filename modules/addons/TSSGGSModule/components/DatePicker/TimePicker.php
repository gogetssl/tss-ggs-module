<?php

namespace ModulesGarden\TSSGGSModule\Components\DatePicker;

use ModulesGarden\TSSGGSModule\Components\DatePicker\Enums\Format;
use ModulesGarden\TSSGGSModule\Components\DatePicker\Enums\Type;
use ModulesGarden\TSSGGSModule\Components\DatePicker\Traits\DisableTimeTrait;

class TimePicker extends AbstractPicker
{
    use DisableTimeTrait;

    public function __construct()
    {
        parent::__construct();

        $this->setFormat(Format::HH_mm_ss)
            ->setType(Type::Time);
    }

    public function disableBeforeNow():self
    {
        return $this->disableBeforeTime(new \DateTime('NOW'));
    }

    public function disableAfterNow():self
    {
        return $this->disableAfterTime(new \DateTime('NOW'));
    }
}