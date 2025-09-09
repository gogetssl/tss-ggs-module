<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Components;

use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\Components\Alert\Alert;
use ModulesGarden\TSSGGSModule\Core\Components\Enums\Color;

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