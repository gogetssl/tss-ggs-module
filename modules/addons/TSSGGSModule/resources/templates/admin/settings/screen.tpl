{*
    One settings screen ($screen): its title, then one form with the sections of its own template
    (admin/settings/<template>.tpl) and the sticky Save bar. Saving puts this whole block back into
    #tssggs-settings-screen with the result on top. The Save bar (enabled once something changed, "unsaved changes",
    the question before leaving) is resources/assets/js/core/savebar.js.
    In a screen's template: $f is the group of its texts, $data its values, $options the choices of its selects.
*}
<div class="lu-widget {$css}">
    <div class="lu-widget__header">
        <div class="lu-widget__top lu-top lu-top--sub">
            <span class="lu-widget__icon">{icon name=$icon}</span>
            <div class="lu-top__title"> {t key="{$w}.title"} </div>
            <div class="lu-top__subtitle">{t key="{$w}.subtitle"}</div>
        </div>
    </div>
    <div class="lu-widget__body">
        <div class="lu-widget__content lu-single-element">
            {* a plain screen (no provider): no form and no Save bar, only its template *}
            {if $plain}
                <div class="lu-w-100">
                    {if $flash}<div class="tssggs-note is-{$flash.tone}" role="status">{$flash.text nofilter}</div>{/if}
                    {include file="admin/settings/{$template}.tpl"}
                </div>
            {else}
            <form method="post" action="{$saveUrl}" class="lu-w-100" data-saved="{if $saved}1{else}0{/if}"
                  hx-post="{$saveUrl}" hx-target="#tssggs-settings-screen" hx-swap="innerHTML">
                <input type="hidden" name="token" value="{$token}">
                {if $flash}
                    <div class="tssggs-note is-{$flash.tone} tssggs-settings__result" role="status">{$flash.text nofilter}</div>
                {/if}
                <div>
                    {include file="admin/settings/{$template}.tpl"}
                </div>
                <div class="lu-top__toolbar tssggs-savebar">
                    <button type="submit" class="lu-btn lu-btn--success"><span class="lu-btn__text">{t key='admin.savebar.saveChanges'}</span></button>
                    {* Test Connection: only its result comes back (into the screen's own box), the form stays as typed *}
                    {if $testUrl !== ''}
                        <button type="submit" class="lu-btn lu-btn--primary" formaction="{$testUrl}" data-test
                                hx-post="{$testUrl}" hx-target="#tssggs-settings-test" hx-swap="innerHTML show:bottom"><span class="lu-btn__text">{t key="{$f}.testConnection"}</span></button>
                    {/if}
                    <span>
                        <span class="tssggs-savebar__status" role="status" aria-live="polite"{if $always} data-always="1"{/if} data-clean="{t key='admin.savebar.noUnsaved'}" data-dirty="{t key='admin.savebar.unsaved'}"
                              data-failed="{t key='admin.savebar.notSaved'}" data-confirm="{t key='admin.savebar.leaveConfirm'}">{t key='admin.savebar.noUnsaved'}</span>
                    </span>
                </div>
            </form>
            {/if}
        </div>
    </div>
</div>
