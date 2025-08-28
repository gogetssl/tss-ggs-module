<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\Http\Admin;

use ModulesGarden\TTSGGSModule\Core\Contracts\Controllers\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Helper;
use ModulesGarden\TTSGGSModule\Core\Http\AbstractController;
use ModulesGarden\TTSGGSModule\Core\UI\View;
use ModulesGarden\TTSGGSModule\Packages\ModuleSettings\Support\Facades\ModuleSettings;
use ModulesGarden\TTSGGSModule\Packages\Notifier\UI\NotificationsForRecipients\NotificationsForCurrentAdmin;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\AjaxSearch\Forms\AjaxSearchForm;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DataTable\Pages\DataTable;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\HintsBox\Components\HintsBox;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\HintsBox\Forms\SettingsForm;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\Home\FileManager\Pages\FileManager;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\Home\Tabs\Pages\TabsWidget;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\Notifications\Widgets\NotificationWidget;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\OnOffSwitchers\Forms\OnOffSwitchersForm;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ProgressBars\Widgets\ProgressBarsWidget;
use ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ReloadableDropdowns\Forms\ReloadableDropdownsForm;

class Samples extends AbstractController implements AdminAreaInterface
{
    public function basicElements()
    {
        return Helper\view()
            ->addElement(\ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\BasicElements\Container::class);
    }

    public function complexElements()
    {
        return Helper\view()
            ->addElement(\ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ComplexElements\Container::class);
    }

    public function forms()
    {
        return Helper\view()
            ->addElement(\ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\Forms\Container::class);
    }


    public function dataTable()
    {
        return Helper\view()
            ->addElement(\ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DataTable\Container::class);
    }


    public function graphs()
    {
        return Helper\view()
            ->addElement(\ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\Graphs\Container::class);
    }

    public function kanbanBoard()
    {
        return Helper\view()
            ->addElement(\ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\KanbanBoard\Container::class);
    }

    public function treeView()
    {
        return Helper\view()
            ->addElement(\ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\TreeView\Container::class);
    }


    /**
     * Example of static page
     * @return View
     */
    public function index()
    {
        return Helper\view();
        /*return Helper\view()
            ->addElement(\ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\Samples\Pages\HomePage::class);*/
    }


    public function samplesIntegration()
    {
        return Helper\viewIntegrationAddon()
            ->addElement(\ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\TreeView\Container::class);
    }

    public function adaptiveTwoColumnsContainer()
    {
        return Helper\view()->addElement(\ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\AdaptiveTwoColumnsContainer\Container::class);
    }

    public function notifications()
    {
        return Helper\view()
            ->addElement(NotificationWidget::class)
            ->addElement(NotificationsForCurrentAdmin::class);
    }

    public function hintsBox()
    {
        $view = Helper\view();

        if (!ModuleSettings::get('HintsBox.hide_guide')) {
            $view->addElement(HintsBox::class);
        }

        return $view->addElement(SettingsForm::class);
    }

    public function ajaxSearch()
    {
        $view = Helper\view();
        return $view->addElement(AjaxSearchForm::class);
    }

    public function addReloadableDropdowns()
    {
        $view = Helper\view();
        return $view->addElement(ReloadableDropdownsForm::class);
    }

    public function onOffSwitchers()
    {
        $view = Helper\view();
        return $view->addElement(OnOffSwitchersForm::class);
    }

    public function progressBars()
    {
        $view = Helper\view();
        return $view->addElement(\ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ProgressBars\Container::class);
    }

    public function collapsableBox()
    {
        $view = Helper\view();
        return $view->addElement(\ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\CollapsableBox\Container::class);
    }

    public function componentActions()
    {
        $view = Helper\view();
        return $view->addElement(\ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ComponentActions\Container::class);
    }

    public function copyToTargetComponent()
    {
        $view = Helper\view();
        return $view->addElement(\ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\CopyToTarget\Container::class);
    }

    public function elementsListComponent()
    {
        $view = Helper\view();
        return $view->addElement(\ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\ElementsListComponent\Container::class);
    }

    public function ticketRepliesComponent()
    {
        $view = Helper\view();
        return $view->addElement(\ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\TicketReplies\Container::class);
    }

    public function dynamicTabs()
    {
        $view = Helper\view();
        return $view->addElement(\ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\DynamicTabs\Container::class);
    }
}
