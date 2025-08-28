<?php

namespace ModulesGarden\TTSGGSModule\Components\TableSimple\Column;

class Column
{
    protected $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function __toString()
    {
        return $this->toString();
    }

    public function toString()
    {
        return $this->name;
    }
}
