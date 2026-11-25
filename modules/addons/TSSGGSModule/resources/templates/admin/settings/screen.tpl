{*
    One settings screen ($screen): its title, then one form with the sections of its own template
    (admin/settings/<template>.tpl) and the sticky Save bar. Saving puts this whole block back into
    #tssggs-settings-screen with the result on top. The Save bar (enabled once something changed, "unsaved changes",
    the question before leaving) is resources/assets/js/core/savebar.js.
    In a screen's template: $f is the group of its texts, $data its values, $options the choices of its selects.
*}
<div class="mod-box {$css}">
    <div class="mod-box-head">
        <div class="mod-box-top mod-bar mod-bar--sub">
            <span class="mod-box-icon">{icon name=$icon}</span>
            <div class="mod-bar-title"> {t key="{$w}.title"} </div>
            <div class="mod-bar-sub">{t key="{$w}.subtitle"}</div>
        </div>
    </div>
    <div class="mod-box-body">
        <div class="mod-box-content mod-solo">
            {* a plain screen (no provider): no form and no Save bar, only its template *}
            {if $plain}
                <div class="mod-w-full">
                    {if $flash}<div class="tssggs-note is-{$flash.tone}" role="status">{$flash.text nofilter}</div>{/if}
                    {include file="admin/settings/{$template}.tpl"}
                </div>
            {else}
            <form method="post" action="{$saveUrl}" class="mod-w-full" data-saved="{if $saved}1{else}0{/if}"
                  hx-post="{$saveUrl}" hx-target="#tssggs-settings-screen" hx-swap="innerHTML">
                <input type="hidden" name="token" value="{$token}">
                {if $flash}
                    <div class="tssggs-note is-{$flash.tone} tssggs-settings__result" role="status">{$flash.text nofilter}</div>
                {/if}
                {* where Test Connection puts its result *}
                {if $testUrl !== ''}<div id="tssggs-settings-test"></div>{/if}
                <div>
                    {include file="admin/settings/{$template}.tpl"}
                </div>
                <div class="mod-bar-tools tssggs-savebar">
                    <button type="submit" class="mod-btn mod-btn--success"><span class="mod-btn-text">{t key='admin.savebar.saveChanges'}</span></button>
                    {* Test Connection: only its result comes back (into the screen's own box), the form stays as typed *}
                    {if $testUrl !== ''}
                        <button type="submit" class="mod-btn mod-btn--primary" formaction="{$testUrl}" data-test
                                hx-post="{$testUrl}" hx-target="#tssggs-settings-test" hx-swap="innerHTML"><span class="mod-btn-text">{t key="{$f}.testConnection"}</span></button>
                    {/if}
                    <span>
                        <span class="tssggs-savebar__status" role="status" aria-live="polite"{if $always} data-always="1"{/if} data-clean="{t key='admin.savebar.noUnsaved'}" data-dirty="{t key='admin.savebar.unsaved'}"
                              data-failed="{t key='admin.savebar.notSaved'}" data-confirm="{t key='admin.savebar.leaveConfirm'}">{if !$always}{t key='admin.savebar.noUnsaved'}{/if}</span>
                    </span>
                </div>
            </form>
            {/if}
        </div>
    </div>
</div>
