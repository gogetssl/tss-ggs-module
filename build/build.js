#!/usr/bin/env node
/**
 * Builds the two distribution packages from one git ref. The source keeps the working name TSSGGSModule; each package
 * gets its own identity, so a client who installs one edition finds nothing of the other:
 *
 *   dist/GoGetSSL_Module_<version>.zip      modules/addons/ggsmodule, modules/servers/ggsmodule   (edition 'ggs')
 *   dist/TheSSLStore_Plugin_<version>.zip   modules/addons/tssmodule, modules/servers/tssmodule   (edition 'tss')
 *
 * What differs per edition (build/editions/<edition>.json):
 *   - the module folder / file names, PHP namespace, function prefixes and database table prefix
 *     (TSSGGSModule -> folder), so the editions have separate tables, settings and URLs
 *   - app/Config/edition.php (name, description, support - see App\Libs\Edition)
 *
 * Usage (repository root, Node 18+, tar on the PATH):
 *   node build/build.js [ref] [ggs|tss]     # ref defaults to HEAD, both editions by default
 */
const crypto = require('crypto');
const fs = require('fs');
const os = require('os');
const path = require('path');
const { execFileSync } = require('child_process');

const SOURCE_NAME  = 'TSSGGSModule';
const SHORT_NAME   = 'tssggs';
const PACKAGE_PATHS = ['modules', 'feeds'];
const EDITION_FILE = 'modules/addons/TSSGGSModule/app/Config/edition.php';
// development tools: never in a package for clients (KEEP_DEV=1 keeps them, for the test box)
const DEV_ONLY = [
    'modules/addons/TSSGGSModule/app/Cron/TestEnvironment.php',
    'modules/addons/TSSGGSModule/app/Cron/TestCommand.php',
    'modules/addons/TSSGGSModule/app/Migration/TestEnvironment.php',
];
const VERSION_FILE = 'modules/addons/TSSGGSModule/app/Config/configuration.yml';
const TEXT_EXT = new Set(['.php', '.js', '.css', '.html', '.tpl', '.yml', '.yaml', '.json', '.sql', '.md', '.txt', '.lock', '.xml', '.svg', '.htaccess', '.ini', '.dist', '']);

const TAR = process.platform === 'win32' ? process.env.SystemRoot + '/System32/tar.exe' : 'tar';
const root = path.resolve(__dirname, '..');
const ref = process.argv[2] || 'HEAD';
const only = process.argv[3] || null;

function fail(message) {
    console.error('ERROR: ' + message);
    process.exit(1);
}

if (!/^[A-Za-z0-9._\/-]+$/.test(ref)) fail('invalid git ref: ' + ref);

const editions = fs.readdirSync(path.join(__dirname, 'editions')).filter((f) => f.endsWith('.json'))
    .map((f) => JSON.parse(fs.readFileSync(path.join(__dirname, 'editions', f), 'utf8')))
    .filter((e) => !only || e.edition === only);
if (!editions.length) fail('no edition to build');

// ---- Export the ref ----
const work = fs.mkdtempSync(path.join(os.tmpdir(), 'tssggs-build-'));
const src = path.join(work, 'src');
fs.mkdirSync(src);
const tarFile = path.join(work, 'source.tar');
execFileSync('git', ['-C', root, 'archive', '--format=tar', '-o', tarFile, ref, '--', ...PACKAGE_PATHS]);
execFileSync(TAR, ['-xf', tarFile, '-C', src]);

if (!process.env.KEEP_DEV) DEV_ONLY.forEach((file) => fs.rmSync(path.join(src, file), { force: true }));
if (!fs.existsSync(path.join(src, EDITION_FILE))) fail(EDITION_FILE + ' not found in ' + ref);
const versionMatch = fs.readFileSync(path.join(src, VERSION_FILE), 'utf8').match(/^version:\s*'([^']+)'/m);
if (!versionMatch) fail('version not found in ' + VERSION_FILE);
const version = versionMatch[1];

function walk(dir, visit) {
    for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
        const full = path.join(dir, entry.name);
        entry.isDirectory() ? walk(full, visit) : visit(full);
    }
}

function isText(file) {
    return TEXT_EXT.has(path.extname(file).toLowerCase());
}

