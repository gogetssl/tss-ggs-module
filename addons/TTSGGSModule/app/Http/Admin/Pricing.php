<?php

namespace ModulesGarden\TTSGGSModule\App\Http\Admin;


use ModulesGarden\TTSGGSModule\App\UI\Admin\Pricing\Index\PricingContainer;
use ModulesGarden\TTSGGSModule\Core\Contracts\Controllers\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Http\AbstractController;
use ModulesGarden\TTSGGSModule\Core\Helper;

class Pricing extends AbstractController implements AdminAreaInterface
{

    public function index()
    {
        return Helper\view()
            ->addElement(PricingContainer::class);
    }
}
