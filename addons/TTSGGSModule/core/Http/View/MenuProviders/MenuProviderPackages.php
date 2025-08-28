<?php

namespace ModulesGarden\TTSGGSModule\Core\Http\View\MenuProviders;

use ModulesGarden\TTSGGSModule\Core\DependencyInjection\PackageServices;
use ModulesGarden\TTSGGSModule\Core\ModuleConstants;
use function ModulesGarden\TTSGGSModule\Core\make;

class MenuProviderPackages implements MenuProviderInterface
{
    //@todo - this method should be called with user level, this function cannot check if we are in the AA or CA
    public function getMenuItems(): array
    {
        $menu = make(PackageServices::class)->getMenu();
        $type = ModuleConstants::getLevel();

        return $menu[$type] ?? [];
    }
}
