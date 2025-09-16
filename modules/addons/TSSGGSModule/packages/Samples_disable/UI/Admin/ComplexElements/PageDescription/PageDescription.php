<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\ComplexElements\PageDescription;

use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\ModuleConstants;

class PageDescription extends Container implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        $pageDescription = new \ModulesGarden\TSSGGSModule\Components\PageDescription\PageDescription();
        $pageDescription->setImagePath(ModuleConstants::getFullPath('resources', 'assets', 'img', 'actions', 'backup-jobs.png'));
        $pageDescription->setTitle('Mailing List');
        $pageDescription->setContent('Mailing lists allow you to use a single address to send message to multiple email addresses.
This feature is very useful when you need to send a newsletter or another update to a group of people.
Allowed actions on a mailing list:');

        $this->addElement($pageDescription);
    }
}