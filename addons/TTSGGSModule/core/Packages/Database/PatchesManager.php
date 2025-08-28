<?php

namespace ModulesGarden\TTSGGSModule\Core\Packages\Database;

use ModulesGarden\TTSGGSModule\Core\ModuleConstants;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\LogActivity;

abstract class PatchesManager
{
    protected string $patchesNamespace;
    protected string $patchesDir;

    public function __construct()
    {
        $reflection = new \ReflectionClass($this);

        $this->patchesNamespace = $reflection->getNamespaceName() . '\Patches\\';
        $this->patchesDir = dirname($reflection->getFileName()) . "/Patches";
    }

    public function executeAll():void
    {
        $patches = $this->getAll();

        $executed = [];

        foreach ($patches as $patch)
        {
            $className = $this->patchesNamespace . $patch;

            $this->executePatch($className, $executed);
        }
    }

    protected function getAll(): array
    {
        $patches = scandir($this->patchesDir) ?: [];

        $patches = array_filter($patches, function($file) {
            return !in_array($file, ['.', '..']);
        });

        return array_map(function($file) {
            return str_replace('.php', '', $file);
        }, $patches);
    }

    protected function executePatch(string $patchClass, array &$executed):void
    {
        try
        {
            if (!class_exists($patchClass) || in_array($patchClass, $executed))
            {
                return;
            }

            $patchObj = new $patchClass();

            if (!is_subclass_of($patchObj, PatchInterface::class))
            {
                return;
            }

            $requiredPatches = $patchObj->requires();

            if (!empty($requiredPatches))
            {
                foreach ($requiredPatches as $requiredPatch)
                {
                    if (!is_string($requiredPatch))
                    {
                        continue;
                    }

                    $this->executePatch($requiredPatch, $executed);
                }
            }

            $patchObj->execute();

            $executed[] = $patchClass;
        }
        catch (\Exception $ex)
        {
            LogActivity::error($ex->getMessage(), [
                'module'    => ModuleConstants::getModuleName(),
                'patch'     => $patchClass
            ]);
        }
    }

}