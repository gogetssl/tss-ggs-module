<?php
// phpcs:ignoreFile

use ModulesGarden\TTSGGSModule\Packages\RequirementsChecker\Checkers\ClassExists;
use ModulesGarden\TTSGGSModule\Packages\RequirementsChecker\Checkers\DirectoryExists;
use ModulesGarden\TTSGGSModule\Packages\RequirementsChecker\Checkers\ExtensionExists;
use ModulesGarden\TTSGGSModule\Packages\RequirementsChecker\Checkers\FileExists;
use ModulesGarden\TTSGGSModule\Packages\RequirementsChecker\Checkers\FunctionExists;
use ModulesGarden\TTSGGSModule\Packages\RequirementsChecker\Checkers\IsReadable;
use ModulesGarden\TTSGGSModule\Packages\RequirementsChecker\Checkers\IsWritable;
use ModulesGarden\TTSGGSModule\Packages\RequirementsChecker\Checkers\MethodExists;
use ModulesGarden\TTSGGSModule\Packages\RequirementsChecker\Checkers\PhpMinVersion;
use ModulesGarden\TTSGGSModule\Packages\RequirementsChecker\Checkers\WhmcsMinVersion;

return [
    /*new PhpMinVersion('7.2.0'),
    new WhmcsMinVersion('8.9.0'),
    new MethodExists('SimpleXMLElement', 'attributes'),
    new DirectoryExists('/var/www'),
    new FileExists('/var/www/html/index.php'),
    new FunctionExists('exec'),
    new ClassExists('SimpleXMLElement'),
    new ExtensionExists('mysqli'),
    new IsWritable('/var/www'),
    new IsReadable('/var/www')*/
];

