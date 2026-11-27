<?php

/**
 * The module's stylesheet for the admin area: the CSS files of resources/assets/css joined into ONE response.
 * A reseller's copy of a file in overrides/assets/css (same file name) is used instead of the module's own.
 * Loaded by the admin layout (resources/templates/admin/layout.tpl); needs nothing of WHMCS or the module.
 *
 * The files are joined here instead of @import-ed: an @import is fetched only after this file arrived, one more round
 * trip before the page has its styles, so the page showed unstyled first and jumped into place.
 */

$files = [
    'ui-base.css',
    'ui-controls.css',
    'ui-layout.css',
    'pages.css',
    'wizard.css',
    'migration.css',
    'dashboard.css',
    'shell.css',
    'settings.css',
    'icons.css',
    'yesno.css',
    'theme.css',
];

if (isset($_REQUEST['type']) && $_REQUEST['type'] == 'module')
{
    $files[] = 'module_main_content.css';
}

// each file: its path on disk and its folder as seen from this script (relative url() values are rewritten to it)
$parts = [];
foreach ($files as $file)
{
    $override = dirname(__DIR__, 2) . '/overrides/assets/css/' . $file;
    $own      = dirname(__DIR__) . '/assets/css/' . $file;
    if (is_file($override))
    {
        $parts[] = [$override, '../../overrides/assets/css/'];
    }
    elseif (is_file($own))
    {
        $parts[] = [$own, '../assets/css/'];
    }
}

// the version from the layout plus every file's modification time: a changed file (an upgrade, an edited override)
// gives another ETag, so the browser does not keep old styles
$version = preg_replace('/[^0-9A-Za-z.\-]/', '', (string)($_GET['v'] ?? ''));
$etag    = '"' . md5($version . '|' . implode('|', array_map(function($part) {
    return $part[0] . ':' . filemtime($part[0]);
}, $parts))) . '"';

header('Content-Type: text/css; charset=utf-8');
header('ETag: ' . $etag);
// the URL changes with the module version; a short lifetime lets an edited override show within minutes
header('Cache-Control: public, max-age=300');

if (trim((string)($_SERVER['HTTP_IF_NONE_MATCH'] ?? '')) === $etag)
{
    http_response_code(304);
    exit;
}

foreach ($parts as $part)
{
    list($path, $base) = $part;
    $css = (string)file_get_contents($path);
    // url(...) relative to the CSS file's own folder (fonts, images) -> relative to this script
    $css = preg_replace_callback('/url\(\s*([\'"]?)(?![a-z][a-z0-9+.\-]*:|\/|#)([^\'")]+)\1\s*\)/i', function($m) use ($base) {
        return 'url(' . $m[1] . $base . $m[2] . $m[1] . ')';
    }, $css);
    echo "/* " . basename($path) . " */\n" . $css . "\n";
}
