<?php

namespace ModulesGarden\TTSGGSModule\Packages\Product\Database\Patches;

use ModulesGarden\TTSGGSModule\Core\ServiceLocator;
use ModulesGarden\TTSGGSModule\Packages\Product\Database\Patches\Source\PatchInterface as ProductPatchInterface;

class PatchesManager
{
    public function executeAll()
    {
        $patches = $this->getAll();

        foreach ($patches as $patch)
        {
            $className = __NAMESPACE__ . '\Patches\\' . $patch;

            if (!class_exists($className))
            {
                continue;
            }

            $patchObj = new $className();

            if (!is_subclass_of($patchObj, ProductPatchInterface::class))
            {
                continue;
            }

            $patchObj->execute();
        }
    }

    protected function getAll(): array
    {
        $path    = __DIR__ . DIRECTORY_SEPARATOR . "Patches";
        $patches = scandir($path);

        $patches = array_filter($patches, function($file) {
            return !in_array($file, ['.', '..']);
        });

        return array_map(function($file) {
            return str_replace('.php', '', $file);
        }, $patches);
    }
}