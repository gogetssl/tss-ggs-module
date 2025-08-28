<?php

namespace ModulesGarden\TTSGGSModule\Core\Packages;

use ModulesGarden\TTSGGSModule\Core\ModuleConstants;

class Packages
{
    public function getPackagesInstallFiles(): array
    {
        $packages = [];

        $packagesDir = ModuleConstants::getFullPath('packages');

        $scan = scandir($packagesDir);

        foreach ($scan as $package)
        {
            $packageDirectory   = $packagesDir . DIRECTORY_SEPARATOR . $package;
            $packageInstallFile = $packageDirectory . DIRECTORY_SEPARATOR . 'install.php';

            if (is_dir($packageDirectory) && is_file($packageInstallFile))
            {
                $packages[basename(dirname($packageInstallFile))] = $packageInstallFile;
            }
        }

        return $packages;
    }
}
