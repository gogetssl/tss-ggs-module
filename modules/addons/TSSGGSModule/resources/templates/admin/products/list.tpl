{*
    The products list as every change puts it back into #tssggs-products: the message of the last action, the two
    filters (imported or not, validation level; each button shows how many products it would list), the table.
*}
{include file='admin/partials/flash.tpl'}

<span class="tssggs-html">
    <div class="tssggs-filters">
        {foreach $filters as $filter}
            <div class="tssggs-show" role="group" aria-label="{$filter.label}">
                {foreach $filter.choices as $choice}
                    <a class="lu-btn lu-btn--sm {if $choice.current}lu-btn--primary{else}lu-btn--default lu-btn--outline{/if}" href="{$choice.url}"
                       hx-get="{$choice.url}" hx-target="#tssggs-products" hx-swap="innerHTML" hx-push-url="true"{if $choice.current} aria-current="page"{/if}>{$choice.text} <b>{$choice.count}</b></a>
                {/foreach}
            </div>
        {/foreach}
    </div>
</span>

{include file='admin/partials/table.tpl'}
