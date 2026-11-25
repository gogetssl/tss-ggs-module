{*
    One legacy module ($src): its numbers, whether its version can be migrated, and the start form - with one choice
    per product that has no match at the vendor ($src.toChoose). The buttons of the form start the migration in a
    variant (default / without the cPanel keys / keeping the legacy module active); "Standalone" leaves the legacy
    module as it is. In the wizard the start comes back to the wizard's migration step.
*}
{$m = 'admin.migration'}
<div class="mod-box tssggs-migration__source">
    <div class="mod-box-head">
        <div class="mod-box-top mod-bar">
            <span class="mod-box-icon">{icon name='package-variant-closed'}</span>
            <div class="mod-bar-title"> {$src.title} </div>
        </div>
    </div>
    <div class="mod-box-body">
        <div class="mod-box-content">
            <span class="tssggs-html">
                <div class="tssggs-cards">
                    {foreach $src.cards as $card}
                        <div class="tssggs-card">
                            <div class="tssggs-card__label">{$card.label}</div>
                            <div class="tssggs-card__value">{$card.value}</div>
                            <div class="tssggs-card__sub">{$card.sub}</div>
                        </div>
                    {/foreach}
                </div>

                <div class="tssggs-note is-{$src.supportTone}"><b>{$src.supportText}</b>{if $src.supportMore !== ''} {$src.supportMore}{/if}</div>
                {if $src.unsupported !== ''}<div class="tssggs-note is-warn">{$src.unsupported}</div>{/if}
                {if $src.others !== ''}<p class="tssggs-muted">{$src.others}</p>{/if}
                <p class="tssggs-muted">{$src.credentials}</p>
                <p class="tssggs-muted">{t key="{$m}.choice_current"} <b>{$src.choice}</b></p>

                {if $src.state === 'nothing'}
                    <div class="tssggs-note is-ok">{t key="{$m}.nothing_left"}</div>
                {elseif $src.state === 'old'}
                    <div class="tssggs-note is-bad">{t key="{$m}.unsupported_version_hint"}</div>
                {else}
                    <form method="post" action="{url page='migration' action='start'}" data-confirm="{t key="{$m}.confirm_start"}">
                        <input type="hidden" name="token" value="{$token}">
                        <input type="hidden" name="source" value="{$src.source}">
                        {if $wizard}<input type="hidden" name="return" value="wizard">{/if}

                        {if $src.toChoose}
                            <div class="tssggs-note is-warn"><b>{$src.mapTitle}</b><br>{t key="{$m}.map_intro"}</div>
                            <table class="tssggs-table tssggs-map">
                                <thead><tr><th>{t key="{$m}.map_col_legacy"}</th><th>{t key="{$m}.map_col_services"}</th><th>{t key="{$m}.map_col_choice"}</th></tr></thead>
                                <tbody>
                                    {foreach $src.toChoose as $item}
                                        <tr>
                                            <td>{$item.label}{if $item.unsupported}<br><span class="tssggs-muted">{t key="{$m}.map_unsupported"}</span>{/if}</td>
                                            <td>{$item.services}</td>
                                            <td>
                                                <select class="mod-input" name="map[{$item.ref}]" required data-native>
                                                    <option value="">{t key="{$m}.map_choose"}</option>
                                                    <option value="record">{t key="{$m}.map_record"}</option>
                                                    {if $item.same}
                                                        <optgroup label="{t key="{$m}.map_same_brand"}">
                                                            {foreach $item.same as $option}<option value="{$option.id}">{$option.name}</option>{/foreach}
                                                        </optgroup>
                                                    {/if}
                                                    {if $item.other}
                                                        <optgroup label="{t key="{$m}.map_all_products"}">
                                                            {foreach $item.other as $option}<option value="{$option.id}">{$option.name}</option>{/foreach}
                                                        </optgroup>
                                                    {/if}
                                                </select>
                                            </td>
                                        </tr>
                                    {/foreach}
                                </tbody>
                            </table>
                            <p class="tssggs-muted">{t key="{$m}.map_record_hint"}</p>
                        {/if}
                        {if $src.quiet !== ''}
                            <details class="tssggs-details"><summary>{$src.quietTitle}</summary><p class="tssggs-muted">{$src.quiet}</p></details>
                        {/if}

                        <p class="tssggs-muted">{t key="{$m}.start_defaults"}</p>
                        <div class="tssggs-actions">
                            <a class="mod-btn mod-btn--default" href="{url page='migration' action='report' source=$src.source}">{t key="{$m}.btn_report"}</a>
                            <button type="submit" class="mod-btn mod-btn--success" name="variant" value="default">{t key="{$m}.btn_start"}</button>
                            <button type="submit" class="mod-btn mod-btn--default" name="variant" value="nokeys">{t key="{$m}.btn_start_nokeys"}</button>
                            {if !$wizard}
                                <button type="submit" class="mod-btn mod-btn--default" name="variant" value="keeplegacy">{t key="{$m}.btn_start_keeplegacy"}</button>
                                {* leaves the legacy module as it is: not a start, so the choices above are not asked for *}
                                <button type="submit" class="mod-btn mod-btn--default" formaction="{url page='migration' action='skip'}" formnovalidate data-no-confirm>{t key="{$m}.btn_skip"}</button>
                            {/if}
                        </div>
                    </form>
                {/if}
            </span>
        </div>
    </div>
</div>
