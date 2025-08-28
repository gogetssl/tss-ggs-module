<?php

namespace ModulesGarden\TTSGGSModule\Components\DatePicker\Enums;

enum Format: string
{
    case YYYY_MM_DD             = 'YYYY-MM-DD';
    case YYYY_MM_DD_HH_mm_ss    = 'YYYY-MM-DD HH:mm:ss';
    case HH_mm_ss               = 'HH:mm:ss';
}