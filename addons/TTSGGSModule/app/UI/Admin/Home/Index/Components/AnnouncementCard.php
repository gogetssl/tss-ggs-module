<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Home\Index\Components;

use ModulesGarden\TTSGGSModule\Components\Alert\Alert;
use ModulesGarden\TTSGGSModule\Core\Components\Enums\Color;

class AnnouncementCard extends Alert
{
    public function __construct($title, $vendor, $description, $date, $category)
    {
        parent::__construct();

        if($category == 'critical')
        {
            $this->setType(Color::DANGER);
        }
        else
        {
            $this->setType(Color::SECONDARY);
        }

        $dateFormated = date('jS F Y', strtotime($date));

        $content = <<<CONTENT
                <p class="announcement-date" style="float: right">$dateFormated</p>
                <p class="announcement-title" style="font-size: 20px">{$title} </p>
                <p class="announcement-vendor">{$vendor}</p>
                <p class="announcement-black">{$description}</p>
CONTENT;
        $this->setText($content);
    }
}