{*
    Import page: two tabs (one order / a CSV file). The tabs are switched by Alpine; both forms post to the action
    "import" and come back to this page.
*}
{include file='admin/partials/flash.tpl'}

{if $showAbout}
    <span class="tssggs-html">
        <div class="tssggs-explain">
            <span class="tssggs-explain__icon">{icon name='information-outline'}</span>
            <div>
                <h3>{t key='about_title'}</h3>
                <p>{t key='about_intro'}</p>
                <ul>
                    <li>{t key='about_single'}</li>
                    <li>{t key='about_csv'}</li>
                    <li>{t key='about_service'}</li>
                </ul>
            </div>
        </div>
    </span>
{/if}

<div class="mod-box" data-component="tabs-container" x-data="{ tab: '{$tab}' }">
    <div class="mod-box-head">
        <div class="mod-box-nav">
            <ul class="mod-nav mod-nav--md mod-nav--row mod-nav--tabs mod-nav--arrow">
                <li class="mod-nav-item" :class="tab === 'single' && 'is-active'">
                    <a class="mod-nav-link" href="#" @click.prevent="tab = 'single'"><span class="mod-nav-text">{t key='index.import_container.single'}</span></a>
                </li>
                <li class="mod-nav-item" :class="tab === 'csv' && 'is-active'">
                    <a class="mod-nav-link" href="#" @click.prevent="tab = 'csv'"><span class="mod-nav-text">{t key='index.import_container.csv'}</span></a>
                </li>
            </ul>
        </div>
    </div>
    <div class="mod-box-body">
        <div class="mod-panes">
            <div class="mod-pad mod-solo mod-pane" :class="tab === 'single' && 'is-active'">
                <div class="mod-box">
                    <div class="mod-box-body">
                        <div class="mod-box-content mod-solo">
                            <form method="post" action="{url action='import'}" class="mod-w-full">
                                <input type="hidden" name="token" value="{$token}">
                                <input type="hidden" name="mode" value="single">
                                <div class="mod-wrap--wide">
                                    <div class="mod-row">
                                        <div class="mod-field mod-col-6">
                                            <label class="mod-field-label"><span>{t key='index.forms.import_form.remoteOrderId'}</span></label>
                                            <input name="remoteOrderId" type="text" class="mod-input" autocomplete="off" required>
                                        </div>
                                        <div class="mod-field mod-col-6">
                                            <label class="mod-field-label"><span>{t key='index.forms.import_form.clientId'}</span></label>
                                            <select name="clientId" class="mod-input" required>
                                                <option value=""></option>
                                                {foreach $clients as $id => $name}
                                                    <option value="{$id}">{$name}</option>
                                                {/foreach}
                                            </select>
                                        </div>
                                        <div class="mod-field mod-col-6">
                                            <label class="mod-field-label"><span>{t key='index.forms.import_form.serviceId'}</span></label>
                                            <input name="serviceId" type="text" class="mod-input" autocomplete="off" inputmode="numeric">
                                        </div>
                                        <div class="mod-field mod-col-6">
                                            <label class="mod-field-label"><span>{t key='index.forms.import_form.payMethod'}</span></label>
                                            <select name="payMethod" class="mod-input" required>
                                                <option value=""></option>
                                                {foreach $payMethods as $id => $name}
                                                    <option value="{$id}">{$name}</option>
                                                {/foreach}
                                            </select>
                                        </div>
                                        <div class="mod-field mod-col-6 mod-check mod-gap-b mod-solo">
                                            {include file='admin/partials/yesno.tpl' name='generateInvoice' label={t key='index.forms.import_form.generateInvoice'} on=false}
                                        </div>
                                    </div>
                                </div>
                                <div class="mod-bar-tools mod-solo">
                                    <button type="submit" class="mod-btn mod-btn--success">
                                        <span class="mod-btn-icon tssggs-icobox">{icon name='import'}</span><span class="mod-btn-text">{t key='index.forms.import_form.import'}</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mod-pad mod-pane" :class="tab === 'csv' && 'is-active'">
                <div class="mod-box">
                    <div class="mod-box-body">
                        <div class="mod-box-content mod-solo">
                            <form method="post" enctype="multipart/form-data" action="{url action='import'}" class="mod-w-full">
                                <input type="hidden" name="token" value="{$token}">
                                <input type="hidden" name="mode" value="csv">
                                <div class="mod-field">
                                    <label class="mod-field-label"><span>{t key='index.forms.import_form_csv.csv'}</span></label>
                                    {include file='admin/partials/file.tpl' name='csv' accept='.csv,text/csv'}
                                </div>
                                <div class="mod-field mod-check mod-gap-b mod-solo">
                                    {include file='admin/partials/yesno.tpl' name='generateInvoice' label={t key='index.forms.import_form_csv.generateInvoice'} on=false}
                                </div>
                                <div class="mod-bar-tools mod-solo">
                                    <button type="submit" class="mod-btn mod-btn--success">
                                        <span class="mod-btn-icon tssggs-icobox">{icon name='import'}</span><span class="mod-btn-text">{t key='index.forms.import_form_csv.import'}</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="mod-box tssggs-dl">
                    <div class="mod-box-body">
                        <div class="mod-box-content mod-solo">
                            <div class="mod-bar-tools mod-solo">
                                <a class="mod-btn mod-btn--success" href="{url action='template'}">
                                    <span class="mod-btn-icon tssggs-icobox">{icon name='file-document-outline'}</span><span class="mod-btn-text">{t key='index.forms.template_download_form.download'}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
