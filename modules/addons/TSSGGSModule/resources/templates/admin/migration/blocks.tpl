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
        <div class="mod-box">
            <div class="mod-box-head">
                <div class="mod-box-top mod-bar">
                    <span class="mod-box-icon">{icon name='database-outline'}</span>
                    <div class="mod-bar-title"> {t key='admin.migration.title'} </div>
                </div>
            </div>
            <div class="mod-box-body"><div class="mod-box-content"><span class="tssggs-html"><p class="tssggs-muted">{t key='admin.migration.none_found'}</p></span></div></div>
        </div>
    {elseif $block.kind === 'done'}
        <span class="tssggs-html">
            <div class="tssggs-note is-ok">{t key='admin.migration.wizard_done'}</div>
            <div class="tssggs-actions"><a class="mod-btn mod-btn--success" href="{$block.next}">{t key='admin.migration.wizard_continue'}</a></div>
        </span>
    {elseif $block.kind === 'later'}
        <span class="tssggs-html"><p class="tssggs-muted">{t key='admin.migration.wizard_later_hint'} <a href="{$block.next}">{t key='admin.migration.wizard_later'}</a></p></span>
    {/if}
{/foreach}
<script src="{$assets}/assets/js/pages/migration.js?v={$version}"></script>
