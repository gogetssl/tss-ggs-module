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

<div class="lu-widget" data-component="tabs-container" x-data="{ tab: '{$tab}' }">
    <div class="lu-widget__header">
        <div class="lu-widget__nav">
            <ul class="lu-nav lu-nav--md lu-nav--h lu-nav--tabs lu-nav--arrow">
                <li class="lu-nav__item" :class="tab === 'single' && 'is-active'">
                    <a class="lu-nav__link" href="#" @click.prevent="tab = 'single'"><span class="lu-nav__link-text">{t key='index.import_container.single'}</span></a>
                </li>
                <li class="lu-nav__item" :class="tab === 'csv' && 'is-active'">
                    <a class="lu-nav__link" href="#" @click.prevent="tab = 'csv'"><span class="lu-nav__link-text">{t key='index.import_container.csv'}</span></a>
                </li>
            </ul>
        </div>
    </div>
    <div class="lu-widget__body">
        <div class="lu-tab-content">
            <div class="lu-p-2x lu-single-element lu-tab-pane" :class="tab === 'single' && 'is-active'">
                <div class="lu-widget">
                    <div class="lu-widget__body">
                        <div class="lu-widget__content lu-single-element">
                            <form method="post" action="{url action='import'}" class="lu-w-100">
                                <input type="hidden" name="token" value="{$token}">
                                <input type="hidden" name="mode" value="single">
                                <div class="lu-container-fluid">
                                    <div class="lu-row">
                                        <div class="lu-form-group lu-col-md-6">
                                            <label class="lu-form-label"><span>{t key='index.forms.import_form.remoteOrderId'}</span></label>
                                            <input name="remoteOrderId" type="text" class="lu-form-control" autocomplete="off" required>
                                        </div>
                                        <div class="lu-form-group lu-col-md-6">
                                            <label class="lu-form-label"><span>{t key='index.forms.import_form.clientId'}</span></label>
                                            <select name="clientId" class="lu-form-control" required>
                                                <option value=""></option>
                                                {foreach $clients as $id => $name}
                                                    <option value="{$id}">{$name}</option>
                                                {/foreach}
                                            </select>
                                        </div>
                                        <div class="lu-form-group lu-col-md-6">
                                            <label class="lu-form-label"><span>{t key='index.forms.import_form.serviceId'}</span></label>
                                            <input name="serviceId" type="text" class="lu-form-control" autocomplete="off" inputmode="numeric">
                                        </div>
                                        <div class="lu-form-group lu-col-md-6">
                                            <label class="lu-form-label"><span>{t key='index.forms.import_form.payMethod'}</span></label>
                                            <select name="payMethod" class="lu-form-control" required>
                                                <option value=""></option>
                                                {foreach $payMethods as $id => $name}
                                                    <option value="{$id}">{$name}</option>
                                                {/foreach}
                                            </select>
                                        </div>
                                        <div class="lu-form-group lu-col-md-6 lu-form-check lu-m-b-2x lu-single-element">
                                            {include file='admin/partials/yesno.tpl' name='generateInvoice' label={t key='index.forms.import_form.generateInvoice'} on=false}
                                        </div>
                                    </div>
                                </div>
                                <div class="lu-top__toolbar lu-single-element">
                                    <button type="submit" class="lu-btn lu-btn--success">
                                        <span class="lu-btn__icon tssggs-icobox">{icon name='import'}</span><span class="lu-btn__text">{t key='index.forms.import_form.import'}</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lu-p-2x lu-tab-pane" :class="tab === 'csv' && 'is-active'">
                <div class="lu-widget">
                    <div class="lu-widget__body">
                        <div class="lu-widget__content lu-single-element">
                            <form method="post" enctype="multipart/form-data" action="{url action='import'}" class="lu-w-100">
                                <input type="hidden" name="token" value="{$token}">
                                <input type="hidden" name="mode" value="csv">
                                <div class="lu-form-group">
                                    <label class="lu-form-label"><span>{t key='index.forms.import_form_csv.csv'}</span></label>
                                    {include file='admin/partials/file.tpl' name='csv' accept='.csv,text/csv'}
                                </div>
                                <div class="lu-form-group lu-form-check lu-m-b-2x lu-single-element">
                                    {include file='admin/partials/yesno.tpl' name='generateInvoice' label={t key='index.forms.import_form_csv.generateInvoice'} on=false}
                                </div>
                                <div class="lu-top__toolbar lu-single-element">
                                    <button type="submit" class="lu-btn lu-btn--success">
                                        <span class="lu-btn__icon tssggs-icobox">{icon name='import'}</span><span class="lu-btn__text">{t key='index.forms.import_form_csv.import'}</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="lu-widget tssggs-dl">
                    <div class="lu-widget__body">
                        <div class="lu-widget__content lu-single-element">
                            <div class="lu-top__toolbar lu-single-element">
                                <a class="lu-btn lu-btn--success" href="{url action='template'}">
                                    <span class="lu-btn__icon tssggs-icobox">{icon name='file-document-outline'}</span><span class="lu-btn__text">{t key='index.forms.template_download_form.download'}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
