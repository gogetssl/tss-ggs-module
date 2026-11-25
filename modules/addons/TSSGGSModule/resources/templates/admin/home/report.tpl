{*
    "Send Report" dialog: the facts of this install (read-only), the dates and the description of the issue.
    Send e-mails the report with the module log to support; "Download Log File" gives the same log as a file.
*}
{$f = 'forms.reporting_form'}
{include file='admin/partials/dialog_start.tpl' title={t key='modals.reporting_modal.sendReport'} action={url action='sendReport'} target='#tssggs-dialog' size='lg'}
    <div class="lu-container-fluid">
        <div class="lu-row">
            <div class="lu-form-group lu-col-md-6">
                <label class="lu-form-label"><span>{t key="{$f}.sendTo"}</span></label>
                <input type="text" class="lu-form-control" value="{$sendTo}" readonly>
                <span class="lu-form-feedback lu-form-feedback--icon">{t key="{$f}.sendTo_description"}</span>
            </div>
            <div class="lu-form-group lu-col-md-6">
                <label class="lu-form-label"><span>{t key="{$f}.whmcsUrl"}</span></label>
                <input type="text" class="lu-form-control" value="{$whmcsUrl}" readonly>
            </div>
            <div class="lu-form-group lu-col-md-6">
                <label class="lu-form-label"><span>{t key="{$f}.moduleVersion"}</span></label>
                <input type="text" class="lu-form-control" value="{$moduleVersion}" readonly>
            </div>
            {if $ggsPartnerId !== ''}
                <div class="lu-form-group lu-col-md-6">
                    <label class="lu-form-label"><span>{t key="{$f}.ggsPartnerId"}</span></label>
                    <input type="text" class="lu-form-control" value="{$ggsPartnerId}" readonly>
                </div>
            {/if}
            {if $tssPartnerId !== ''}
                <div class="lu-form-group lu-col-md-6">
                    <label class="lu-form-label"><span>{t key="{$f}.tssPartnerId"}</span></label>
                    <input type="text" class="lu-form-control" value="{$tssPartnerId}" readonly>
                </div>
            {/if}
            <div class="lu-form-group lu-col-md-6">
                <label class="lu-form-label"><span>{t key="{$f}.phpVersion"}</span></label>
                <input type="text" class="lu-form-control" value="{$phpVersion}" readonly>
            </div>
            <div class="lu-form-group lu-col-md-6">
                <label class="lu-form-label"><span>{t key="{$f}.cronStatus"}</span></label>
                <input type="text" class="lu-form-control" value="{$cronStatus}" readonly>
            </div>
            <div class="lu-form-group lu-col-md-6">
                <label class="lu-form-label"><span>{t key="{$f}.fromDate"}</span></label>
                <input type="date" name="fromDate" class="lu-form-control" value="{$fromDate}" required>
            </div>
            <div class="lu-form-group lu-col-md-6">
                <label class="lu-form-label"><span>{t key="{$f}.toDate"}</span></label>
                <input type="date" name="toDate" class="lu-form-control" value="{$toDate}" required>
            </div>
            <div class="lu-form-group lu-col-md-12">
                <label class="lu-form-label"><span>{t key="{$f}.issueDetails"}</span></label>
                <textarea name="issueDetails" class="lu-form-control" rows="4" required>{$issueDetails}</textarea>
            </div>
            <div class="lu-form-group lu-col-md-12">
                <label class="lu-form-label"><span>{t key="{$f}.modifiedModuleFiles"}</span></label>
                <div class="lu-alert lu-alert--faded lu-alert--{if $modifiedCount}danger{else}success{/if}">
                    {if $modifiedCount === 0}{t key="{$f}.noModifiedFiles"}{elseif $modifiedCount === 1}{t key="{$f}.modifiedFile"}{else}{t key="{$f}.modifiedFiles" modifiedCount=$modifiedCount}{/if}
                </div>
            </div>
        </div>
    </div>
    {* the log as a file: a plain download with what is typed above (not sent anywhere) *}
    <div class="lu-top__toolbar" x-data>
        <button type="button" class="lu-btn lu-btn--default" data-url="{url action='log'}"
                @click="var form = $el.closest('form'); location.href = $el.dataset.url + '&' + new URLSearchParams({ fromDate: form.fromDate.value, toDate: form.toDate.value, issueDetails: form.issueDetails.value }).toString()">
            <span class="lu-btn__text">{t key="{$f}.download"}</span>
        </button>
    </div>
{include file='admin/partials/dialog_end.tpl' submit={t key="{$f}.send"}}
