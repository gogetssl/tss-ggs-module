<?php

namespace ModulesGarden\TSSGGSModule\Packages\Logs\UI\Widgets;

use ModulesGarden\TSSGGSModule\Components\Button\ButtonBasic;
use ModulesGarden\TSSGGSModule\Components\Card\CardButton;
use ModulesGarden\TSSGGSModule\App\Libs\GeneralSettings;
use ModulesGarden\TSSGGSModule\App\Libs\Icons;
use ModulesGarden\TSSGGSModule\Components\Container\Container;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
use ModulesGarden\TSSGGSModule\Components\Grid\Grid;
use ModulesGarden\TSSGGSModule\Core\Components\Actions\ReloadById;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TSSGGSModule\Core\Support\Facades\Config;
use ModulesGarden\TSSGGSModule\Packages\Logs\Enums\Status;
use ModulesGarden\TSSGGSModule\Packages\Logs\Models\Job;
use ModulesGarden\TSSGGSModule\Packages\Logs\Models\Logs;
use WHMCS\Database\Capsule as DB;

class Summary extends Container implements AdminAreaInterface
{
    public function loadHtml(): void
    {
        if(!GeneralSettings::enabled('hideInfoMessages'))
        {
            $intro = new Text();
            $intro->setCss('tssggs-html');
            $intro->setText($this->explanation());
            $this->addElement($intro);
        }

        //build per typy widgets
        $all      = Logs::select(DB::raw('\'total\' as type'), DB::raw('count(id) as count'));
        $types    = Logs::select('type', DB::raw('count(id) as count'))->union($all)->groupBy('type')->get();
        $elements = [];

        foreach ($types as $type)
        {
            $elements[] = [
                $this->buildCard(
                    $type->count,
                    $type->type,
                )];
        }

        $grid = new Grid();
        $grid->setRows([$elements]);
        $this->addElement($grid);
    }

    protected function explanation(): string
    {
        $t    = function($key) { return htmlspecialchars((string)$this->translate($key), ENT_QUOTES); };
        $item = function($key) use ($t) { return '<li>' . $t($key) . '</li>'; };

        return '<div class="tssggs-explain"><span class="tssggs-explain__icon">' . Icons::svg('information-outline') . '</span><div>'
            . '<h3>' . $t('aboutTitle') . '</h3>'
            . '<p>' . $t('aboutIntro') . '</p>'
            . '<ul>' . $item('aboutApi') . $item('aboutError') . $item('aboutDetails') . $item('aboutRelated') . $item('aboutSettings') . '</ul>'
            . '</div></div>';
    }

    protected function buildCard(int $count, $type)
    {
        $button = new (Config::get('install.logs.colors.' . $type . '.button', ButtonBasic::class));
        $button->setTitle($this->translate('show'));
        $button->onClick((new ReloadById("LogsDataTable"))->withParams([
            "filters" => $type != 'total' ? ['type' => $type] : []
        ]));


        $card = new CardButton();
        $card->setButton($button);
        $card->setIcon(Config::get('install.logs.colors.' . $type . '.icon', ''));
        $card->setTitle($this->translate($type . 'Title'));
        $card->setContent($count);

        if(in_array($type, ['info', 'error', 'total', 'warning', 'debug'], true))
        {
            $note = new Text();
            $note->setCss('tssggs-html tssggs-cardnote');
            $note->setText('<small>' . htmlspecialchars((string)$this->translate($type . 'Note'), ENT_QUOTES) . '</small>');
            $card->addElement($note);
        }

        return $card;
    }
}