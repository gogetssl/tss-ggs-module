<?php

namespace ModulesGarden\TTSGGSModule\Core\Http\View;

use ModulesGarden\TTSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;
use ModulesGarden\TTSGGSModule\Core\Helper\BuildUrl;
use ModulesGarden\TTSGGSModule\Core\Http\View\MenuProviders\MenuProviderConfig;
use ModulesGarden\TTSGGSModule\Core\Http\View\MenuProviders\MenuProviderInterface;
use ModulesGarden\TTSGGSModule\Core\Http\View\MenuProviders\MenuProviderPackages;
use ModulesGarden\TTSGGSModule\Core\Support\Arr;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Menu;
use ModulesGarden\TTSGGSModule\Core\UI\Menu\Item;

/**
 * Description of MainMenu
 */
class MainMenu
{
    /**
     * @var array
     */
    protected $providers = [];

    public function __construct()
    {
        $this->addMenuProvider(new MenuProviderConfig);
        $this->addMenuProvider(new MenuProviderPackages);

        $this->buildMenu();
    }

    private function addMenuProvider(MenuProviderInterface $provider)
    {
        $menuItems = $provider->getMenuItems();

        $this->providers = array_merge($this->providers, Arr::mergeRecursiveDistinct($menuItems, $this->providers));
    }

    private function buildMenu()
    {
        if(isset($this->providers['samples'])) unset($this->providers['samples']);
        $moduleConfiguration = (new AddonModuleRepository())->getModuleConfiguration();
        if(empty($moduleConfiguration) || (!isset($moduleConfiguration['test_connection']) || $moduleConfiguration['test_connection'] == 'error'))
        {
            $this->providers = [];
            $this->providers['configuration'] = [
                'icon' => 'mdi mdi-cog'
            ];
        }

        foreach ($this->providers as $catName => $category)
        {
            if (Arr::get($category, 'hide', false))
            {
                continue;
            }

            $item = new Item($catName);

            if (isset($category['submenu']))
            {
                foreach ($category['submenu'] as $subName => &$subPage)
                {
                    if (!Arr::get($subPage, 'hide', false) && empty($subPage['url']))
                    {
                        $subtitem = new Item($subName, !empty($subPage['externalUrl']) ? $subPage['externalUrl']
                            : BuildUrl::getUrl($catName, $subName));
                        $subtitem->setTarget($subPage['target'] ?: '');
                        $subtitem->setIcon($subPage['icon'] ?: '');
                        $item->addItem($subtitem);
                    }
                }
            }

            $item->setUrl(!empty($category['externalUrl']) ? $category['externalUrl'] : BuildUrl::getUrl($catName));
            $item->setTarget($category['target'] ?? '');
            $item->setIcon($category['icon'] ?? '');
            Menu::addItem($item);
        }
    }
}
