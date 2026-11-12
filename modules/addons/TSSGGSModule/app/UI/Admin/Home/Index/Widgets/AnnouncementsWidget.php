<?php

namespace ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Widgets;

use ModulesGarden\TSSGGSModule\App\Libs\Helpers;
use ModulesGarden\TSSGGSModule\App\Libs\SSLTrustCenterApi;
use ModulesGarden\TSSGGSModule\App\UI\Admin\Home\Index\Shared\Card;

/**
 * Announcements published by the gateway (cached in the module's request table)
 */
class AnnouncementsWidget extends Card
{
    const ALLOWED_TAGS = '<a><b><strong><i><em><br><p><ul><ol><li>';

    /** category => [tile icon, tile tone] */
    const TONES = [
        'critical' => ['alert-octagon-outline', 'bad'],
        'warning'  => ['alert-outline', 'warn'],
    ];

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

        $html = '';
        foreach($items as $a)
        {
            [$icon, $tone] = self::TONES[$a['category']] ?? ['bullhorn-outline', 'info'];
            $html .= '<article class="tssggs-alertrow">' . Card::tile($icon, $tone)
                . '<div><h4>' . Card::e($a['title']) . ($a['vendor'] !== '' ? ' <small>' . Card::e($a['vendor']) . '</small>' : '') . '</h4>'
                . '<div class="tssggs-alertrow__text">' . self::safeHtml($a['body']) . '</div></div>'
                . '<time>' . Card::e($a['date'] !== '' ? date('j M Y', strtotime($a['date'])) : '') . '</time></article>';
        }
        $this->body('<div class="tssggs-alerts">' . $html . '</div>');
    }

    /** The allowed tags without their attributes; a link keeps its http(s) address only */
    protected static function safeHtml($body): string
    {
        return (string)preg_replace_callback('/<([a-z0-9]+)\b[^>]*>/i', function($tag) {
            $name = strtolower($tag[1]);
            if($name === 'a' && preg_match('/href\s*=\s*(["\'])(https?:\/\/[^"\'<>\s]+)\1/i', $tag[0], $href))
            {
                return '<a href="' . htmlspecialchars($href[2], ENT_QUOTES, 'UTF-8', false) . '" target="_blank" rel="noopener">';
            }

            return '<' . $name . '>';
        }, strip_tags((string)$body, self::ALLOWED_TAGS));
    }

    /** Announcements of the configured vendors; every gateway returns all of them, so one call is enough */
    protected function announcements(): array
    {
        // only the news of this edition's gateway (GoGetSSL or TheSSLStore). The list needs no sign-in at the
        // gateway, so it also works before the API credentials are entered.
        $vendor = GatewayWidget::vendor();
        $apis   = Helpers::getConfiguredApis();
        $api    = $apis[$vendor] ?? reset($apis) ?: new SSLTrustCenterApi($vendor, '', '');

        $items = [];
        foreach((array)$api->getAnnouncements() as $a)
        {
            if(strtoupper((string)($a['owner'] ?? '')) !== $vendor)
            {
                continue;
            }
            $items[] = [
                'title'    => (string)($a['title'] ?? ''),
                'body'     => (string)($a['body'] ?? ''),
                'date'     => (string)($a['date'] ?? ''),
                'category' => strtolower((string)($a['category'] ?? '')),
                'vendor'   => '',
            ];
        }

        return $items;
    }
}
