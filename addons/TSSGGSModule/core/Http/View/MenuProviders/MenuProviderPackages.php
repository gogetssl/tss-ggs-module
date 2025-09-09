<?php

namespace ModulesGarden\TSSGGSModule\Core\Http\View\MenuProviders;

use ModulesGarden\TSSGGSModule\Core\DependencyInjection\PackageServices;
use ModulesGarden\TSSGGSModule\Core\ModuleConstants;
use function ModulesGarden\TSSGGSModule\Core\make;

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
