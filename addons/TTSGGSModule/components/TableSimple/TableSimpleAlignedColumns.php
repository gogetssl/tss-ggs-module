<?php

namespace ModulesGarden\TTSGGSModule\Components\TableSimple;

class TableSimpleAlignedColumns extends TableSimple
{
    public function __construct()
    {
        parent::__construct();
        $this->addClass("lu-table-layout-fixed");
    }
}