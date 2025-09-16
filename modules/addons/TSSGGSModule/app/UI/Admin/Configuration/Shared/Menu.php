<?php
namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Configuration\Shared;

use ModulesGarden\TSSGGSModule\Components\Icon\Icon;
use ModulesGarden\TSSGGSModule\Components\Sidebar\Sidebar;
use ModulesGarden\TSSGGSModule\Components\SidebarItem\SidebarItem;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Request;
use ModulesGarden\TSSGGSModule\Core\Translation\TranslatorTrait;

class Menu
{
    use TranslatorTrait;

    public function getMenu()
    {
        $action = Request::get('mg-action');

        $menuViewContainer = new Sidebar();
        $menuViewContainer->setClass('menu-configuration-unified');
        $menuViewContainer->setTitle($this->translate('installation_steps',[],['instalation.menu']));

        $additionalClass = 'textCrossed';

        foreach ([
                    'step1' => $this->translate('step1',[],['instalation.menu']),
                    'step2' => $this->translate('step2',[],['instalation.menu']),
                    'step3' => $this->translate('step3',[],['instalation.menu']),
                    'step4' => $this->translate('step4',[],['instalation.menu']),
                    'step5' => $this->translate('step5',[],['instalation.menu']),
                    'step6' => $this->translate('step6',[],['instalation.menu'])
                 ] as $page => $name) {

            $item = new SidebarItem();
            $item->setTitle($name);
            $item->setUrl('#');

            if($action == $page) {
                $additionalClass = '';
                $item->setActive(true);
            }

            $item->setClass($additionalClass);
            $menuViewContainer->addItem($item);
        }

        return $menuViewContainer;
    }
}