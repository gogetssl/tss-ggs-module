{*
    "Edit Settings" dialog of the log: what is recorded (the module log, gateway requests, events, debug mode for
    chosen services, the types) and for how long it is kept. $values: App\Libs\Logs\LogBook::settings;
    $modes / $hours / $types: value => name. The days field shows only while Clear Automatically is on.
*}
{include file='admin/partials/dialog_start.tpl' title={t key='settingsTitle'} action={url action='settings'} target='#tssggs-logs'}
    <div x-data="{ prune: {if $values.auto_prune}true{else}false{/if} }" @change="if ($event.target.name === 'auto_prune') { prune = $event.target.value === 'on' }">
        <div class="mod-field">
            {capture name='label'}{t key='set_logging'} {tip key='set_logging_description'}{/capture}
            {include file='admin/partials/yesno.tpl' name='logging' label=$smarty.capture.label on=$values.logging}
        </div>
        <div class="mod-field">
            <label class="mod-field-label mod-solo"><span>{t key='set_api_mode'}</span>{tip key='set_api_mode_description'}</label>
            <select name="api_mode" class="mod-input">
                {foreach $modes as $value => $name}
                    <option value="{$value}"{if $values.api_mode === $value} selected{/if}>{$name}</option>
                {/foreach}
            </select>
        </div>
        <div class="mod-field">
            {capture name='label'}{t key='set_events'} {tip key='set_events_description'}{/capture}
            {include file='admin/partials/yesno.tpl' name='events' label=$smarty.capture.label on=$values.events}
        </div>
        <div class="mod-field">
            <label class="mod-field-label mod-solo"><span>{t key='set_debug_services'}</span>{tip key='set_debug_services_description'}</label>
            <input type="text" name="debug_services" class="mod-input" value="{$values.debug_services}" autocomplete="off">
        </div>
        <div class="mod-field">
            <label class="mod-field-label mod-solo"><span>{t key='set_debug_hours'}</span>{tip key='set_debug_hours_description'}</label>
            <select name="debug_hours" class="mod-input">
                {foreach $hours as $value => $name}
                    <option value="{$value}"{if (string)$values.debug_hours === (string)$value} selected{/if}>{$name}</option>
                {/foreach}
            </select>
        </div>
        <div class="mod-field">
            <label class="mod-field-label mod-solo"><span>{t key='set_types'}</span>{tip key='set_types_description'}</label>
            <select name="types[]" class="mod-input" multiple>
                {foreach $types as $value => $name}
                    <option value="{$value}"{if in_array($value, $values.types)} selected{/if}>{$name}</option>
                {/foreach}
            </select>
        </div>
        <div class="mod-field">
            {capture name='label'}{t key='set_auto_prune'} {tip key='set_auto_prune_description'}{/capture}
            {include file='admin/partials/yesno.tpl' name='auto_prune' label=$smarty.capture.label on=$values.auto_prune}
        </div>
        <div class="mod-field" x-show="prune" x-cloak>
            <label class="mod-field-label mod-solo"><span>{t key='set_auto_prune_older_than'}</span>{tip key='set_auto_prune_older_than_description'}</label>
            <input type="number" name="auto_prune_older_than" class="mod-input" value="{$values.auto_prune_older_than}" min="1" max="9999" step="1">
        </div>
    </div>
{include file='admin/partials/dialog_end.tpl'}
