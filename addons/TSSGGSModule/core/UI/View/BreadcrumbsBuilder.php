<?php

namespace ModulesGarden\TSSGGSModule\Core\UI\View;

use ModulesGarden\TSSGGSModule\Components\AppBreadcrumb\AppBreadcrumb;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Breadcrumbs;

class BreadcrumbsBuilder
{
    public function create(): AppBreadcrumb
    {
        $breadcrumb = new AppBreadcrumb();
        $breadcrumb->setItems(Breadcrumbs::get());

        return $breadcrumb;
    }
}