<?php

namespace ModulesGarden\TTSGGSModule\App\Http\Admin;

use ModulesGarden\TTSGGSModule\App\UI\Admin\Products\Index\ProductsContainer;
use ModulesGarden\TTSGGSModule\Core\Contracts\Controllers\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Http\AbstractController;
use ModulesGarden\TTSGGSModule\Core\Helper;

class Products extends AbstractController implements AdminAreaInterface
{

    public function index()
    {
        return Helper\view()
            ->addElement(ProductsContainer::class);
    }
}
