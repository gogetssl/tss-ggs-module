<?php

namespace Tests\Unit\Support;

use ModulesGarden\TSSGGSModule\App\Libs\Support\Edition;
use PHPUnit\Framework\TestCase;

/**
 * Edition reads app/Config/edition.php by a path relative to its own file. When that path is wrong it silently falls
 * back to the GoGetSSL edition (wrong tables, wrong credentials, wrong name) - this test catches a moved file.
 */
class EditionTest extends TestCase
{
    public function testEditionComesFromTheConfigFile(): void
    {
        $file   = dirname(__DIR__, 3) . '/modules/addons/TSSGGSModule/app/Config/edition.php';
        $config = include $file;
        $this->assertIsArray($config, 'app/Config/edition.php must return an array');
        $this->assertSame(strtolower($config['edition']), Edition::get());
    }

    public function testModuleFolderIsTheAddonFolder(): void
    {
        $this->assertSame('TSSGGSModule', Edition::folder());
    }
}
