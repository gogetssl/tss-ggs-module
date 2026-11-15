<?php

require_once dirname(__FILE__, 3) . '/vendor/autoload.php';

\ModulesGarden\TSSGGSModule\Core\ModuleConstants::initialize();

$files = [
    'products-icons.css',
    'simplemde.min.css',
    'layers2-ui.css',
    'module_styles.css',
    'mg_styles.css',
    'vue2-datepicker.min.css',
    'wizard.css',
    'migration.css',
    'dashboard.css',
    'shell.css',
    'settings.css',
    'icons.css',
    'tinymce-icons.css',
    'yesno.css',
    'theme.css',
];

if (isset($_REQUEST['type']) && $_REQUEST['type'] == 'module')
{
    $files[] = 'module_main_content.css';
}

header('Content-Type: text/css');

// every imported file carries the module version and its own modification time: browsers cache an @import by its
// URL, so without them an upgrade (or a changed override) kept showing the old styles until the cache expired
$version = preg_replace('/[^0-9A-Za-z.\-]/', '', (string)($_GET['v'] ?? ''));
$stamp   = function($path) use ($version) {
    return '?v=' . rawurlencode($version . '.' . (is_file($path) ? filemtime($path) : 0));
};

foreach ($files as $file)
{
    $override = \ModulesGarden\TSSGGSModule\Core\ModuleConstants::getFullPath('overrides', 'assets', 'css', $file);
    if (file_exists($override))
    {
        echo "@import \"../../overrides/assets/css/$file" . $stamp($override) . "\";\n";
    }
    else
    {
        echo "@import \"../assets/css/$file" . $stamp(dirname(__DIR__) . '/assets/css/' . $file) . "\";\n";
    }
}

