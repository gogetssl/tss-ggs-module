<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ComplexElements\Tiles;

use ModulesGarden\TTSGGSModule\Components\Container\Container;
use ModulesGarden\TTSGGSModule\Components\TileButton\TileButton;
use ModulesGarden\TTSGGSModule\Components\TilesBar\TilesBar;
use ModulesGarden\TTSGGSModule\Core\Components\Actions\ModalLoad;
use ModulesGarden\TTSGGSModule\Core\ModuleConstants;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DataTable\Modals\UserDelete;

class Tiles extends Container
{
    public function loadHtml(): void
    {
        $tiles = new TilesBar();
        $tiles->setTitle('Actions');
        $tiles->addTile((new TileButton())->setImagePath(ModuleConstants::getFullPath('resources', 'assets', 'img', 'actions', 'backup-jobs.png'))->setTitle('Backups Jobs')->onClick(new ModalLoad(new UserDelete())));
        $tiles->addTile((new TileButton())->setImagePath(ModuleConstants::getFullPath('resources', 'assets', 'img', 'actions', 'backups.png'))->setTitle('Backups'));
        $tiles->addTile((new TileButton())->setImagePath(ModuleConstants::getFullPath('resources', 'assets', 'img', 'actions', 'console.png'))->setTitle('Console'));
        $tiles->addTile((new TileButton())->setImagePath(ModuleConstants::getFullPath('resources', 'assets', 'img', 'actions', 'disks.png'))->setTitle('Disk'));
        $tiles->addTile((new TileButton())->setImagePath(ModuleConstants::getFullPath('resources', 'assets', 'img', 'actions', 'dns.png'))->setTitle('DNS'));
        $tiles->addTile((new TileButton())->setImagePath(ModuleConstants::getFullPath('resources', 'assets', 'img', 'actions', 'firewall.png'))->setTitle('Firewall'));

        $this->addElement($tiles);
    }
}