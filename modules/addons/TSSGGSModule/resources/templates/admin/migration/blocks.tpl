{*
    The blocks of the Migration page ($blocks from App\Libs\Setup\MigrationScreen): a run (run.tpl), a legacy module
    (source.tpl), "nothing found", and in the wizard ($wizard) "done, continue" / "later".
    $error: what the last action said when it failed. Texts: admin.migration.*
*}
{if $error !== ''}
    <span class="tssggs-html"><div class="tssggs-note is-bad">{$error}</div></span>
{/if}
{foreach $blocks as $block}
    {if $block.kind === 'run'}
        {include file='admin/migration/run.tpl' run=$block}
    {elseif $block.kind === 'source'}
        {include file='admin/migration/source.tpl' src=$block}
    {elseif $block.kind === 'none'}
        <div class="lu-widget">
            <div class="lu-widget__header">
                <div class="lu-widget__top lu-top">
                    <span class="lu-widget__icon">{icon name='database-outline'}</span>
                    <div class="lu-top__title"> {t key='admin.migration.title'} </div>
                </div>
            </div>
            <div class="lu-widget__body"><div class="lu-widget__content"><span class="tssggs-html"><p class="tssggs-muted">{t key='admin.migration.none_found'}</p></span></div></div>
        </div>
    {elseif $block.kind === 'done'}
        <span class="tssggs-html">
            <div class="tssggs-note is-ok">{t key='admin.migration.wizard_done'}</div>
            <div class="tssggs-actions"><a class="lu-btn lu-btn--success" href="{$block.next}">{t key='admin.migration.wizard_continue'}</a></div>
        </span>
    {elseif $block.kind === 'later'}
        <span class="tssggs-html"><p class="tssggs-muted">{t key='admin.migration.wizard_later_hint'} <a href="{$block.next}">{t key='admin.migration.wizard_later'}</a></p></span>
    {/if}
{/foreach}
<script src="{$assets}/assets/js/pages/migration.js?v={$version}"></script>
