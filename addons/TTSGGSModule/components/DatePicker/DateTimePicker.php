<?php

namespace ModulesGarden\TTSGGSModule\Components\DatePicker;

use ModulesGarden\TTSGGSModule\Components\DatePicker\Enums\Format;
use ModulesGarden\TTSGGSModule\Components\DatePicker\Enums\Type;
use ModulesGarden\TTSGGSModule\Components\DatePicker\Traits\DisableDateTrait;
use ModulesGarden\TTSGGSModule\Components\DatePicker\Traits\DisableTimeTrait;

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
