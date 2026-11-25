{*
    One field of a settings screen:

        {include file='admin/settings/field.tpl' type='yesno' name='clientSummary'}
        {include file='admin/settings/field.tpl' type='select' name='expiresSoonDays'}

    type: yesno | select | multi (several values, new ones can be typed) | text | password | number | textarea
    The label is the text "<name>" of the screen's form group ($f), the "?" hint beside it the text
    "<name>_description" (left out when there is none). The value comes from $data (or from what was typed, after a
    save that failed), the choices of a select from $options.<name>.
    Optional: placeholder (escaped text, e.g. from {t}), class (on the input, e.g. tssggs-percent / tssggs-money show a unit), placeholder, min, max, rows,
    label / hint (texts given by the screen instead of the ones looked up).
    A yesno posts $switchOn / $switchOff (on / off, or 1 / 0 where the screen's store keeps those).
*}
{if isset($typed[$name])}{$value = $typed[$name]}{else}{$value = $data[$name]|default:''}{/if}
{capture name='label'}{if $label|default:'' !== ''}{$label}{else}{t key="{$f}.{$name}"}{/if}{/capture}
{capture name='hint'}{if $hint|default:'' !== ''}{tip text=$hint}{else}{tip key="{$f}.{$name}_description"}{/if}{/capture}

{if $type === 'yesno'}
    {$on = ($value === true || $value === 1 || $value === '1' || $value === 'on')}
    <div class="lu-form-group lu-form-check lu-m-b-2x lu-single-element">
        <div class="tssggs-yn lu-single-element">
            <span class="lu-form-text lu-single-element"> {$smarty.capture.label nofilter} {$smarty.capture.hint nofilter}</span>
            <div role="radiogroup" class="tssggs-yn__segments">
                <label class="tssggs-yn__segment">
                    <input type="radio" name="{$name}" value="{$switchOn}" class="tssggs-yn__radio no-icheck"{if $on} checked{/if}>
                    <span>{t key='admin.kernel.yes'}</span>
                </label>
                <label class="tssggs-yn__segment">
                    <input type="radio" name="{$name}" value="{$switchOff}" class="tssggs-yn__radio tssggs-yn__radio--danger no-icheck"{if !$on} checked{/if}>
                    <span>{t key='admin.kernel.no'}</span>
                </label>
            </div>
        </div>
    </div>
{else}
    <div class="lu-form-group">
        <label class="lu-form-label lu-single-element"><span>{$smarty.capture.label nofilter}</span>{$smarty.capture.hint nofilter}</label>
        {if $type === 'select'}
            <select name="{$name}" class="lu-form-control {$class|default:''}">
                {foreach $options[$name]|default:[] as $optionValue => $optionText}
                    <option value="{$optionValue}"{if (string)$optionValue === (string)$value} selected{/if}>{$optionText}</option>
                {/foreach}
            </select>
        {elseif $type === 'multi'}
            {* nothing chosen still has to be posted: the empty value comes first *}
            <input type="hidden" name="{$name}" value="">
            <select name="{$name}[]" class="lu-form-control {$class|default:''}" multiple data-create data-placeholder="{$placeholder|default:'' nofilter}">
                {foreach $options[$name]|default:[] as $optionValue => $optionText}
                    <option value="{$optionValue}"{if in_array($optionValue, (array)$value)} selected{/if}>{$optionText}</option>
                {/foreach}
            </select>
        {elseif $type === 'textarea'}
            <textarea name="{$name}" class="lu-form-control {$class|default:''}" rows="{$rows|default:5}" placeholder="{$placeholder|default:'' nofilter}">{$value}</textarea>
        {else}
            <input type="{$type}" name="{$name}" class="lu-form-control {$class|default:''}" value="{$value}" placeholder="{$placeholder|default:'' nofilter}"
                   {if $min|default:'' !== ''}min="{$min}"{/if} {if $max|default:'' !== ''}max="{$max}"{/if} {if $type === 'number'}step="any"{/if} autocomplete="off">
        {/if}
    </div>
{/if}
