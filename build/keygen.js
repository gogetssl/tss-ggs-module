#!/usr/bin/env node
/**
 * Creates an Ed25519 key pair for signing module releases.
 *
 *   node build/keygen.js primary            asks for a passphrase (twice), writes ~/.tssggs-signing/primary.pem
 *   node build/keygen.js reserve            the same for the reserve key (keep it offline, e.g. on a USB stick)
 *   node build/keygen.js test --test        a TEST key without passphrase (test installs only, never in updateKeys.php)
 *
 * The private key is written encrypted (PKCS#8, AES-256) and readable by its owner only. The public key is printed:
 * it goes into modules/addons/TSSGGSModule/app/Config/updateKeys.php under the same key id.
 * Losing the private key means the next release must be signed with the reserve key; leaking it means it must be
 * revoked (release.json "revoked_keys") by a release signed with the reserve key.
 */
const crypto = require('crypto');
const fs = require('fs');
const os = require('os');
const path = require('path');
const { askHidden } = require('./lib/ask');

(async () => {
    const id = process.argv[2];
    const test = process.argv.includes('--test');
    if (!id || !/^[a-z0-9-]+$/.test(id)) {
        console.error('Usage: node build/keygen.js <key-id> [--test]');
        process.exit(1);
    }
    const dir = path.join(os.homedir(), '.tssggs-signing');
    const file = path.join(dir, id + '.pem');
    if (fs.existsSync(file)) {
        console.error('ERROR: ' + file + ' exists already - a key is never overwritten');
        process.exit(1);
    }

    let passphrase = '';
    if (!test) {
        passphrase = await askHidden('Passphrase for the "' + id + '" key (at least 12 characters): ');
        if (passphrase.length < 12) {
            console.error('ERROR: passphrase too short');
            process.exit(1);
        }
        if ((await askHidden('Repeat the passphrase: ')) !== passphrase) {
            console.error('ERROR: the passphrases differ');
            process.exit(1);
        }
    }

    const { publicKey, privateKey } = crypto.generateKeyPairSync('ed25519', {
        publicKeyEncoding: { type: 'spki', format: 'der' },
        privateKeyEncoding: test ? { type: 'pkcs8', format: 'pem' } : { type: 'pkcs8', format: 'pem', cipher: 'aes-256-cbc', passphrase },
    });
    fs.mkdirSync(dir, { recursive: true, mode: 0o700 });
    fs.writeFileSync(file, privateKey, { mode: 0o600, flag: 'wx' });

    const raw = publicKey.subarray(publicKey.length - 32).toString('base64');
    console.log('Private key: ' + file + (test ? ' (TEST key, no passphrase)' : ' (encrypted)'));
    console.log('Public key (' + id + '): ' + raw);
    if (!test) {
        console.log("Add to app/Config/updateKeys.php:  '" + id + "' => '" + raw + "',");
        console.log('Make a backup copy of the private key file now; without it (or the reserve key) no release can be signed.');
    }
})();
