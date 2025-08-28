<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Configuration\Content;

use ModulesGarden\TTSGGSModule\Components\Button\ButtonSuccess;
use ModulesGarden\TTSGGSModule\Components\Image\Image;
use ModulesGarden\TTSGGSModule\Components\Row\Row;
use ModulesGarden\TTSGGSModule\Components\Text\Text;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;
use ModulesGarden\TTSGGSModule\Core\Components\Action;
use ModulesGarden\TTSGGSModule\Core\Translation\TranslatorTrait;

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
        $img->setUrl($CONFIG['SystemURL'].'/modules/addons/TTSGGSModule/resources/assets/img/img_providers.png');
        $this->addElement($img);

        $row = new Row();
        $this->addElement($row);

        $nextStep = new ButtonSuccess();
        $nextStep->setCss('lu-btn lu-btn--success mt30_custom');
        $nextStep->onClick(Action::redirect('addonmodules.php?module=TTSGGSModule&mg-page=configuration&mg-action=step2'));
        $nextStep->setTitle($this->translate('next_step_btn'));
        $this->addElement($nextStep);


    }
}