<?php

namespace ModulesGarden\TSSGGSModule\App\Migration;

use ModulesGarden\TSSGGSModule\App\Migration\Readers\GgsReader;
use ModulesGarden\TSSGGSModule\App\Migration\Readers\LegacyReaderInterface;
use ModulesGarden\TSSGGSModule\App\Migration\Readers\TssReader;

/**
 * Finds legacy modules on this WHMCS install. Read-only.
 */
class Detector
{
    /** @return LegacyReaderInterface[] keyed by source */
    public static function readers(): array
    {
        return ['ggs' => new GgsReader(), 'tss' => new TssReader()];
    }

    public static function reader(string $source): LegacyReaderInterface
    {
        $readers = self::readers();
        if(!isset($readers[$source]))
        {
            throw new \InvalidArgumentException('Unknown legacy source: ' . $source);
        }

        return $readers[$source];
    }

    /**
     * One entry per legacy module that leaves any trace (addon row, tables or products).
     * Each: source, label, version, support, installed (files), active (addon), counts, tables
     */
    public static function detect(): array
    {
        $found = [];
        foreach(self::readers() as $source => $reader)
        {
            $hasAddonRow = $reader->version() !== null;
            $hasTables   = count($reader->tables()) > 0;
            $hasProducts = count($reader->productIds()) > 0;

            if(!$hasAddonRow && !$hasTables && !$hasProducts)
            {
                continue;
            }

            $found[$source] = [
                'source'    => $source,
                'label'     => $reader->label(),
                'version'   => $reader->version(),
                'support'   => $reader->versionSupport(),
                'installed' => $reader->filesPresent(),
                'active'    => $reader->isAddonActive(),
                'counts'    => $reader->counts(),
                'tables'    => $reader->tables(),
                'cronLines' => $reader->cronLines(),
            ];
        }

        return $found;
    }

    public static function anyFound(): bool
    {
        return count(self::detect()) > 0;
    }
}
