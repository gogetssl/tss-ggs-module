<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Products\Index;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Products\Index\DataTables\Products\ArrayDataProvider\DataTable;
use ModulesGarden\TSSGGSModule\Components\Alert\Alert;
use ModulesGarden\TSSGGSModule\App\Http\Admin\Products as ProductsController;
use ModulesGarden\TSSGGSModule\Components\Alert\AlertDanger;
use ModulesGarden\TSSGGSModule\Components\Alert\AlertInfo;
use ModulesGarden\TSSGGSModule\Components\Alert\AlertSuccess;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;

class ProductsContainer extends Container implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        $session = $_SESSION[ProductsController::SESSION] ?? [];
        unset($_SESSION[ProductsController::SESSION]);
        if(!empty($session['error']))
        {
            $this->addElement((new AlertDanger())->setText($session['error']));
        }
        if(!empty($session['notice']))
        {
            $notice = $session['notice'];
            $this->addElement((new AlertSuccess())->setText($this->translate('notice_' . $notice['key'], ['product' => $notice['product'], 'detail' => $notice['detail'] !== '' ? $notice['detail'] : '-'])));
        }

        $text = new Text();
        $text->setCss('tssggs-html');
        $text->setText($this->explanation());
        $this->addElement($text);

        $this->addElement(new DataTable());
    }

    /** What the page is for and what the actions in the Manage column do */
    protected function explanation(): string
    {
        $t    = function($key) { return htmlspecialchars((string)$this->translate($key, [], ['admin.products']), ENT_QUOTES); };
        $item = function($key) use ($t) { return '<li><b>' . $t($key . '_name') . '</b> ' . $t($key) . '</li>'; };

        return '<div class="tssggs-explain"><span class="tssggs-explain__icon">' . Icons::svg('information-outline') . '</span><div>'
            . '<h3>' . $t('about_title') . '</h3>'
            . '<p>' . $t('about_intro') . '</p>'
            . '<ul>' . $item('about_import') . $item('about_hide') . $item('about_retire') . $item('about_fix') . $item('about_cancel') . '</ul>'
            . '</div></div>';
    }
}