// composer's autoload_static.php stores the length of every PSR-4 prefix - recount them after the rename
function fixAutoloadLengths(content) {
    return content.replace(/'((?:[^'\\]|\\\\)*)' => (\d+),/g, (match, key) => {
        return "'" + key + "' => " + key.replace(/\\\\/g, '\\').length + ',';
    });
}

function build(edition) {
    const dir = path.join(work, edition.edition);
    fs.cpSync(src, dir, { recursive: true });
    const name = edition.folder;

    // file contents
    walk(dir, (file) => {
        if (!isText(file)) return;
        let content = fs.readFileSync(file, 'utf8');
        const original = content;
        content = content.split(SOURCE_NAME).join(name).split(SOURCE_NAME.toUpperCase()).join(name.toUpperCase())
            .split(SHORT_NAME).join(edition.short).split(SHORT_NAME.toUpperCase()).join(edition.short.toUpperCase());
        content = content.replace(/(Composer(?:Autoloader|Static)Init)([0-9a-f]{32})/g, (m, prefix, hash) => prefix + crypto.createHash('md5').update(name + hash).digest('hex'));
        if (file.endsWith(path.join('vendor', 'composer', 'autoload_static.php'))) content = fixAutoloadLengths(content);
        if (content !== original) fs.writeFileSync(file, content);
    });

    // module folders and entry files
    for (const area of ['addons', 'servers']) {
        const from = path.join(dir, 'modules', area, SOURCE_NAME);
        const to = path.join(dir, 'modules', area, name);
        fs.renameSync(from, to);
        const entry = path.join(to, SOURCE_NAME + '.php');
        if (fs.existsSync(entry)) fs.renameSync(entry, path.join(to, name + '.php'));
    }

    // edition identity
    const editionFile = path.join(dir, EDITION_FILE.replace(SOURCE_NAME, name));
    const template = fs.readFileSync(editionFile, 'utf8');
    if (!/'edition'\s*=>\s*'[a-z]+'/.test(template)) fail("'edition' line not found in " + EDITION_FILE);
    fs.writeFileSync(editionFile, template.replace(/'edition'\s*=>\s*'[a-z]+'/, "'edition' => '" + edition.edition + "'"));

    // author WHMCS shows in its module lists: the edition's own company
    if (edition.author) {
        const authorFiles = [
            [path.join('modules', 'addons', name, 'app', 'Config', 'configuration.yml'), /^author:.*$/m, "author: '" + edition.author + "'"],
            [path.join('modules', 'servers', name, 'Configuration.php'), /public \$author = '[^']*';/, "public $author = '" + edition.author + "';"],
        ];
        for (const [rel, pattern, replacement] of authorFiles) {
            const file = path.join(dir, rel);
            const content = fs.readFileSync(file, 'utf8');
            if (!pattern.test(content)) fail('author line not found in ' + rel);
            fs.writeFileSync(file, content.replace(pattern, replacement));
        }
    }

    // inside a double-quoted PHP string a namespace right after a backslash can turn into an escape sequence
    // (\t = tab, \n = newline), which only happens when the folder name starts with one of those letters
    if (/^[nrtvefxu0-7]/.test(name)) {
        const unsafe = new RegExp('"\\\\?ModulesGarden\\\\' + name);
        walk(dir, (file) => {
            if (file.endsWith('.php') && unsafe.test(fs.readFileSync(file, 'utf8'))) fail('unsafe escape in ' + path.relative(dir, file));
        });
    }

    // leftovers of the other edition's working name
    const leftovers = [];
    walk(dir, (file) => {
        if (isText(file) && /tssggs/i.test(fs.readFileSync(file, 'utf8'))) leftovers.push(path.relative(dir, file));
    });
    if (leftovers.length) fail('working name (tssggs) left in ' + leftovers.slice(0, 5).join(', '));

    fs.mkdirSync(path.join(root, 'dist'), { recursive: true });
    const zipFile = path.join(root, 'dist', edition.package + '_' + version + '.zip');
    fs.rmSync(zipFile, { force: true });
    execFileSync(TAR, ['-a', '-c', '-f', zipFile, '-C', dir, ...fs.readdirSync(dir)]);
    console.log(edition.package + ' ' + version + ' (' + edition.edition + ', folder ' + name + ') -> dist/' + path.basename(zipFile));
}

try {
    editions.forEach(build);
} finally {
    fs.rmSync(work, { recursive: true, force: true });
}
