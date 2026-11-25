<?php

namespace ModulesGarden\TSSGGSModule\App\Libs\Settings;

use ModulesGarden\TSSGGSModule\App\Kernel\Lang;
use ModulesGarden\TSSGGSModule\App\Libs\Billing\RenewalPolicy;
use ModulesGarden\TSSGGSModule\App\Libs\Certificates\CertificateService;
use ModulesGarden\TSSGGSModule\App\Libs\Certificates\IssuedMessage;
use ModulesGarden\TSSGGSModule\App\Libs\Support\Edition;

/**
 * What Settings > SSL Certificates and Settings > Automated SSL show besides their fields: the certificate
 * authorities with an agreement, the issued-message families and languages, and the "renewal check" boxes that say in
 * words what the invoice lead and the payment term mean together.
 */
class SslScreen
{
    const TEXTS = 'admin.settings.index.forms.ssl_settings_form.';

    public static function data(Lang $lang): array
    {
        $families = [];
        foreach(array_keys(IssuedMessage::FAMILIES) as $family)
        {
            $brand = $lang->get(self::TEXTS . 'issuedMessageFor_' . $family);
            $texts = [];
            foreach(IssuedMessage::languages() as $language)
            {
                $texts[] = [
                    'name'  => IssuedMessage::textKey($family, $language),
                    'label' => str_replace(['{brand}', '{language}'], [$brand, ucfirst($language)], $lang->get(self::TEXTS . 'issuedMessageText')),
                    // a language the built-in text is not translated into shows the English one
                    'hint'  => $lang->get(self::TEXTS . (IssuedMessage::translated($language) ? 'issuedMessageText_description' : 'issuedMessageTextEnglish_description')),
                ];
            }
            $families[] = ['name' => $family, 'brand' => $brand, 'switch' => IssuedMessage::SWITCH_PREFIX . $family, 'texts' => $texts];
        }

        $languages = [];
        foreach(IssuedMessage::languages() as $language)
        {
            $languages[$language] = ucfirst($language);
        }

        return [
            'authorities'     => array_keys(CertificateService::AUTHORITIES),
            'downloadFormats' => ['zip', 'crt', 'ca', 'fullchain', 'nginx', 'apache', 'pkcs7'],
            'technicalFields' => ['firstName', 'lastName', 'companyName', 'jobTitle', 'emailAddress', 'address', 'city', 'zipCode', 'stateRegion', 'postCode'],
            'families'        => $families,
            'languages'       => $languages,
            'checkCertificate'  => self::renewalCheck($lang, RenewalPolicy::CERTIFICATE),
            'checkSubscription' => self::renewalCheck($lang, RenewalPolicy::SUBSCRIPTION),
            // waiting past the renewal day: the vendor charges before the payment has cleared
            'pendingWaitRisk' => RenewalPolicy::pendingWait(RenewalPolicy::SUBSCRIPTION) > 0,
            'wiki'            => Edition::wiki(),
        ];
    }

    /**
     * The box under the renewal invoice lead and payment term of a type: its texts (every one translated; the page
     * script resources/assets/js/custom/renewalCheck.js only puts the numbers in while the fields change) and the box
     * as it is for the saved values.
     */
    protected static function renewalCheck(Lang $lang, string $type): array
    {
        [, $leadKey, $termKey] = RenewalPolicy::KEYS[$type];
        $prefix = $type === RenewalPolicy::SUBSCRIPTION ? 'renewalCheckSubscription' : 'renewalCheckCertificate';
        $texts  = [
            'before' => $lang->get(self::TEXTS . $prefix . 'Before'),
            'on'     => $lang->get(self::TEXTS . $prefix . 'On'),
            'after'  => $lang->get(self::TEXTS . $prefix . 'After'),
            'amber'  => $lang->get(self::TEXTS . ($type === RenewalPolicy::SUBSCRIPTION ? 'renewalDueDayBeforeRenewal' : 'renewalDueAfterCertificateEnds')),
            'red'    => $lang->get(self::TEXTS . 'renewalDueAfterSubscriptionRenews'),
        ];

        return ['type' => $type, 'lead' => $leadKey, 'term' => $termKey, 'texts' => $texts,
                'body' => self::renewalCheckBody($type, RenewalPolicy::lead($type), RenewalPolicy::term($type), $texts)];
    }

    /** The inner HTML of a renewal check - the same rules as renewalCheck.js */
    public static function renewalCheckBody(string $type, int $lead, int $term, array $texts): string
    {
        $after   = $term - $lead;
        $vars    = [':lead' => $lead, ':term' => $term, ':days' => abs($after), ':safe' => RenewalPolicy::safeTerm($type, $lead)];
        $summary = strtr($after < 0 ? $texts['before'] : ($after === 0 ? $texts['on'] : $texts['after']), $vars);
        $html    = '<p class="tssggs-renewal-check__summary">' . htmlspecialchars($summary) . '</p>';
        $warning = RenewalPolicy::warning($type, $lead, $term);
        if($warning)
        {
            $text  = $warning['level'] === 'red' ? $texts['red'] : $texts['amber'];
            $html .= '<p class="tssggs-renewal-check__warning tssggs-renewal-check__warning--' . $warning['level'] . '">' . htmlspecialchars(strtr($text, $vars)) . '</p>';
        }

        return $html;
    }
}
