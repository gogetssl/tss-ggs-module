<?php

namespace ModulesGarden\Servers\TSSGGSModule\app\services;

use ModulesGarden\Servers\TSSGGSModule\core\Lang;
use ModulesGarden\TSSGGSModule\App\Libs\CaaCheck;

/** The CAA warning box in the client area (texts in the client's language) - see App\Libs\CaaCheck */
class CaaWarning
{
    /** Validation step of the configuration: the domains about to be ordered; '' when nothing blocks them */
    public static function beforeOrder($packageId, array $domains)
    {
        try
        {
            $authority = CaaCheck::authority($packageId);
            $blocked   = $authority ? CaaCheck::blocked($authority, $domains) : [];

            return $blocked ? CaaCheck::html(['blocked' => $blocked, 'gateway' => false], self::texts(true)) : '';
        }
        catch(\Throwable $e)
        {
            return ''; // a warning must never stop the configuration
        }
    }

    /** Pending certificate page: the names not validated yet; '' when nothing blocks them */
    public static function pending($serviceId, array $domains)
    {
        try
        {
            $warning = CaaCheck::forService($serviceId, $domains);

            return $warning ? CaaCheck::html($warning, self::texts(false)) : '';
        }
        catch(\Throwable $e)
        {
            return '';
        }
    }

    protected static function texts($beforeOrder)
    {
        $texts = [];
        foreach(['title', 'intro', 'inherited', 'now', 'add', 'fix', 'gateway'] as $key)
        {
            $texts[$key] = Lang::absoluteT('caa_' . $key);
        }
        $texts['extra'] = $beforeOrder ? Lang::absoluteT('caa_continue') : '';

        return $texts;
    }
}
