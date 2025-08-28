<?php

namespace ModulesGarden\TTSGGSModule\App\UI\Admin\Home\Index\Components;

use ModulesGarden\TTSGGSModule\Components\Alert\Alert;
use ModulesGarden\TTSGGSModule\Core\Components\Enums\Color;

class ManagerCard extends Alert
{
    public function __construct($name, $email, $phone, $photo)
    {
        parent::__construct();

        if(!file_get_contents($photo))
        {
            $photo = '../modules/addons/TTSGGSModule/storage/assets/photoPlaceholder.jpg'; //placeholder
        }

        $this->setType(Color::SECONDARY);
        $this->setSlot('css', 'manager');

        $content = <<<CONTENT
                    <div  class="manager-photo-container">
                        <img class="manager-photo" src="{$photo}" height="100">
                    </div>
                    <div class="manager-details-container">
                        <p><strong>{$name}</strong></p>
                        <p>{$email}</p>
                        <p>{$phone}</p>
                    </div>
                </div>
CONTENT;
        $this->setText($content);
    }
}