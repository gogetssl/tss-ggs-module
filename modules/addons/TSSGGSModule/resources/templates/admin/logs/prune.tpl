{*
    "Delete Logs Manually" dialog: the types to delete ($types: type => name; none chosen = every type) and
    "older than" in days (0 = of any age).
*}
{include file='admin/partials/dialog_start.tpl' title={t key='pruneTitle'} action={url action='prune'} target='#tssggs-logs'}
    <div class="lu-form-group">
        <label class="lu-form-label lu-single-element"><span>{t key='pruneTypes'}</span>{tip key='pruneTypes_description'}</label>
        <select name="types[]" class="lu-form-control" multiple>
            {foreach $types as $value => $name}
                <option value="{$value}">{$name}</option>
            {/foreach}
        </select>
    </div>
    <div class="lu-form-group">
        <label class="lu-form-label lu-single-element"><span>{t key='pruneOlderThan'}</span>{tip key='pruneOlderThan_description'}</label>
        <input type="number" name="delete_older_than" class="lu-form-control" value="0" min="0" max="9999" step="1">
    </div>
{include file='admin/partials/dialog_end.tpl' submit={t key='delete'}}
