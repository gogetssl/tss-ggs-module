<?php

/**
 * Code style check of the module's own code (tools/qa.sh cs). The rules keep the house style (braces on their own
 * line, "if(" without a space) and only enforce what is safe and mechanical: no trailing whitespace, one newline at the
 * end, LF line endings, no unused or duplicate imports, short arrays, no closing PHP tag.
 */
$finder = PhpCsFixer\Finder::create()
    ->in([__DIR__ . '/modules/addons/TSSGGSModule/app', __DIR__ . '/modules/servers/TSSGGSModule/app', __DIR__ . '/tests'])
    ->name('*.php');

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(false)
    ->setUsingCache(false)
    ->setRules([
        'encoding'                     => true,
        'full_opening_tag'             => true,
        'no_closing_tag'               => true,
        'no_trailing_whitespace'       => true,
        'no_whitespace_in_blank_line'  => true,
        'single_blank_line_at_eof'     => true,
        'line_ending'                  => true,
        'no_unused_imports'            => true,
        'single_import_per_statement'  => true,
        'no_leading_import_slash'      => true,
        'array_syntax'                 => ['syntax' => 'short'],
        'no_empty_statement'           => true,
        'lowercase_keywords'           => true,
        'constant_case'                => ['case' => 'lower'],
    ])
    ->setFinder($finder);
