<?php

namespace ModulesGarden\TTSGGSModule\Components\FileManager\Source;

use ModulesGarden\TTSGGSModule\Core\Components\AbstractComponent;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\ActionInterface;

class File extends Item
{
    protected static bool $isDir = false;
    protected static string $icon = "file-document";

    public function getClickAction(AbstractComponent $component): ?ActionInterface
    {
        return null;
    }
}