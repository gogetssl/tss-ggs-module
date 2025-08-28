<?php

namespace ModulesGarden\TTSGGSModule\Core\Hook\Integration\Enums;

enum IntegrationInsertTypes: string
{
    case Content    = "content";
    case Full       = "full";
    case McContent  = "mc_content";
}