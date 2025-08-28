<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\CollapsableBox\Widgets;

use ModulesGarden\TTSGGSModule\Components\Accordion\Accordion;
use ModulesGarden\TTSGGSModule\Components\AccordionElement\AccordionElement;
use ModulesGarden\TTSGGSModule\Components\Text\Text;
use ModulesGarden\TTSGGSModule\Components\Widget\Widget;
use ModulesGarden\TTSGGSModule\Core\Components\Enums\BackgroundColor;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AjaxComponentInterface;

class AccordionWidget extends Widget implements AjaxComponentInterface, AdminAreaInterface
{
    public function loadHtml(): void
    {
        $this->setTitle("Accordion Example (Tylko jeden tab może być aktywny)");

        $accordion = new Accordion();

        $elements = [
            "Accordion 1" => "Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.",
            "Accordion 2" => "Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old. Richard McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage, and going through the cites of the word in classical literature, discovered the undoubtable source.",
            "Accordion 3" => "There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don't look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden in the middle of text. "];

        foreach ($elements as $name => $content)
        {
            $accordionElement = new AccordionElement();
            $accordionElement->setTitle($name);
            $accordionElement->addElement((new Text())->setText($content));
            $accordionElement->removeIcon();
            $accordionElement->setTextCentered();
            $accordionElement->setType(BackgroundColor::WARNING);

            $accordion->addItem($accordionElement);
        }

        $this->addElement($accordion);
    }
}