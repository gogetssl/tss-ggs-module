<?php

namespace ModulesGarden\Servers\TSSGGSModule\app\services;

use ModulesGarden\Servers\TSSGGSModule\core\Lang;
use ModulesGarden\TSSGGSModule\App\Libs\CaaCheck;

/** The CAA warning box on the client's pending certificate page (texts in the client's language) - see App\Libs\CaaCheck */
class CaaWarning
{
    /** Pending certificate page: the names not validated yet; '' when nothing blocks them */
    public static function pending($serviceId, array $domains)
    {
        try
        {
            $warning = CaaCheck::forService($serviceId, $domains);

            return $warning ? CaaCheck::html($warning, self::texts()) : '';
        }
        catch(\Throwable $e)
        {
            return '';
        }
    }

    protected static function texts()
    {
        $texts = [];
        foreach(['title', 'intro', 'inherited', 'now', 'add', 'fix', 'gateway', 'gateway_fix'] as $key)
        {
            $texts[$key] = Lang::absoluteT('caa_' . $key);
        }

        return $texts;
    }
}
