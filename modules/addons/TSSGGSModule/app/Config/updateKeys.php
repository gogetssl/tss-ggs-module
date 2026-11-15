<?php

/**
 * Public keys that may sign module releases (App\Libs\Update\Release). Ed25519, base64 of the 32-byte public key,
 * keyed by key id. Made with `node build/keygen.js` - the private halves never leave the release signer.
 *
 * - 'primary': signs every release
 * - 'reserve': kept offline; takes over when the primary is lost or stolen (the primary is then revoked by a release
 *   signed with the reserve: release.json "revoked_keys")
 *
 * No key here = the module only tells admins about new releases (with the download link); it never installs one.
 *
 * Test installs can trust an extra key by defining, in WHMCS configuration.php:
 *   define('TSSGGS_UPDATE_TEST_KEY', '<base64 public key>');
 */
return [
    // 'primary' => '',
    // 'reserve' => '',
];
