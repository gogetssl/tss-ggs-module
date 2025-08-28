<?php

namespace ModulesGarden\TTSGGSModule\Packages\Samples\UI\Admin\Home\SimpleForm\Alerts;

use ModulesGarden\TTSGGSModule\Components\Alert\Alert;
use ModulesGarden\TTSGGSModule\Core\Components\Enums\Color;
use ModulesGarden\TTSGGSModule\Core\Contracts\Components\AdminAreaInterface;


class RandomAlert extends Alert implements AdminAreaInterface
{
    public function loadData(): void
    {
        $colors = [
            Color::DANGER,
            Color::INFO,
            Color::PRIMARY,
            Color::SUCCESS,
            Color::WARNING,
        ];
        $this->setType($colors[rand(0, 4)]);
    }

    public function loadHtml(): void
    {
        $this->setText('This is some text');
    }
}
