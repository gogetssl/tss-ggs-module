{*
    A list with search, sorting and paging ($table from App\Kernel\Table::run). The cells of a row come from the
    page's own template ($table.rowTemplate, variable $row). Search, a click on a column title, a page or a length
    reloads only this block (htmx; the request carries table=<id>).
    A button inside a row posts with hx-post; the enclosing form sends the current search, sorting and page along.
    Give the button its own hx-target AND hx-swap (it would inherit hx-swap="outerHTML" from the form).
*}
<div id="tssggs-table-{$table.id}" class="lu-widget"
     x-data="{ ticked: 0, go(values) { var refs = this.$refs; Object.keys(values).forEach(function (name) { refs[name].value = values[name] }); refs.form.requestSubmit() }, count() { this.ticked = this.$root.querySelectorAll('[data-row-check]:checked').length }, tickAll(on) { this.$root.querySelectorAll('[data-row-check]:not(:disabled)').forEach(function (box) { box.checked = on }); this.count() } }">
    <div class="lu-widget__body">
        <form method="get" action="addonmodules.php" x-ref="form" class="lu-t-c datatableLoader"
              hx-get="addonmodules.php" hx-target="#tssggs-table-{$table.id}" hx-swap="outerHTML"
              hx-trigger="submit, input delay:400ms from:find input[name=tq]">
            <input type="hidden" name="module" value="{$moduleName}">
            <input type="hidden" name="mg-page" value="{$pageName}">
            <input type="hidden" name="table" value="{$table.id}">
            <input type="hidden" name="ts" value="{$table.sort}" x-ref="ts">
            <input type="hidden" name="td" value="{$table.dir}" x-ref="td">
            <input type="hidden" name="tp" value="{$table.page}" x-ref="tp">
            <input type="hidden" name="tl" value="{$table.length}" x-ref="tl">
            {foreach $table.keep as $keepName => $keepValue}
                <input type="hidden" name="{$keepName}" value="{$keepValue}">
            {/foreach}

            {if $table.searchable}
                <div class="lu-t-c__top lu-top mob-top-search">
                    <div class="lu-top__toolbar">
                        <div class="lu-top__search lu-input-group">
                            <span class="lu-icon-sm lu-input-group__icon"><span class="tssggs-icobox">{icon name='magnify'}</span></span>
                            <input class="lu-form-control lu-input-group__form-control lu-table-search" name="tq" x-ref="tq" value="{$table.search}"
                                   placeholder="{t key='admin.kernel.search'}" autocomplete="off" @input="$refs.tp.value = 1">
                            <button type="button" class="lu-btn lu-btn--link lu-p-r-0x lu-p-l-0x lu-m-r-1x lu-clear-main-search-button"
                                    {if $table.search === ''}disabled{/if} @click="go({ tq: '', tp: 1 })">
                                <span class="lu-i-c-2x tssggs-icobox">{icon name='close'}</span>
                            </button>
                        </div>
                    </div>
                    {* buttons on the right of the search (Table::toolbar) *}
                    {if $table.toolbar !== ''}
                        <div class="lu-top__toolbar lu-single-element">{include file="{$table.toolbar}.tpl"}</div>
                    {/if}
                </div>
            {/if}

            {* rows can be ticked (Table::selectable): the number of ticked rows and what can be done with them *}
            {if $table.mass !== ''}
                <div class="lu-t-c__mass-actions lu-top" :class="ticked && 'is-open'">
                    <div class="lu-top__title"><span class="lu-badge lu-badge--primary lu-value" x-text="ticked"></span> {t key='admin.kernel.selected'}</div>
                    <div class="lu-top__toolbar lu-single-element">{include file="{$table.mass}.tpl"}</div>
                </div>
            {/if}

            <div class="dataTables_wrapper no-footer">
                <div class="lu-d-grid">
                    <table class="lu-table lu-table--mob-collapsible dataTable no-footer dtr-column">
                        <thead>
                            <tr role="row">
                                {foreach $table.columns as $column}
                                    <th class="{$column.class}">
                                        {if $column@first && $table.mass !== ''}
                                            <div class="lu-rail">
                                                <div class="lu-form-check">
                                                    <label>
                                                        <input class="lu-form-checkbox" type="checkbox" @change="tickAll($el.checked)">
                                                        <span class="lu-form-indicator"></span>
                                                    </label>
                                                </div>
                                                <span class="lu-table__text{if $column.sortable} lu-is-sortable{/if}" {if $column.sortable}@click="go({ ts: '{$column.name}', td: '{$column.nextDir}', tp: 1 })"{/if}>{$column.title}</span>
                                            </div>
                                        {elseif $column.sortable}
                                            <span class="lu-table__text lu-is-sortable" @click="go({ ts: '{$column.name}', td: '{$column.nextDir}', tp: 1 })">{$column.title}</span>
                                        {else}
                                            <span class="lu-table__text">{$column.title}</span>
                                        {/if}
                                    </th>
                                {/foreach}
                                {if $table.actions}<th class="mgTableActionsHeader"></th>{/if}
                            </tr>
                        </thead>
                        <tbody @change="count()">
                            {foreach $table.rows as $row}
                                <tr class="lu-table-row">
                                    {include file="{$table.rowTemplate}.tpl" row=$row}
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                    {if !$table.rows}
                        <div class="tssggs-table__empty">{t key='admin.kernel.noRecords'}</div>
                    {/if}

                    <div class="lu-t-c__footer table-footer">
                        <div class="dataTables_paginate paging_simple_numbers">
                            <a class="paginate_button previous{if $table.page < 2} disabled{/if}" href="#" {if $table.page > 1}@click.prevent="go({ tp: {$table.page - 1} })"{else}@click.prevent{/if}></a>
                            {foreach $table.pageList as $number}
                                <span>
                                    {if $number}
                                        <a class="paginate_button{if $number === $table.page} current{/if}" href="#" @click.prevent="go({ tp: {$number} })"> {$number} </a>
                                    {else}
                                        <a class="paginate_button disabled"> ... </a>
                                    {/if}
                                </span>
                            {/foreach}
                            <a class="paginate_button next{if $table.page >= $table.pages} disabled{/if}" href="#" {if $table.page < $table.pages}@click.prevent="go({ tp: {$table.page + 1} })"{else}@click.prevent{/if}></a>
                        </div>
                        <div class="lu-dt-buttons">
                            {foreach $table.lengths as $length}
                                <a class="dt-button{if $length === $table.length} active{/if}" @click="go({ tl: {$length}, tp: 1 })"><span>{$length}</span></a>
                            {/foreach}
                            <a class="dt-button{if $table.length === $table.all} active{/if}" @click="go({ tl: {$table.all}, tp: 1 })"><span>∞</span></a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
