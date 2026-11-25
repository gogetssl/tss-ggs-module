#!/bin/bash
# Quality checks of the module source, run on a Linux host with PHP 8.2+ (the developer machine needs no PHP).
#
#   tools/qa.sh unit     PHPUnit unit tests (tests/Unit, no WHMCS needed)
#   tools/qa.sh stan     PHPStan static analysis (phpstan.neon.dist, known issues in phpstan-baseline.neon)
#   tools/qa.sh cs       code style check, nothing is changed (.php-cs-fixer.dist.php)
#   tools/qa.sh smoke    smoke tests inside a WHMCS install where this code is deployed (tests/Smoke)
#   tools/qa.sh all      unit + stan + cs
#
# Settings (environment):
#   QA_HOST     user@host to run on (required)
#   QA_SSH      ssh command, default "ssh"; QA_SCP scp command, default "scp"
#   QA_TOOLS    folder with phpunit.phar, phpstan.phar, php-cs-fixer.phar on the host, default ~/tools
#   QA_DIR      work folder on the host, default ~/qa
#   QA_WHMCS    WHMCS root on the host (smoke only), QA_MODULE deployed module folder (smoke only, e.g. TSSPlugin)
set -euo pipefail
: "${QA_HOST:?set QA_HOST=user@host}"
SSH=${QA_SSH:-ssh}; SCP=${QA_SCP:-scp}; TOOLS=${QA_TOOLS:-'~/tools'}; DIR=${QA_DIR:-'~/qa'}
ROOT=$(cd "$(dirname "$0")/.." && pwd)
what=${1:-all}

upload() {
    # the working tree as it is (uncommitted changes included), without build output and VCS data
    local tgz; tgz=$(mktemp -t qa-XXXX).tgz
    tar -C "$ROOT" --exclude=.git --exclude=dist --exclude=.phpunit.cache -czf "$tgz" modules tests tools phpunit.xml.dist phpstan.neon.dist phpstan-baseline.neon .php-cs-fixer.dist.php 2>/dev/null \
        || tar -C "$ROOT" --exclude=.git --exclude=dist -czf "$tgz" modules tests tools phpunit.xml.dist
    local src="$tgz"
    command -v cygpath >/dev/null 2>&1 && src=$(cygpath -w "$tgz")   # Windows OpenSSH scp needs a Windows path
    $SCP "$src" "$QA_HOST:/tmp/qa-src.tgz"
    rm -f "$tgz"
    $SSH "$QA_HOST" "rm -rf $DIR/src && mkdir -p $DIR/src && tar -xzf /tmp/qa-src.tgz -C $DIR/src && rm -f /tmp/qa-src.tgz"
}

run() { $SSH "$QA_HOST" "cd $DIR/src && $1"; }

unit()  { run "php $TOOLS/phpunit.phar -c phpunit.xml.dist"; }
stan()  {
    : "${QA_WHMCS:?set QA_WHMCS to the WHMCS root (PHPStan reads its Illuminate / Symfony libraries)}"
    run "QA_WHMCS=$QA_WHMCS php -d memory_limit=2G $TOOLS/phpstan.phar analyse -c phpstan.neon.dist --no-progress --error-format=table ${STAN_ARGS:-}"
}
cs()    { run "php $TOOLS/php-cs-fixer.phar check --config=.php-cs-fixer.dist.php --diff --show-progress=none"; }
smoke() {
    : "${QA_WHMCS:?set QA_WHMCS to the WHMCS root}"; : "${QA_MODULE:?set QA_MODULE to the deployed module folder}"
    # each smoke test is a module console command: copied into the deployed module (namespace of its folder), run, removed
    for f in "$ROOT"/tests/Smoke/*.php; do
        local name; name=$(basename "$f" .php)
        sed "s/__MODULE__/$QA_MODULE/g" "$f" | $SSH "$QA_HOST" "cat > $QA_WHMCS/modules/addons/$QA_MODULE/app/Cron/$name.php"
        $SSH "$QA_HOST" "cd $QA_WHMCS/modules/addons/$QA_MODULE && php -q cron/cron.php $name; rc=\$?; rm -f app/Cron/$name.php; exit \$rc"
    done
}

case "$what" in
    unit|stan|cs) upload; $what ;;
    smoke) smoke ;;
    all) upload; status=0; unit || status=1; stan || status=1; cs || status=1; exit $status ;;
    *) echo "usage: tools/qa.sh unit|stan|cs|smoke|all"; exit 2 ;;
esac
