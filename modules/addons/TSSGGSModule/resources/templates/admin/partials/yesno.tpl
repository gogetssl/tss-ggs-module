{*
    A Yes / No switch of a form: {include file='admin/partials/yesno.tpl' name='generateInvoice' label={t key=...} on=false}  ($label: escaped HTML)
    The form posts <name>=on or <name>=off. $danger=true shows the chosen "No" in red.
*}
<div class="tssggs-yn mod-solo">
    {if $label|default:'' !== ''}<span class="mod-check-text"> {$label nofilter} </span>{/if}
    <div role="radiogroup" class="tssggs-yn__segments">
        <label class="tssggs-yn__segment">
            <input type="radio" name="{$name}" value="on" class="tssggs-yn__radio no-icheck"{if $on} checked{/if}{if $disabled|default:false} disabled{/if}>
            <span>{t key='admin.kernel.yes'}</span>
        </label>
        <label class="tssggs-yn__segment">
            <input type="radio" name="{$name}" value="off" class="tssggs-yn__radio{if $danger|default:true} tssggs-yn__radio--danger{/if} no-icheck"{if !$on} checked{/if}{if $disabled|default:false} disabled{/if}>
            <span>{t key='admin.kernel.no'}</span>
        </label>
    </div>
</div>
