<?php

namespace ModulesGarden\TTSGGSModule\Core\Configuration\Addon\Update;

use Exception;
use ModulesGarden\TTSGGSModule\Core\Configuration\Addon\Update\Patch\AbstractPatch;
use ModulesGarden\TTSGGSModule\Core\DependencyInjection;
use ModulesGarden\TTSGGSModule\Core\ModuleConstants;
use ModulesGarden\TTSGGSModule\Core\Support\Facades\LogActivity;

/**
 * Description of PatchManager
 */
class PatchManager
{
    const TYPE_ADDON  = 'Addon';
    const TYPE_SERVER = 'Server';
    protected $updateFiles = [];
    protected $updatePath;

    protected string $type = 'Addon';

    public function __construct(string $type = self::TYPE_ADDON)
    {
        $this->type       = $type;
        $this->updatePath = ModuleConstants::getModuleRootDir() . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Configuration' . DIRECTORY_SEPARATOR . $this->type . DIRECTORY_SEPARATOR . 'Update' . DIRECTORY_SEPARATOR . 'Patch';
        $this->loadUpdatePath();
    }

    protected function loadUpdatePath()
    {
        $files = scandir($this->getUpdatePath(), 1);

        if (count($files) != 0)
        {
            foreach ($files as $file)
            {
                if (pathinfo($file)['extension'] == 'php')
                {
                    $version                     = $this->generateVersion($file);
                    $this->updateFiles[$version] = explode('.', $file)[0];
                }
            }
        }

        uksort($this->updateFiles, 'version_compare');
    }

    protected function getUpdatePath()
    {
        return $this->updatePath;
    }

    protected function generateVersion($fileName)
    {
        $name = explode('.', $fileName)[0];

        return str_replace(['M', 'P'], '.', substr($name, 1));
    }

    public function run(string $version, bool $force = false)
    {
        $fullPath = $this->getUpdatePath();

        $allPatchVersions = $this->getUpdateFiles();

        if ($force)
        {
            $allPatchVersions = array_filter($allPatchVersions,  function($patchVersion) use ($version) {
                return $version == $patchVersion;
            }, ARRAY_FILTER_USE_KEY);
        }

        foreach ($allPatchVersions as $newVersion => $fileName)
        {
            if ($force || $this->checkVersion($newVersion, $version))
            {
                try
                {
                    $this->executeUpdate($newVersion, $fileName);

                    LogActivity::info(sprintf('Updated module from %s to %s', $version, $newVersion), [
                        'newVersion'    => $newVersion,
                        'oldVersion'    => $version,
                        'updateVersion' => $newVersion,
                        'fullFileName'  => $fullPath . DIRECTORY_SEPARATOR . $fileName . '.php',
                    ]);
                }
                catch (Exception $exc)
                {
                    LogActivity::error($exc->getMessage(), [
                        'newVersion'    => $newVersion,
                        'oldVersion'    => $oldVersion,
                        'updateVersion' => $newVersion,
                        'fullFileName'  => $fullPath . DIRECTORY_SEPARATOR . $fileName . '.php',
                    ]);
                }
                $oldVersion = $newVersion;
            }
        }

        return $this;
    }

    public function executeUpdate(string $version, string $fileName = "")
    {
        if (empty($fileName))
        {
            $fileName = $this->getUpdateFiles()[$version];
        }

        if (empty($fileName))
        {
            throw new Exception("No patch for this version found. Specified version: $version");
        }

        $className = ModuleConstants::getRootNamespace() . "\App\Configuration\\" . $this->type . "\Update\Patch\\" . $fileName;

        $classObject = DependencyInjection::create($className);

        if (!is_subclass_of($classObject, AbstractPatch::class))
        {
            throw new Exception("Incorrect patch found");
        }

        $classObject->setVersion($version)->execute();
    }

    protected function checkVersion($newVersion, $oldVersion): bool
    {
        if (version_compare($oldVersion, $newVersion, '<'))
        {
            return true;
        }

        return false;
    }

    public function getUpdateFiles()
    {
        return $this->updateFiles;
    }
}
