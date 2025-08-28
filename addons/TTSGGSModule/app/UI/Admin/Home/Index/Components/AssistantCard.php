<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Home\Index\Components;

use ModulesGarden\TTSGGSModule\App\Libs\Helpers;
use ModulesGarden\TTSGGSModule\Components\Alert\Alert;
use ModulesGarden\TTSGGSModule\Core\Components\Enums\Color;

class AssistantCard extends Alert
{
    public function __construct($details)
    {
        parent::__construct();

        $content = '<div class="assistant-details-container">';

        foreach ($details as $detail)
        {
            if(isset($detail['url']))
            {
                $content .= "<p>{$detail["title"]} <a target='_blank' href=\"{$detail['url']}\"> {$detail["display"]}</a></p>";
            }
            else
            {
                $content .= "<p>{$detail["title"]} {$detail["display"]}</p>";
            }
        }

        $content.='</div>';


        $this->setType(Color::SECONDARY);

        $this->setText($content);
    }
}