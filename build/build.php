<?php

/**
 * Builds the two distribution packages from one git ref:
 *
 *   dist/GoGetSSL_Module_<version>.zip      edition 'ggs'
 *   dist/TheSSLStore_Plugin_<version>.zip   edition 'tss'
 *
 * The code is identical; only modules/addons/TSSGGSModule/app/Config/edition.php differs.
 *
 * Usage (from the repository root, PHP CLI with the zip and phar extensions):
 *   php build/build.php            # builds from HEAD
 *   php build/build.php v1.2.6     # builds from a tag / branch / commit
 */

const EDITION_FILE = 'modules/addons/TSSGGSModule/app/Config/edition.php';
const VERSION_FILE = 'modules/addons/TSSGGSModule/app/Config/configuration.yml';
const PACKAGE_PATHS = ['modules', 'feeds'];
const EDITIONS = [
    'ggs' => 'GoGetSSL_Module',
    'tss' => 'TheSSLStore_Plugin',
];

function fail($message)
{
    fwrite(STDERR, 'ERROR: ' . $message . PHP_EOL);
    exit(1);
}

function removeDir($dir)
{
    if(!is_dir($dir))
    {
        return;
    }

    $items = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach($items as $item)
    {
        $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
    }
    rmdir($dir);
}

if(!class_exists('ZipArchive') || !class_exists('PharData'))
{
    fail('PHP extensions zip and phar are required');
}

$root = realpath(__DIR__ . '/..');
$ref  = isset($argv[1]) ? $argv[1] : 'HEAD';

if(!preg_match('/^[A-Za-z0-9._\/-]+$/', $ref))
{
    fail('invalid git ref: ' . $ref);
}

// ---- Export the ref ----
$work = sys_get_temp_dir() . '/tssggs-build-' . getmypid();
removeDir($work);
mkdir($work . '/src', 0777, true);

$tar = $work . '/source.tar';
$cmd = 'git -C ' . escapeshellarg($root) . ' archive --format=tar -o ' . escapeshellarg($tar) . ' ' . escapeshellarg($ref)
    . ' -- ' . implode(' ', array_map('escapeshellarg', PACKAGE_PATHS)) . ' 2>&1';
exec($cmd, $out, $code);
if($code !== 0)
{
    removeDir($work);
    fail('git archive failed: ' . implode("\n", $out));
}

(new PharData($tar))->extractTo($work . '/src');

if(!is_file($work . '/src/' . EDITION_FILE))
{
    removeDir($work);
    fail(EDITION_FILE . ' not found in ' . $ref . ' (the ref predates the edition switch)');
}

if(!preg_match("/^version:\s*'([^']+)'/m", (string)file_get_contents($work . '/src/' . VERSION_FILE), $m))
{
    removeDir($work);
    fail('version not found in ' . VERSION_FILE);
}
$version = $m[1];

// ---- One zip per edition ----
$files = [];
$items = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($work . '/src', FilesystemIterator::SKIP_DOTS));
foreach($items as $item)
{
    if($item->isFile())
    {
        $files[] = str_replace('\\', '/', substr($item->getPathname(), strlen($work . '/src/')));
    }
}
sort($files);

$editionTemplate = file_get_contents($work . '/src/' . EDITION_FILE);
if(!preg_match("/'edition'\s*=>\s*'[a-z]+'/", $editionTemplate))
{
    removeDir($work);
    fail("'edition' => '...' line not found in " . EDITION_FILE);
}

@mkdir($root . '/dist', 0777, true);

foreach(EDITIONS as $edition => $packageName)
{
    $zipFile = $root . '/dist/' . $packageName . '_' . $version . '.zip';
    @unlink($zipFile);

    $zip = new ZipArchive();
    if($zip->open($zipFile, ZipArchive::CREATE) !== true)
    {
        removeDir($work);
        fail('cannot create ' . $zipFile);
    }

    foreach($files as $file)
    {
        if($file === EDITION_FILE)
        {
            $zip->addFromString($file, preg_replace("/'edition'\s*=>\s*'[a-z]+'/", "'edition' => '" . $edition . "'", $editionTemplate));
            continue;
        }

        $zip->addFile($work . '/src/' . $file, $file);
    }

    $zip->close();

    echo $packageName . ' ' . $version . ': ' . count($files) . ' files -> dist/' . basename($zipFile) . PHP_EOL;
}

removeDir($work);
