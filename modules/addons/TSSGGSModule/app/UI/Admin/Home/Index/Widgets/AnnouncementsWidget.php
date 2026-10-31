<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets;

use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared\Card;

/**
 * Announcements published by the gateway (cached in the module's request table)
 */
class AnnouncementsWidget extends Card
{
    const ALLOWED_TAGS = '<a><b><strong><i><em><br><p><ul><ol><li>';

    public function loadHtml(): void
    {
        $this->start('bell-outline', $this->translate('title'), '<span class="tssggs-dcard__aside">' . Card::e($this->translate('latest')) . '</span>');

        try
        {
            $items = $this->announcements();
        }
        catch(\Throwable $e)
        {
            $this->body('<p class="tssggs-muted">' . Card::e($this->translate('unavailable', ['error' => $e->getMessage()])) . '</p>');

            return;
        }

        if(!$items)
        {
            $this->body('<p class="tssggs-muted">' . Card::e($this->translate('none')) . '</p>');

            return;
        }

        $html = '<div class="tssggs-list">';
        foreach($items as $a)
        {
            $tag   = implode(' · ', array_filter([ucfirst(str_replace('_', ' ', $a['category'])), $a['vendor']]));
            $html .= '<article class="tssggs-news' . ($a['category'] !== '' ? ' is-' . preg_replace('/[^a-z0-9_-]/', '', $a['category']) : '') . '">'
                . '<div class="tssggs-news__meta"><span class="tssggs-news__tag">' . Card::e($tag) . '</span>'
                . '<time>' . Card::e($a['date'] !== '' ? date('j M Y', strtotime($a['date'])) : '') . '</time></div>'
                . '<h4>' . Card::e($a['title']) . '</h4>'
                . '<div class="tssggs-news__body">' . strip_tags($a['body'], self::ALLOWED_TAGS) . '</div>'
                . '</article>';
        }
        $this->addElement(Card::html($html . '</div>'));
    }

    /** Announcements of the configured vendors; every gateway returns all of them, so one call is enough */
    protected function announcements(): array
    {
        $apis = Helpers::getConfiguredApis();
        $api  = reset($apis);
        if(!$api)
        {
            return [];
        }

        $items = [];
        foreach((array)$api->getAnnouncements() as $a)
        {
            $owner = strtoupper((string)($a['owner'] ?? ''));
            if(!array_key_exists($owner, $apis))
            {
                continue;
            }
            $items[] = [
                'title'    => (string)($a['title'] ?? ''),
                'body'     => (string)($a['body'] ?? ''),
                'date'     => (string)($a['date'] ?? ''),
                'category' => strtolower((string)($a['category'] ?? '')),
                'vendor'   => count($apis) > 1 ? Helpers::vendorToDisplay($owner) : '',
            ];
        }

        return $items;
    }
}
