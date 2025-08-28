<?php

namespace ModulesGarden\TTSGGSModule\Core\Components\Decorator;

use ModulesGarden\TTSGGSModule\Core\Components\Enums\ColumnSize;

class Columns extends AbstractDecorator
{
    protected const NAMES = [
        1 => 'one',
        2 => 'two',
        3 => 'three',
        4 => 'four',
        6 => 'six'
    ];

    public function default()
    {
        return $this->appendClass(ColumnSize::DEFAULT);
    }

    public function one()
    {
        return $this->appendClass(ColumnSize::TWELVE);
    }

    public function two()
    {
        return $this->appendClass(ColumnSize::SIX);

    }

    public function three()
    {
        return $this->appendClass(ColumnSize::FOUR);
    }

    public function four()
    {
        return $this->appendClass(ColumnSize::THREE);
    }

    public function six()
    {
        return $this->appendClass(ColumnSize::TWO);
    }

    /**
     * @param int $number
     * @return Columns
     * @throws \Exception
     */
    public function byColumnsNumber(int $number)
    {
        return $this->appendClass(ColumnSize::byNumber($number));
    }
}