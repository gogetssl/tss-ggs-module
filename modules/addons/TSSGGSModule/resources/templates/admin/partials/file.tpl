{* A file field of a form: {include file='admin/partials/file.tpl' name='csv' accept='.csv,text/csv'}; shows the chosen file's name *}
<div class="lu-input-group lu-form-control" x-data="{ file: '' }">
    <div class="lu-form-input-file">
        <span class="lu-form-control" x-text="file || $el.dataset.empty" data-empty="{t key='admin.kernel.noFile'}">{t key='admin.kernel.noFile'}</span>
        <input type="file" name="{$name}" id="tssggs-file-{$name}" class="lu-form-control hidden"{if $accept|default:'' !== ''} accept="{$accept}"{/if}
               @change="file = $el.files.length ? $el.files[0].name : ''">
    </div>
    <label class="lu-btn lu-btn--default lu-form-label lu-input-group__btn" for="tssggs-file-{$name}"> {t key='admin.kernel.browse'} </label>
</div>
