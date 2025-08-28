<?php

namespace ModulesGarden\TTSGGSModule\Core\UI\View;

use ModulesGarden\TTSGGSModule\Components\AppBreadcrumb\AppBreadcrumb;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\Breadcrumbs;

class BreadcrumbsBuilder
{
    public function create(): AppBreadcrumb
    {
        $breadcrumb = new AppBreadcrumb();
        $breadcrumb->setItems(Breadcrumbs::get());

        return $breadcrumb;
    }
}