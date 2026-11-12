<?php

namespace ModulesGarden\TSSGGSModule\App\Migration;

use ModulesGarden\TSSGGSModule\App\Libs\SSLTrustCenterApi;
use ModulesGarden\TSSGGSModule\App\Repositories\Whmcs\AddonModuleRepository;

/**
 * Gateway credentials of the legacy modules, reused for this module.
 * Both legacy modules talk to the same gateway accounts, so their partner code / token fit the unified
 * credential block one to one (GoGetSSL: api_partner_code or login + API password; TheSSLStore: live/test pairs).
 * Existing unified credentials are never overwritten.
 */
class Credentials
{
    const STATE_ALREADY_SET = 'already_set';
    const STATE_AVAILABLE   = 'available';
    const STATE_MISSING     = 'missing';

    const EMPTY_BLOCK = ['PartnerCode' => '', 'AuthToken' => '', 'TestPartnerCode' => '', 'TestAuthToken' => '', 'OperationMode' => ''];

    /** Current unified credentials, every vendor block present */
    public static function current(): array
    {
        $cfg = (new AddonModuleRepository())->getModuleConfiguration();
        $all = (array)($cfg['credentials'] ?? []);
        foreach(['ggs', 'tss'] as $vendor)
        {
            $all[$vendor] = array_merge(self::EMPTY_BLOCK, (array)($all[$vendor] ?? []));
        }

        return $all;
    }

    public static function isSet(array $block): bool
    {
        $pair = ($block['OperationMode'] ?? '') === 'sandbox' ? ['TestPartnerCode', 'TestAuthToken'] : ['PartnerCode', 'AuthToken'];

        return trim((string)($block[$pair[0]] ?? '')) !== '' && trim((string)($block[$pair[1]] ?? '')) !== '';
    }

    /** Legacy credentials of one source, [] when the legacy module has none */
    public static function legacy(string $source): array
    {
        try
        {
            $block = Detector::reader($source)->readCredentials();
        }
        catch(\Throwable $e)
        {
            return [];
        }

        return self::isSet($block) ? array_merge(self::EMPTY_BLOCK, $block) : [];
    }

    /** already_set (unified has its own) | available (legacy has them, unified not) | missing */
    public static function state(string $source): string
    {
        if(self::isSet(self::current()[$source]))
        {
            return self::STATE_ALREADY_SET;
        }

        return self::legacy($source) ? self::STATE_AVAILABLE : self::STATE_MISSING;
    }

    /**
     * Unified credentials with empty vendor blocks filled from the legacy modules chosen for migration.
     * Returns [credentials, sources that were filled]. Nothing is stored.
     */
    public static function prefilled(): array
    {
        $all    = self::current();
        $filled = [];
        foreach(Choice::toMigrate() as $source)
        {
            if(!self::isSet($all[$source]) && ($legacy = self::legacy($source)))
            {
                $all[$source] = $legacy;
                $filled[]     = $source;
            }
        }

        return [$all, $filled];
    }

    /**
     * Stores the legacy credentials of $source when the unified block is still empty and the gateway accepts them.
     * Returns 'copied' | 'rejected: <reason>' | '' (nothing to do).
     */
    public static function applyIfEmpty(string $source): string
    {
        $all = self::current();
        if(self::isSet($all[$source]) || !($legacy = self::legacy($source)))
        {
            return '';
        }
        $error = self::verify($source, $legacy);
        if($error !== '')
        {
            return 'rejected: ' . $error;
        }
        $all[$source] = $legacy;
        self::save($all);

        return 'copied';
    }

    /** One read-only gateway call with the block; '' when accepted, else the error */
    public static function verify(string $source, array $block): string
    {
        $sandbox = ($block['OperationMode'] ?? '') === 'sandbox';
        $code    = $sandbox ? $block['TestPartnerCode'] : $block['PartnerCode'];
        $token   = $sandbox ? $block['TestAuthToken'] : $block['AuthToken'];
        try
        {
            (new SSLTrustCenterApi(strtoupper($source), $code, $token))->getProducts();
        }
        catch(\Throwable $e)
        {
            return $e->getMessage();
        }

        return '';
    }

    public static function save(array $all): void
    {
        $tss = array_merge(self::EMPTY_BLOCK, (array)($all['tss'] ?? []));
        $ggs = array_merge(self::EMPTY_BLOCK, (array)($all['ggs'] ?? []));
        (new AddonModuleRepository())->saveCredentials(
            $tss['PartnerCode'], $tss['AuthToken'], $ggs['PartnerCode'], $ggs['AuthToken'],
            $tss['TestPartnerCode'], $tss['TestAuthToken'], $tss['OperationMode']
        );
    }
}
