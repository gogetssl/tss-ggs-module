#!/usr/bin/env node
/**
 * Makes and signs release.json for a built release (after `node build/build.js vX.Y.Z`).
 *
 *   node build/release.js vX.Y.Z [--key primary] [--manual-only] [--not-rollback-safe] [--revoke <key-id>]
 *
 * release.json (one file for both editions, so a release is signed once):
 *   format, version, released, notes (the top entry of release_notes.txt), min_php, min_whmcs (build/release-config.json),
 *   rollback_safe (false = the release changes the database in a way older code can not work with: no rollback),
 *   manual_only (true = the module may not install it by itself), revoked_keys,
 *   editions.<ggs|tss>: folder, asset (ZIP name), size, sha256, files (path -> SHA-256 of every file in the ZIP)
 * release.json.sig: {"key": "<key id>", "sig": "<base64 Ed25519 signature of release.json's exact bytes>"}
 *
 * The key is ~/.tssggs-signing/<key id>.pem (build/keygen.js); its passphrase is asked on the terminal.
 * Output: dist/<version>/release.json and release.json.sig, next to copies of the two ZIPs - the files a release
 * publishes (build/publish.js) and the CDN fallback gets.
 */
const crypto = require('crypto');
const fs = require('fs');
const os = require('os');
const path = require('path');
const { execFileSync } = require('child_process');
const { askHidden } = require('./lib/ask');

const TAR = process.platform === 'win32' ? process.env.SystemRoot + '/System32/tar.exe' : 'tar';
const root = path.resolve(__dirname, '..');

function fail(message) {
    console.error('ERROR: ' + message);
    process.exit(1);
}

function option(name) {
    const i = process.argv.indexOf(name);
    return i > 0 ? process.argv[i + 1] : null;
}

function walk(dir, visit) {
    for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
        const full = path.join(dir, entry.name);
        entry.isDirectory() ? walk(full, visit) : visit(full);
    }
}

(async () => {
    const tag = process.argv[2] || '';
    if (!/^v\d+\.\d+\.\d+$/.test(tag)) fail('Usage: node build/release.js vX.Y.Z [--key primary]');
    const version = tag.slice(1);
    const keyId = option('--key') || 'primary';
    const config = JSON.parse(fs.readFileSync(path.join(__dirname, 'release-config.json'), 'utf8'));

    // the release notes of this version: the top entry of release_notes.txt, if it is this version's
    const notesFile = fs.readFileSync(path.join(root, 'release_notes.txt'), 'utf8').replace(/\r\n/g, '\n');
    const top = notesFile.split(/\n(?=\d+\.\d+\.\d+ \()/)[0].trim();
    if (!top.startsWith(version + ' (')) fail('release_notes.txt does not start with ' + version);

    const manifest = {
        format: 1,
        version,
        released: new Date().toISOString().replace(/\.\d+Z$/, 'Z'),
        notes: top,
        min_php: config.min_php || '',
        min_whmcs: config.min_whmcs || '',
        rollback_safe: !process.argv.includes('--not-rollback-safe'),
        manual_only: process.argv.includes('--manual-only'),
        revoked_keys: option('--revoke') ? [option('--revoke')] : [],
        editions: {},
    };

    const out = path.join(root, 'dist', version);
    fs.rmSync(out, { recursive: true, force: true });
    fs.mkdirSync(out, { recursive: true });

    for (const file of fs.readdirSync(path.join(__dirname, 'editions')).filter((f) => f.endsWith('.json'))) {
        const edition = JSON.parse(fs.readFileSync(path.join(__dirname, 'editions', file), 'utf8'));
        const asset = edition.package + '_' + version + '.zip';
        const zip = path.join(root, 'dist', asset);
        if (!fs.existsSync(zip)) fail(asset + ' not built (node build/build.js ' + tag + ')');
        const data = fs.readFileSync(zip);

        // every file of the ZIP, as the module will unpack it
        const work = fs.mkdtempSync(path.join(os.tmpdir(), 'tssggs-release-'));
        try {
            execFileSync(TAR, ['-xf', zip, '-C', work]);
            const files = {};
            walk(work, (f) => {
                const rel = path.relative(work, f).split(path.sep).join('/');
                if (!rel.startsWith('modules/addons/' + edition.folder + '/') && !rel.startsWith('modules/servers/' + edition.folder + '/')) {
                    fail(asset + ' holds a file outside the module folders: ' + rel);
                }
                if (/(^|\/)\.\.?(\/|$)/.test(rel) || /[\\\0]/.test(rel)) fail('unsafe path in ' + asset + ': ' + rel);
                if (/^modules\/addons\/[^/]+\/app\/Cron\/Test(Environment|Command)\.php$/.test(rel)) fail(asset + ' holds development files (built with KEEP_DEV?)');
                files[rel] = crypto.createHash('sha256').update(fs.readFileSync(f)).digest('hex');
            });
            manifest.editions[edition.edition] = {
                folder: edition.folder,
                asset,
                size: data.length,
                sha256: crypto.createHash('sha256').update(data).digest('hex'),
                files,
            };
            console.log(edition.edition + ': ' + asset + ', ' + Object.keys(files).length + ' files');
        } finally {
            fs.rmSync(work, { recursive: true, force: true });
        }
        fs.copyFileSync(zip, path.join(out, asset));
    }

    const body = Buffer.from(JSON.stringify(manifest));
    fs.writeFileSync(path.join(out, 'release.json'), body);

    // signing
    const keyFile = path.join(os.homedir(), '.tssggs-signing', keyId + '.pem');
    if (!fs.existsSync(keyFile)) fail('signing key ' + keyFile + ' not found (node build/keygen.js ' + keyId + ')');
    const pem = fs.readFileSync(keyFile, 'utf8');
    const encrypted = pem.includes('ENCRYPTED');
    const passphrase = encrypted ? await askHidden('Passphrase of the "' + keyId + '" signing key: ') : undefined;
    let signature;
    try {
        signature = crypto.sign(null, body, { key: pem, format: 'pem', passphrase });
    } catch (e) {
        fail('signing failed (wrong passphrase?)');
    }
    // checked right away with the public half, as the module will check it
    const publicKey = crypto.createPublicKey({ key: pem, format: 'pem', passphrase });
    if (!crypto.verify(null, body, publicKey, signature)) fail('the signature does not verify');
    fs.writeFileSync(path.join(out, 'release.json.sig'), JSON.stringify({ key: keyId, sig: signature.toString('base64') }));

    console.log('Signed with "' + keyId + '"' + (encrypted ? '' : ' (TEST key)') + ': dist/' + version + '/release.json, release.json.sig');
})();
