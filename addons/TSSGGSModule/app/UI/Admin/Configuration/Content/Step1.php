<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Configuration\Content;

use ModulesGarden\TSSGGSModule\Components\Button\ButtonSuccess;
use ModulesGarden\TSSGGSModule\Components\Image\Image;
use ModulesGarden\TSSGGSModule\Components\Row\Row;
use ModulesGarden\TSSGGSModule\Components\Text\Text;
use ModulesGarden\TSSGGSModule\Components\Widget\Widget;
use ModulesGarden\TSSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TSSGGSModule\Core\Components\Action;
use ModulesGarden\TSSGGSModule\Core\Translation\TranslatorTrait;

class Step1 extends Widget implements AjaxComponentInterface
{

    use TranslatorTrait;

    public function loadHtml(): void
    {
        global $CONFIG;

        $this->setTitle($this->translate('step1_title'));

        $text = new Text();
        $text->setText($this->translate('first_p').'<br><br>');
        $this->addElement($text);

        $row = new Row();
        $this->addElement($row);

        $text = new Text();
        $text->setText($this->translate('second_p').'<br><br>');
        $this->addElement($text);

        $row = new Row();
        $this->addElement($row);

        $text = new Text();
        $text->setText($this->translate('third_p'));
        $this->addElement($text);

        $row = new Row();
        $this->addElement($row);

        $img = new Image();
        $img->setCss('mt30_custom');
        $img->setUrl($CONFIG['SystemURL'].'/modules/addons/TSSGGSModule/resources/assets/img/img_providers.png');
        $this->addElement($img);

        $row = new Row();
        $this->addElement($row);

        $nextStep = new ButtonSuccess();
        $nextStep->setCss('lu-btn lu-btn--success mt30_custom');
        $nextStep->onClick(Action::redirect('addonmodules.php?module=TSSGGSModule&mg-page=configuration&mg-action=step2'));
        $nextStep->setTitle($this->translate('next_step_btn'));
        $this->addElement($nextStep);


    }
}