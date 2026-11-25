{*
    "Send Report" dialog: the facts of this install (read-only), the dates and the description of the issue.
    Send e-mails the report with the module log to support; "Download Log File" gives the same log as a file.
*}
{$f = 'forms.reporting_form'}
{include file='admin/partials/dialog_start.tpl' title={t key='modals.reporting_modal.sendReport'} action={url action='sendReport'} target='#tssggs-dialog' size='lg'}
    <div class="mod-wrap--wide">
        <div class="mod-row">
            <div class="mod-field mod-col-6">
                <label class="mod-field-label"><span>{t key="{$f}.sendTo"}</span></label>
                <input type="text" class="mod-input" value="{$sendTo}" readonly>
                <span class="mod-field-note mod-field-note--icon">{t key="{$f}.sendTo_description"}</span>
            </div>
            <div class="mod-field mod-col-6">
                <label class="mod-field-label"><span>{t key="{$f}.whmcsUrl"}</span></label>
                <input type="text" class="mod-input" value="{$whmcsUrl}" readonly>
            </div>
            <div class="mod-field mod-col-6">
                <label class="mod-field-label"><span>{t key="{$f}.moduleVersion"}</span></label>
                <input type="text" class="mod-input" value="{$moduleVersion}" readonly>
            </div>
            {if $ggsPartnerId !== ''}
                <div class="mod-field mod-col-6">
                    <label class="mod-field-label"><span>{t key="{$f}.ggsPartnerId"}</span></label>
                    <input type="text" class="mod-input" value="{$ggsPartnerId}" readonly>
                </div>
            {/if}
            {if $tssPartnerId !== ''}
                <div class="mod-field mod-col-6">
                    <label class="mod-field-label"><span>{t key="{$f}.tssPartnerId"}</span></label>
                    <input type="text" class="mod-input" value="{$tssPartnerId}" readonly>
                </div>
            {/if}
            <div class="mod-field mod-col-6">
                <label class="mod-field-label"><span>{t key="{$f}.phpVersion"}</span></label>
                <input type="text" class="mod-input" value="{$phpVersion}" readonly>
            </div>
            <div class="mod-field mod-col-6">
                <label class="mod-field-label"><span>{t key="{$f}.cronStatus"}</span></label>
                <input type="text" class="mod-input" value="{$cronStatus}" readonly>
            </div>
            <div class="mod-field mod-col-6">
                <label class="mod-field-label"><span>{t key="{$f}.fromDate"}</span></label>
                <input type="date" name="fromDate" class="mod-input" value="{$fromDate}" required>
            </div>
            <div class="mod-field mod-col-6">
                <label class="mod-field-label"><span>{t key="{$f}.toDate"}</span></label>
                <input type="date" name="toDate" class="mod-input" value="{$toDate}" required>
            </div>
            <div class="mod-field mod-col-12">
                <label class="mod-field-label"><span>{t key="{$f}.issueDetails"}</span></label>
                <textarea name="issueDetails" class="mod-input" rows="4" required>{$issueDetails}</textarea>
            </div>
            <div class="mod-field mod-col-12">
                <label class="mod-field-label"><span>{t key="{$f}.modifiedModuleFiles"}</span></label>
                <div class="mod-alert mod-alert--soft mod-alert--{if $modifiedCount}danger{else}success{/if}">
                    {if $modifiedCount === 0}{t key="{$f}.noModifiedFiles"}{elseif $modifiedCount === 1}{t key="{$f}.modifiedFile"}{else}{t key="{$f}.modifiedFiles" modifiedCount=$modifiedCount}{/if}
                </div>
            </div>
        </div>
    </div>
    {* the log as a file: a plain download with what is typed above (not sent anywhere) *}
    <div class="mod-bar-tools" x-data>
        <button type="button" class="mod-btn mod-btn--default" data-url="{url action='log'}"
                @click="var form = $el.closest('form'); location.href = $el.dataset.url + '&' + new URLSearchParams({ fromDate: form.fromDate.value, toDate: form.toDate.value, issueDetails: form.issueDetails.value }).toString()">
            <span class="mod-btn-text">{t key="{$f}.download"}</span>
        </button>
    </div>
{include file='admin/partials/dialog_end.tpl' submit={t key="{$f}.send"}}
