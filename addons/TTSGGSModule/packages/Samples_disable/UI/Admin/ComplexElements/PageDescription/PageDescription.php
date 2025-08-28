<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ComplexElements\PageDescription;

use ModulesGarden\TTSGGSModule\Components\Container\Container;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\ModuleConstants;

class PageDescription extends Container implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        $pageDescription = new \ModulesGarden\TTSGGSModule\Components\PageDescription\PageDescription();
        $pageDescription->setImagePath(ModuleConstants::getFullPath('resources', 'assets', 'img', 'actions', 'backup-jobs.png'));
        $pageDescription->setTitle('Mailing List');
        $pageDescription->setContent('Mailing lists allow you to use a single address to send message to multiple email addresses.
This feature is very useful when you need to send a newsletter or another update to a group of people.
Allowed actions on a mailing list:');

        $this->addElement($pageDescription);
    }
}