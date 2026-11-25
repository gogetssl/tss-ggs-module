{*
    A list with search, sorting and paging ($table from App\Kernel\Table::run). The cells of a row come from the
    page's own template ($table.rowTemplate, variable $row). Search, a click on a column title, a page or a length
    reloads only this block (htmx; the request carries table=<id>).
    A button inside a row posts with hx-post; the enclosing form sends the current search, sorting and page along.
    Give the button its own hx-target AND hx-swap (it would inherit hx-swap="outerHTML" from the form).
*}
<div id="tssggs-table-{$table.id}" class="mod-box"
     x-data="{ ticked: 0, go(values) { var refs = this.$refs; Object.keys(values).forEach(function (name) { refs[name].value = values[name] }); refs.form.requestSubmit() }, count() { this.ticked = this.$root.querySelectorAll('[data-row-check]:checked').length }, tickAll(on) { this.$root.querySelectorAll('[data-row-check]:not(:disabled)').forEach(function (box) { box.checked = on }); this.count() } }">
    <div class="mod-box-body">
        <form method="get" action="addonmodules.php" x-ref="form" class="mod-list mod-list-form"
              hx-get="addonmodules.php" hx-target="#tssggs-table-{$table.id}" hx-swap="outerHTML"
              hx-trigger="submit, input delay:400ms from:find input[name=tq]">
            <input type="hidden" name="module" value="{$moduleName}">
            <input type="hidden" name="mod-page" value="{$pageName}">
            <input type="hidden" name="table" value="{$table.id}">
            <input type="hidden" name="ts" value="{$table.sort}" x-ref="ts">
            <input type="hidden" name="td" value="{$table.dir}" x-ref="td">
            <input type="hidden" name="tp" value="{$table.page}" x-ref="tp">
            <input type="hidden" name="tl" value="{$table.length}" x-ref="tl">
            {foreach $table.keep as $keepName => $keepValue}
                <input type="hidden" name="{$keepName}" value="{$keepValue}">
            {/foreach}

            {if $table.searchable}
                <div class="mod-list-top mod-bar">
                    <div class="mod-bar-tools">
                        <div class="mod-bar-search mod-inputbox">
                            <span class="mod-ico-sm mod-inputbox-icon"><span class="tssggs-icobox">{icon name='magnify'}</span></span>
                            <input class="mod-input mod-inputbox-input mod-table-search" name="tq" x-ref="tq" value="{$table.search}"
                                   placeholder="{t key='admin.kernel.search'}" autocomplete="off" @input="$refs.tp.value = 1">
                            <button type="button" class="mod-btn mod-btn--link mod-pad-r0 mod-pad-l0 mod-gap-r mod-search-clear"
                                    {if $table.search === ''}disabled{/if} @click="go({ tq: '', tp: 1 })">
                                <span class="mod-ico-16 tssggs-icobox">{icon name='close'}</span>
                            </button>
                        </div>
                    </div>
                    {* buttons on the right of the search (Table::toolbar) *}
                    {if $table.toolbar !== ''}
                        <div class="mod-bar-tools mod-solo">{include file="{$table.toolbar}.tpl"}</div>
                    {/if}
                </div>
            {/if}

            {* rows can be ticked (Table::selectable): the number of ticked rows and what can be done with them *}
            {if $table.mass !== ''}
                <div class="mod-list-mass mod-bar" :class="ticked && 'is-open'">
                    <div class="mod-bar-title"><span class="mod-count mod-count--primary mod-value" x-text="ticked"></span> {t key='admin.kernel.selected'}</div>
                    <div class="mod-bar-tools mod-solo">{include file="{$table.mass}.tpl"}</div>
                </div>
            {/if}

            <div class="mod-list-scroll">
                <div class="mod-grid">
                    <table class="mod-table mod-table--stack mod-table--list">
                        <thead>
                            <tr role="row">
                                {foreach $table.columns as $column}
                                    <th class="{$column.class}">
                                        {if $column@first && $table.mass !== ''}
                                            <div class="mod-line">
                                                <div class="mod-check">
                                                    <label>
                                                        <input class="mod-check-input" type="checkbox" @change="tickAll($el.checked)">
                                                        <span class="mod-check-box"></span>
                                                    </label>
                                                </div>
                                                <span class="mod-table-title{if $column.sortable} mod-sortable{/if}" {if $column.sortable}@click="go({ ts: '{$column.name}', td: '{$column.nextDir}', tp: 1 })"{/if}>{$column.title}</span>
                                            </div>
                                        {elseif $column.sortable}
                                            <span class="mod-table-title mod-sortable" @click="go({ ts: '{$column.name}', td: '{$column.nextDir}', tp: 1 })">{$column.title}</span>
                                        {else}
                                            <span class="mod-table-title">{$column.title}</span>
                                        {/if}
                                    </th>
                                {/foreach}
                                {if $table.actions}<th class="mod-table-actions-head"></th>{/if}
                            </tr>
                        </thead>
                        <tbody @change="count()">
                            {foreach $table.rows as $row}
                                <tr class="mod-table-row">
                                    {include file="{$table.rowTemplate}.tpl" row=$row}
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                    {if !$table.rows}
                        <div class="tssggs-table__empty">{t key='admin.kernel.noRecords'}</div>
                    {/if}

                    <div class="mod-list-foot">
                        <div class="mod-pager">
                            <a class="mod-pager-btn previous{if $table.page < 2} disabled{/if}" href="#" {if $table.page > 1}@click.prevent="go({ tp: {$table.page - 1} })"{else}@click.prevent{/if}></a>
                            {foreach $table.pageList as $number}
                                <span>
                                    {if $number}
                                        <a class="mod-pager-btn{if $number === $table.page} current{/if}" href="#" @click.prevent="go({ tp: {$number} })"> {$number} </a>
                                    {else}
                                        <a class="mod-pager-btn disabled"> ... </a>
                                    {/if}
                                </span>
                            {/foreach}
                            <a class="mod-pager-btn next{if $table.page >= $table.pages} disabled{/if}" href="#" {if $table.page < $table.pages}@click.prevent="go({ tp: {$table.page + 1} })"{else}@click.prevent{/if}></a>
                        </div>
                        <div class="mod-list-sizes">
                            {foreach $table.lengths as $length}
                                <a class="mod-list-size{if $length === $table.length} active{/if}" @click="go({ tl: {$length}, tp: 1 })"><span>{$length}</span></a>
                            {/foreach}
                            <a class="mod-list-size{if $table.length === $table.all} active{/if}" @click="go({ tl: {$table.all}, tp: 1 })"><span>∞</span></a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
