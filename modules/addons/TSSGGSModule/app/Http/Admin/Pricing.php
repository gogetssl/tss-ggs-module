<?php

namespace ModulesGarden\TSSGGSModule\App\Http\Admin;


use ModulesGarden\TSSGGSModule\App\UI\Admin\Pricing\Index\PricingContainer;
use ModulesGarden\TSSGGSModule\Core\Contracts\Controllers\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Http\AbstractController;
use ModulesGarden\TSSGGSModule\Core\Helper;

class Pricing extends AbstractController implements AdminAreaInterface
{

    public function index()
    {
        return Helper\view()
            ->addElement(PricingContainer::class);
    }
}
