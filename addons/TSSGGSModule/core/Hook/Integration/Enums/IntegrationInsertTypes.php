<?php

namespace ModulesGarden\TSSGGSModule\Core\Hook\Integration\Enums;

enum IntegrationInsertTypes: string
{
    case Content    = "content";
    case Full       = "full";
    case McContent  = "mc_content";
}