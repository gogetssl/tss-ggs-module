<?php

namespace ModulesGarden\TSSGGSModule\Components\DatePicker;

use ModulesGarden\TSSGGSModule\Components\DatePicker\Enums\Format;
use ModulesGarden\TSSGGSModule\Components\DatePicker\Enums\Type;
use ModulesGarden\TSSGGSModule\Components\DatePicker\Traits\DisableDateTrait;

class DatePicker extends AbstractPicker
{
    use DisableDateTrait;

    public function __construct()
    {
        parent::__construct();

        $this->setFormat(Format::YYYY_MM_DD)
            ->setType(Type::Date);
    }

    public function disableBeforeToday():self
    {
        return $this->disableBeforeDate(new \DateTime('NOW'));
    }

    public function disableAfterToday():self
    {
        return $this->disableAfterDate(new \DateTime('NOW'));
    }
}