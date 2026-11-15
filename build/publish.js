#!/usr/bin/env node
/**
 * Publishes a signed release (build/release.js) as a GitHub Release in both distribution repositories: the edition's
 * ZIP, release.json and release.json.sig as assets, the release notes as text. The tag must already be pushed there.
 *
 *   node build/publish.js vX.Y.Z [ggs|tss]
 *
 * Token: ~/.tssggs-github/<owner>.token (one fine-grained token per owner: "thestores", "gogetssl", with
 * Contents: read and write on the repository) or ~/.tssggs-github/token (one classic token). The token is read from
 * the file only - never printed or passed on a command line.
 */
const fs = require('fs');
const os = require('os');
const path = require('path');

const REPOS = { tss: 'thestores/whmcs', ggs: 'gogetssl/whmcs-module' };
const root = path.resolve(__dirname, '..');

function fail(message) {
    console.error('ERROR: ' + message);
    process.exit(1);
}

function token(owner) {
    const dir = path.join(os.homedir(), '.tssggs-github');
    for (const file of [path.join(dir, owner + '.token'), path.join(dir, 'token')]) {
        if (fs.existsSync(file)) return fs.readFileSync(file, 'utf8').trim();
    }
    fail('no GitHub token for ' + owner + ' (' + path.join(dir, owner + '.token') + ')');
}

async function api(method, url, auth, body, contentType) {
    const response = await fetch(url, {
        method,
        headers: {
            Authorization: 'Bearer ' + auth,
            Accept: 'application/vnd.github+json',
            'X-GitHub-Api-Version': '2022-11-28',
            'User-Agent': 'tssggs-release',
            ...(contentType ? { 'Content-Type': contentType } : {}),
        },
        body,
    });
    const text = await response.text();
    return { status: response.status, data: text ? JSON.parse(text) : null };
}

(async () => {
    const tag = process.argv[2] || '';
    if (!/^v\d+\.\d+\.\d+$/.test(tag)) fail('Usage: node build/publish.js vX.Y.Z [ggs|tss]');
    const version = tag.slice(1);
    const dir = path.join(root, 'dist', version);
    const manifest = JSON.parse(fs.readFileSync(path.join(dir, 'release.json'), 'utf8'));
    if (manifest.version !== version || !fs.existsSync(path.join(dir, 'release.json.sig'))) fail('dist/' + version + ' is not a signed release of ' + version);

    for (const [edition, repo] of Object.entries(REPOS)) {
        if (process.argv[3] && process.argv[3] !== edition) continue;
        const auth = token(repo.split('/')[0]);
        const asset = manifest.editions[edition].asset;

        let release = await api('GET', 'https://api.github.com/repos/' + repo + '/releases/tags/' + tag, auth);
        if (release.status === 404) {
            release = await api('POST', 'https://api.github.com/repos/' + repo + '/releases', auth,
                JSON.stringify({ tag_name: tag, name: version, body: manifest.notes, make_latest: 'true' }), 'application/json');
        }
        if (release.status !== 200 && release.status !== 201) fail(repo + ': release ' + tag + ': HTTP ' + release.status + ' ' + JSON.stringify(release.data));

        const existing = new Set((release.data.assets || []).map((a) => a.name));
        for (const name of [asset, 'release.json', 'release.json.sig']) {
            if (existing.has(name)) {
                console.log(repo + ': ' + name + ' already attached');
                continue;
            }
            const upload = await api('POST', 'https://uploads.github.com/repos/' + repo + '/releases/' + release.data.id + '/assets?name=' + encodeURIComponent(name), auth,
                fs.readFileSync(path.join(dir, name)), name.endsWith('.zip') ? 'application/zip' : 'application/octet-stream');
            if (upload.status !== 201) fail(repo + ': upload ' + name + ': HTTP ' + upload.status);
            console.log(repo + ': ' + name + ' attached');
        }
        console.log(repo + ': ' + release.data.html_url);
    }
})();
