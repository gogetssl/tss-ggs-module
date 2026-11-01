<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets;

use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared\Card;

/**
 * Module documentation: the GitHub pages of the two editions' modules
 */
class DocumentationWidget extends Card
{
    const LINKS = [
        'thesslstore' => 'https://github.com/thestores/whmcs/',
        'gogetssl'    => 'https://github.com/gogetssl/whmcs',
    ];

    public function loadHtml(): void
    {
        $this->start('book-open-page-variant-outline', $this->translate('title'));

        $links = '';
        foreach(self::LINKS as $key => $url)
        {
            $links .= '<a class="tssggs-dbtn" href="' . Card::e($url) . '" target="_blank" rel="noopener noreferrer">' . Card::e($this->translate('open_' . $key)) . Card::icon('open-in-new') . '</a>';
        }

        $this->body('<p class="tssggs-muted">' . Card::e($this->translate('info')) . '</p><div class="tssggs-dcard__links">' . $links . '</div>');
    }
}
