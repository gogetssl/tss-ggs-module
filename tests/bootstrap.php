<?php

/**
 * Unit tests run against the module source (namespace ModulesGarden\TSSGGSModule) without WHMCS: the module's own
 * Composer autoloader resolves App\, Core\, Components\ and Packages\. Code that needs WHMCS or its database belongs
 * in tests/Smoke (run inside a WHMCS install by tools/qa.sh), not here.
 */

define('TSSGGS_TESTS', true);

require __DIR__ . '/../modules/addons/TSSGGSModule/vendor/autoload.php';
