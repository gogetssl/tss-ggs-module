<?php
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;

use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Products\Index;
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;

use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Products\Index\DataTables\Products\ArrayDataProvider\DataTable;
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
use ModulesGarden\TSSGGSModule\Components\Alert\Alert;
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
use ModulesGarden\TSSGGSModule\App\Http\Admin\Products as ProductsController;
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
use ModulesGarden\TSSGGSModule\Components\Alert\AlertDanger;
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
use ModulesGarden\TSSGGSModule\Components\Alert\AlertInfo;
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
use ModulesGarden\TSSGGSModule\Components\Alert\AlertSuccess;
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;

use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
class ProductsContainer extends Container implements AdminAreaInterface
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
{
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
    public function loadHtml(): void
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
    {
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
        $session = $_SESSION[ProductsController::SESSION] ?? [];
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
        unset($_SESSION[ProductsController::SESSION]);
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
        if(!empty($session['error']))
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
        {
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
            $this->addElement((new AlertDanger())->setText($session['error']));
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
        }
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
        if(!empty($session['notice']))
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
        {
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
            $notice = $session['notice'];
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
            $this->addElement((new AlertSuccess())->setText($this->translate('notice_' . $notice['key'], ['product' => $notice['product'], 'detail' => $notice['detail'] !== '' ? $notice['detail'] : '-'])));
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
        }
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;

use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
        $text = new Text();
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
        $text->setCss('tssggs-html');
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
        $text->setText($this->explanation());
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
        $this->addElement($text);
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;

use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
        $this->addElement(new DataTable());
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
    }
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;

use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
    /** What the page is for and what the actions in the Manage column do */
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
    protected function explanation(): string
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
    {
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
        $t    = function($key) { return htmlspecialchars((string)$this->translate($key, [], ['admin.products']), ENT_QUOTES); };
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
        $item = function($key) use ($t) { return '<li><b>' . $t($key . '_name') . '</b> ' . $t($key) . '</li>'; };
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;

use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
        return '<div class="tssggs-explain"><span class="tssggs-explain__icon">' . Icons::svg('information-outline') . '</span><div>'
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
            . '<h3>' . $t('about_title') . '</h3>'
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
            . '<p>' . $t('about_intro') . '</p>'
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
            . '<ul>' . $item('about_import') . $item('about_hide') . $item('about_retire') . $item('about_fix') . $item('about_cancel') . '</ul>'
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
            . '</div></div>';
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
    }
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
}
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
