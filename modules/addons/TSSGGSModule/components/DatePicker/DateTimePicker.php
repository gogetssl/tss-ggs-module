<?php

namespace ModulesGarden\TSSGGSModule\Components\DatePicker;

use ModulesGarden\TSSGGSModule\Components\DatePicker\Enums\Format;
use ModulesGarden\TSSGGSModule\Components\DatePicker\Enums\Type;
use ModulesGarden\TSSGGSModule\Components\DatePicker\Traits\DisableDateTrait;
use ModulesGarden\TSSGGSModule\Components\DatePicker\Traits\DisableTimeTrait;

class DateTimePicker extends AbstractPicker
{
    use DisableDateTrait;
    use DisableTimeTrait;

    public function __construct()
    {
        parent::__construct();

        $this->setFormat(Format::YYYY_MM_DD_HH_mm_ss)
            ->setType(Type::DateTime);
    }

    public function disableBeforeNow():self
    {
        return $this->disableBeforeDate(new \DateTime('NOW'))
            ->disableBeforeTime(new \DateTime('NOW'));
    }

    public function disableAfterNow():self
    {
        return $this->disableAfterDate(new \DateTime('NOW'))
            ->disableAfterTime(new \DateTime('NOW'));
    }
}
