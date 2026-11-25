{*
    A part of a page that is slow to build (it calls the vendor API, counts a lot of rows): the page shows a spinner
    here and loads the part right after it is shown.
        {include file='admin/partials/lazy.tpl' url={url action='balance'}}
    $url comes from {url ...}, which escapes it already. The action answers with a fragment, which replaces this
    placeholder. $when (default "load"): "intersect once" loads the part when it becomes visible (a hidden tab);
    $swap (default "outerHTML"): "innerHTML" keeps the placeholder as the part's container ($id names it).
*}
<div class="tssggs-lazy"{if $id|default:'' !== ''} id="{$id}"{/if} hx-get="{$url nofilter}" hx-trigger="{$when|default:'load'}" hx-swap="{$swap|default:'outerHTML'}">
    <div class="mod-spinner-box"><div class="mod-spinner mod-spinner--sm"></div></div>
</div>
