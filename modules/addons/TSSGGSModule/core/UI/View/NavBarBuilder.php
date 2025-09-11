<?php

namespace ModulesGarden\TSSGGSModule\Core\UI\View;

use ModulesGarden\TSSGGSModule\Components\AppNavBar\AppNavBar;
use ModulesGarden\TSSGGSModule\Components\NavBarItem\NavBarItem;
use ModulesGarden\TSSGGSModule\Core\DependencyInjection;
use ModulesGarden\TSSGGSModule\Core\Events\Events\NavBarBuilderBeforeCreateMenu;
use ModulesGarden\TSSGGSModule\Core\Helper\BuildUrl;
use ModulesGarden\TSSGGSModule\Core\Http\View\MainMenu;
use ModulesGarden\TSSGGSModule\Core\ModuleConstants;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Config;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Menu;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Router;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Translator;
use function ModulesGarden\TSSGGSModule\Core\fire;

class NavBarBuilder
{
    protected AppNavBar $navbar;

    public function __construct()
    {
        //@todo refactor
        $this->navbar = new AppNavBar();
    }

    public function createAdminArea(): AppNavBar
    {
        $this->createMenu();
        $this->configureNavbar();

        return $this->navbar;
    }

    public function createClientArea(): AppNavBar
    {
        $this->createMenu();

        return $this->navbar;
    }

    public function createProductConfiguration(): AppNavBar
    {
        $this->configureNavbar();

        return $this->navbar;
    }

    function configureNavbar(): void
    {
        $icon = \ModulesGarden\TSSGGSModule\Core\Support\Facades\Config::get('configuration.moduleIcon', 'modulesgarden_base');
        $this->navbar->setModuleLogo(BuildUrl::getAssetsURL('/img/products/' . $icon . '.png'), str_replace('_', '-', $icon));
        $this->navbar->setVendorLogo(BuildUrl::getAssetsURL('/img/modules-garden-logo.svg'));
        $this->navbar->setMainUrl(BuildUrl::getUrl());
        $this->navbar->setModuleName(Config::get('configuration.systemName'));
        $this->navbar->setModuleVersion(Config::get('configuration.version'));
    }

    protected function createMenu(): void
    {
        //@todo - move this to service
        $mainMenu = DependencyInjection::create(MainMenu::class);
        $level    = ModuleConstants::getLevel();

        fire(NavBarBuilderBeforeCreateMenu::class);

        /**
         * @var \ModulesGarden\TSSGGSModule\Core\UI\Menu\Item $menuItem
         */
        foreach (Menu::getItems() as $menuItem)
        {
            $item = new NavBarItem();
            $item->setTitle(Translator::get($level . '.menu.' . $menuItem->getName()));
            $item->setUrl($menuItem->getUrl());
            $item->setTarget($menuItem->getTarget());
            $item->setIcon($menuItem->getIcon());
            if (Router::getCurrentRoute()->is($menuItem->getName()))
            {
                $item->setActive(true);
            }

            if ($menuItem->hasItems())
            {
                /**
                 * @var \ModulesGarden\TSSGGSModule\Core\UI\Menu\Item $subMenuItem
                 */
                foreach ($menuItem->getItems() as $subMenuItem)
                {
                    $subitem = new NavBarItem();
                    $subitem->setTitle(Translator::get($level . '.menu.' . $menuItem->getName() . '_' . $subMenuItem->getName()));
                    $subitem->setUrl($subMenuItem->getUrl());
                    if (Router::getCurrentRoute()->is($menuItem->getName(), $subMenuItem->getName()))
                    {
                        $subitem->setActive(true);
                    }


                    $item->addItem($subitem);
                }
            }

            $this->navbar->addMenuItem($item);
        }

        foreach (Menu::getToolbarElements() as $toolbarElement)
        {
            $this->navbar->addToolbarElement($toolbarElement);
        }
    }
}