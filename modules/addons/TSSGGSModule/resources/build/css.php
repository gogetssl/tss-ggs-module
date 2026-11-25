<?php

/**
 * The module's stylesheet for the admin area: one request that imports the CSS files of resources/assets/css.
 * A reseller's copy of a file in overrides/assets/css (same file name) is used instead of the module's own.
 * Loaded by the admin layout (resources/templates/admin/layout.tpl); needs nothing of WHMCS or the module.
 */

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
    $override = dirname(__DIR__, 2) . '/overrides/assets/css/' . $file;
    if (file_exists($override))
    {
        echo "@import \"../../overrides/assets/css/$file" . $stamp($override) . "\";\n";
    }
    else
    {
        echo "@import \"../assets/css/$file" . $stamp(dirname(__DIR__) . '/assets/css/' . $file) . "\";\n";
    }
}
