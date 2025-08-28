<?php

namespace ModulesGarden\TTSGGSModule\Components\Container;

use ModulesGarden\TTSGGSModule\Components\Container\Container;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\ComponentContainerInterface;

/**
 * Class Form
 */
class ContainerNoWrap extends Container implements ComponentContainerInterface
{
    protected $css = 'lu-text-nowrap';
}
