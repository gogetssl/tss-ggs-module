<?php

/**
 * Distribution edition of the module.
 *
 * The code base is the same for both distributions; only the name shown in WHMCS
 * (addon list, server module list, menus, email template names) differs.
 *
 *   'ggs' - GoGetSSL Module    (distributed by GoGetSSL)
 *   'tss' - TheSSLStore Plugin (distributed by TheSSLStore)
 *
 * The release build (build/build.js) writes this value for each package; the development install runs the TSS edition.
 */
return [
    'edition' => 'tss',
];
