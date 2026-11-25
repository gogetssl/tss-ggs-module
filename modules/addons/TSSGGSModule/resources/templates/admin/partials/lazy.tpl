{*
    A part of a page that is slow to build (it calls the vendor API, counts a lot of rows): the page shows a spinner
    here and loads the part right after it is shown.
        {include file='admin/partials/lazy.tpl' url={url action='balance'}}
    The action answers with a fragment, which replaces this placeholder.
*}
<div class="tssggs-lazy" hx-get="{$url}" hx-trigger="load" hx-swap="outerHTML">
    <div class="lu-preloader-container"><div class="lu-preloader lu-preloader--sm"></div></div>
</div>
