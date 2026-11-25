<?php

namespace TSSGGSModule\App\Libs\Dashboard;

use TSSGGSModule\App\Libs\Gateway\SSLTrustCenterApi;
use TSSGGSModule\App\Libs\Support\Edition;
use TSSGGSModule\App\Libs\Support\Helpers;

/**
 * Announcements the vendor publishes (dashboard panel). Their text is the vendor's HTML: only a few harmless tags
 * are kept, without attributes.
 */
class Announcements
{
    const ALLOWED_TAGS = '<a><b><strong><i><em><br><p><ul><ol><li>';

    /** category => [tile icon, tile tone]; any other: bullhorn, info */
    const TONES = [
        'critical' => ['alert-octagon-outline', 'bad'],
        'warning'  => ['alert-outline', 'warn'],
    ];

    /** Gateway key of the edition: GGS | TSS */
    public static function vendor(): string
    {
        return Edition::get() === Edition::TSS ? 'TSS' : 'GGS';
    }

    /**
     * @return array[] title, body (safe HTML), date, icon, tone - only the news of this edition's vendor. The list
     *                 needs no sign-in at the vendor, so it also works before the API credentials are entered.
     * @throws \Throwable when the vendor does not answer
     */
    public static function latest(): array
    {
        $vendor = self::vendor();
        $apis   = Helpers::getConfiguredApis();
        $api    = $apis[$vendor] ?? reset($apis) ?: new SSLTrustCenterApi($vendor, '', '');

        $items = [];
        foreach((array)$api->getAnnouncements() as $a)
        {
            if(strtoupper((string)($a['owner'] ?? '')) !== $vendor)
            {
                continue;
            }
            [$icon, $tone] = self::TONES[strtolower((string)($a['category'] ?? ''))] ?? ['bullhorn-outline', 'info'];
            $date          = (string)($a['date'] ?? '');
            $items[]       = [
                'title' => (string)($a['title'] ?? ''),
                'body'  => self::safeHtml((string)($a['body'] ?? '')),
                'date'  => $date !== '' ? date('j M Y', strtotime($date)) : '',
                'icon'  => $icon,
                'tone'  => $tone,
            ];
        }

        return $items;
    }

    /** The allowed tags without their attributes; a link keeps its http(s) address only */
    public static function safeHtml(string $body): string
    {
        return (string)preg_replace_callback('/<([a-z0-9]+)\b[^>]*>/i', function($tag) {
            $name = strtolower($tag[1]);
            if($name === 'a' && preg_match('/href\s*=\s*(["\'])(https?:\/\/[^"\'<>\s]+)\1/i', $tag[0], $href))
            {
                return '<a href="' . htmlspecialchars($href[2], ENT_QUOTES, 'UTF-8', false) . '" target="_blank" rel="noopener">';
            }

            return '<' . $name . '>';
        }, strip_tags($body, self::ALLOWED_TAGS));
    }
}
