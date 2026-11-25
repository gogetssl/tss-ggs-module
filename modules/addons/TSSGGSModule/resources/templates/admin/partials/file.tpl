{* A file field of a form: {include file='admin/partials/file.tpl' name='csv' accept='.csv,text/csv'}; shows the chosen file's name *}
<div class="mod-inputbox mod-input" x-data="{ file: '' }">
    <div class="mod-file">
        <span class="mod-input" x-text="file || $el.dataset.empty" data-empty="{t key='admin.kernel.noFile'}">{t key='admin.kernel.noFile'}</span>
        <input type="file" name="{$name}" id="tssggs-file-{$name}" class="mod-input hidden"{if $accept|default:'' !== ''} accept="{$accept}"{/if}
               @change="file = $el.files.length ? $el.files[0].name : ''">
    </div>
    <label class="mod-btn mod-btn--default mod-field-label mod-inputbox-btn" for="tssggs-file-{$name}"> {t key='admin.kernel.browse'} </label>
</div>
