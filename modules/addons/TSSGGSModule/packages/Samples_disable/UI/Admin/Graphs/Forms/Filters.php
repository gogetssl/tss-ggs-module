<?php

namespace ModulesGarden\TSSGGSModule\Packages\Samples\UI\Admin\Graphs\Forms;

use ModulesGarden\TSSGGSModule\Components\Dropdown\Dropdown;
use ModulesGarden\TSSGGSModule\Components\Form\AbstractForm;
use ModulesGarden\TSSGGSModule\Components\Form\Builder\BuilderCreator;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;

class Filters extends AbstractForm implements AdminAreaInterface, AjaxComponentInterface
{
    public function loadHtml(): void
    {
        $builder = BuilderCreator::simple($this);
        $builder->createField(Dropdown::class, 'coscoscos')->setMultiple(true)->setOptions([
            'xxxx'=>'xxx',
            'yyy'=>'yyy',
            'zzz'=>'zz',
        ]);

    }
}
