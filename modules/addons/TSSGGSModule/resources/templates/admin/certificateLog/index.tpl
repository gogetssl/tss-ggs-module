{*
    Certificate Log page. The filters reload only the list (#tssggs-log-list) through htmx; without JavaScript the
    form simply reloads the page.
*}
{if $showAbout}
    <span class="tssggs-html tssggs-notes">
        <div class="tssggs-explain">
            <span class="tssggs-explain__icon">{icon name='information-outline'}</span>
            <div>
                <h3>{t key='about_title'}</h3>
                <p>{t key='about_intro'}</p>
                <ul>
                    <li>{t key='about_service'}</li>
                    <li>{t key='about_steps'}</li>
                    <li>{t key='about_types'}</li>
                </ul>
            </div>
        </div>
        <div class="tssggs-note">{$aboutOther nofilter}</div>
    </span>
{/if}

<span class="tssggs-html">
    <form method="get" action="addonmodules.php" class="tssggs-log__filters"
          hx-get="addonmodules.php" hx-target="#tssggs-log-list" hx-push-url="true"
          hx-trigger="submit, change from:find select, input delay:400ms from:find input[name=search]">
        <input type="hidden" name="module" value="{$moduleName}">
        <input type="hidden" name="mg-page" value="certificateLog">
        <input class="form-control" style="width:130px" name="service" type="number" min="1" placeholder="{t key='service_id'}" value="{if $filter.service}{$filter.service}{/if}">
        <select class="form-control" style="width:150px" name="type">
            <option value="">{t key='all_types'}</option>
            {foreach $types as $type}
                <option value="{$type}"{if $filter.type === $type} selected{/if}>{t key="type_$type"}</option>
            {/foreach}
        </select>
        <input class="form-control" style="width:260px" name="search" placeholder="{t key='search'}" value="{$filter.search}">
        <button class="lu-btn lu-btn--primary" type="submit">{t key='filter'}</button>
        <span style="flex:1"></span>
        {foreach ['30', 'all'] as $older}
            <button class="lu-btn lu-btn--default" type="button"
                    hx-post="{url action='clear'}" hx-vals='{ "older": "{$older}" }' hx-target="#tssggs-log-list"
                    hx-confirm="{t key="confirm_clear_$older"}">{t key="clear_$older"}</button>
        {/foreach}
    </form>

    <div id="tssggs-log-list">
        {include file='admin/certificateLog/list.tpl'}
    </div>
</span>
