<?php

namespace ModulesGarden\TTSGGSModule\Core\WHMCS;

class VersionComparator
{
    public static function isWhmcsVersionHigherOrEqual($toCompare)
    {
        if (isset($GLOBALS['CONFIG']['Version']))
        {
            $version = explode('-', $GLOBALS['CONFIG']['Version']);

            return version_compare($version[0], $toCompare, '>=');
        }

        global $whmcs;

        return version_compare($whmcs->getVersion()->getRelease(), $toCompare, '>=');
    }
}
