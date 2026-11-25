{*
    A section of a settings screen: icon, title, a short description, then the fields.

        {include file='admin/settings/section_start.tpl' name='clientArea' icon='account-group-outline'}
            ... fields ...
        {include file='admin/settings/section_end.tpl'}

    Texts of the screen's form group ($f): "<name>" the title, "<name>_desc" the description.
    $points: text keys of points listed under the description ("<key>" bold lead-in, "<key>_text" the explanation).
    $sub=true: a small heading inside a section (no icon, no description).
*}
<div class="mod-box tssggs-sec{if $sub|default:false} tssggs-sec--sub{/if}">
    <div class="mod-box-head">
        <div class="mod-box-top mod-bar">
            {if $icon|default:'' !== ''}<span class="mod-box-icon">{icon name=$icon}</span>{/if}
            <div class="mod-bar-title"> {t key="{$f}.{$name}"} </div>
        </div>
    </div>
    <div class="mod-box-body">
        <div class="mod-box-content">
            {if !$sub|default:false}
                <span>
                    <div class="tssggs-sec__desc">
                        <p>{t key="{$f}.{$name}_desc"}</p>
                        {if $points|default:[]}
                            <ul>
                                {foreach $points as $point}
                                    <li><b>{t key="{$f}.{$point}"}</b> {t key="{$f}.{$point}_text"}</li>
                                {/foreach}
                            </ul>
                        {/if}
                    </div>
                </span>
            {/if}
