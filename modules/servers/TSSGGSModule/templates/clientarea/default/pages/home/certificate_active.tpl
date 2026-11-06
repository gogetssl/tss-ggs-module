<div class="validate-ssl-unified tssggs-cert">

    {* $success is shown by main.tpl *}
    {if $errors}
        <div class="alert alert-danger" role="alert">{$errors|escape}</div>
    {/if}
    {if $issued_message}
        <div class="alert alert-info tssggs-issued-message" role="alert">{$issued_message}</div>
    {/if}

    {if $sendEmail}
        <div class="alert alert-success" role="alert">
            {$MGLANG->T('validation_page', 'send_email')}
        </div>
    {/if}

    {if $autoinstall}
        {assign var=aiDomain value=$autoinstall.domain|escape}
        <div class="alert {if $autoinstall.status == 'installed'}alert-success{elseif $autoinstall.status == 'failed'}alert-danger{elseif $autoinstall.status == 'disabled'}alert-warning{else}alert-info{/if}" role="alert">
            <strong>{$MGLANG->T('autoinstallBoxTitle')}:</strong>
            {if $autoinstall.status == 'installed'}{$MGLANG->T('autoinstallBoxInstalled')|sprintf:$aiDomain}{elseif $autoinstall.status == 'failed'}{$MGLANG->T('autoinstallBoxFailed')|sprintf:$aiDomain}{elseif $autoinstall.status == 'disabled'}{$MGLANG->T('autoinstallBoxDisabled')}{else}{$MGLANG->T('autoinstallBoxAwaiting')|sprintf:$aiDomain}{/if}
            {if $autoinstall.status != 'installed' && $autoinstall.status != 'disabled' && $autoinstall.last_error}<br><small>{$MGLANG->T('autoinstallBoxLastError')} {$autoinstall.last_error|escape}</small>{/if}
            {if $autoinstall.logs}
                <details style="margin-top:8px;">
                    <summary style="cursor:pointer;">{$MGLANG->T('autoinstallBoxLogs')}</summary>
                    <ul style="margin:6px 0 0;padding-left:18px;font-size:12px;">
                        {foreach $autoinstall.logs as $aiLog}
                            <li{if $aiLog.type == 'error'} style="color:#c0392b;"{/if}>{$aiLog.date|escape} - {$aiLog.message|escape}</li>
                        {/foreach}
                    </ul>
                </details>
            {/if}
        </div>
    {/if}

    {* ---------- Hero: status, product, subscription bar, main actions ---------- *}
    <section class="tssggs-card tssggs-hero{if !$certificateUsable} tssggs-hero--off{/if}">
        <div class="tssggs-hero__head">
            <span class="tssggs-hero__icon">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
            </span>
            <div class="tssggs-hero__title">
                {if $certificateUsable}
                    <span class="tssggs-badge">
                        <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                        {if $autoinstall && $autoinstall.status == 'installed'}{$MGLANG->T('validation_page', 'statusInstalled')}{else}{$MGLANG->T('validation_page', 'Issued')}{/if}
                    </span>
                {else}
                    <span class="tssggs-badge tssggs-badge--off">{$MGLANG->T('validation_page', 'statusUnavailable')}</span>
                {/if}
                <h3>{$product_name|escape}</h3>
                <p class="tssggs-hero__domain">
                    <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                    {$domain|escape}
                </p>
            </div>
        </div>

        {if $progress}
            <div class="tssggs-progress tssggs-progress--{$progress.state}">
                <div class="tssggs-progress__steps">
                    {foreach $progress.segments as $segment}
                        <div style="width:{$segment[1]}%">
                            {if $segment[0] == 'current' && $progress.message}
                                {assign var=pbDays value=$MGLANG->T('validation_page', $progress.daysKey)|sprintf:$progress.days}
                                {if $progress.message == 'ends'}{$MGLANG->T('validation_page', 'pbSubExpires')|sprintf:$pbDays}{else}{$MGLANG->T('validation_page', 'pbNextReissue')|sprintf:$pbDays}{/if}
                            {/if}
                        </div>
                    {/foreach}
                </div>
                <div class="tssggs-progress__bar">
                    {foreach $progress.segments as $segment}
                        <div class="tssggs-pb tssggs-pb--{$segment[0]}" style="width:{$segment[1]}%"><span>{if $segment[0] == 'current'}{$MGLANG->T('validation_page', 'pbCurrent')}{elseif $segment[0] == 'future'}{$MGLANG->T('validation_page', 'pbAvailable')}{else}{$MGLANG->T('validation_page', 'pbExpired')}{/if}</span></div>
                    {/foreach}
                </div>
                <div class="tssggs-progress__labels">
                    {foreach $progress.labels as $label}
                        {if $label}
                            <div class="tssggs-pl tssggs-pl--{$label[2]}{if $label[3]} tssggs-pl--{$label[3]}{/if}"><b>{$label[0]}</b><span>{$MGLANG->T('validation_page', $label[1])}</span></div>
                        {else}
                            <div></div>
                        {/if}
                    {/foreach}
                </div>
            </div>
        {/if}

        {if !$certificateUsable}
            <div class="tssggs-hero__foot">
                <div class="alert alert-warning" style="margin:0;">{$MGLANG->T('validation_page', 'Certificate not available')}</div>
            </div>
        {else}
            <div class="tssggs-hero__foot">
                <p>{if $autoinstall && $autoinstall.status == 'installed'}{$MGLANG->T('validation_page', 'heroInstalled')}{else}{$MGLANG->T('validation_page', 'heroReady')}{/if}</p>
                <div class="tssggs-actions">
                    {if $install_available}
                        <button type="button" class="tssggs-btn tssggs-btn--primary" data-toggle="modal" data-target="#tssggsInstallCertificate">
                            <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><rect width="20" height="8" x="2" y="2" rx="2"/><rect width="20" height="8" x="2" y="14" rx="2"/><path d="M6 6h.01M6 18h.01"/></svg>
                            {$MGLANG->T('validation_page', 'Install on server')}
                        </button>
                    {/if}
                    {if $download_formats}
                        <a class="tssggs-btn{if !$install_available} tssggs-btn--primary{/if}" href="{$whmcs_url}/clientarea.php?action=productdetails&id={$serviceid}&mg-action=download&format={$download_formats[0]}">
                            <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/><path d="M12 15V3"/></svg>
                            {$MGLANG->T('validation_page', 'Download')}
                        </a>
                    {/if}
                    {if $visibleRenewButton}
                        <button type="button" class="tssggs-btn" data-toggle="modal" data-target="#myModalRenew">
                            <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
                            {$MGLANG->T('validation_page', 'Renew SSL')}
                        </button>
                    {/if}
                </div>
            </div>
        {/if}
    </section>

    <div class="tssggs-grid">

        {* ---------- Certificate details ---------- *}
        <section class="tssggs-card">
            <h4 class="tssggs-card__title">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8M16 13H8M16 17H8"/></svg>
                {$MGLANG->T('validation_page', 'cardCertificate')}
            </h4>
            <dl class="tssggs-dl">
                <dt>{$MGLANG->T('validation_page', 'DomainName')}</dt><dd>{$domain|escape}</dd>
                <dt>{$MGLANG->T('validation_page', 'ValidFrom')}</dt><dd>{if $valid_from}{$valid_from}{else}{$MGLANG->T('validation_page', 'NA')}{/if}</dd>
                <dt>{$MGLANG->T('validation_page', 'ValidTill')}</dt><dd>{if $valid_till}{$valid_till}{else}{$MGLANG->T('validation_page', 'NA')}{/if}</dd>
                <dt>{$MGLANG->T('validation_page', 'rowSubscription')}</dt><dd>{if $subscription_period}{$subscription_period}{else}{$MGLANG->T('validation_page', 'NA')}{/if}</dd>
                {if $certificateUsable}<dt>{$MGLANG->T('validation_page', 'NextReissue')}</dt><dd>{if $next_reissue}{$next_reissue}{else}{$MGLANG->T('validation_page', 'NA')}{/if}</dd>{/if}
                <dt>{$MGLANG->T('validation_page', 'StoreAPIOrder')}</dt><dd class="tssggs-mono">{$api_order_id|escape}</dd>
                <dt>{$MGLANG->T('validation_page', 'VendorOrderID')}</dt><dd class="tssggs-mono">{$vendor_order_id|escape}</dd>
            </dl>
        </section>

        {* ---------- Order & billing ---------- *}
        <section class="tssggs-card">
            <h4 class="tssggs-card__title">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><rect width="20" height="14" x="2" y="5" rx="2"/><path d="M2 10h20"/></svg>
                {$MGLANG->T('validation_page', 'cardBilling')}
            </h4>
            <dl class="tssggs-dl">
                <dt>{$MGLANG->T('validation_page', 'RegistrationDate')}</dt><dd>{$order_details.registration_date}</dd>
                <dt>{$MGLANG->T('validation_page', 'OrderExpires')}</dt><dd>{if $order_details.expiration_date}{$order_details.expiration_date}{else}{$MGLANG->T('validation_page', 'NA')}{/if}</dd>
                <dt>{$MGLANG->T('validation_page', 'ReccuringAmount')}</dt><dd>{$order_details.reccuring_amount}</dd>
                <dt>{$MGLANG->T('validation_page', 'BillingCycle')}</dt><dd>{$order_details.billing_cycle|escape}</dd>
                <dt>{$MGLANG->T('validation_page', 'PaymentMethod')}</dt><dd>{$order_details.payment_method|escape}</dd>
            </dl>
        </section>

        {if $certificateUsable}
            {* ---------- Manage certificate ---------- *}
            <section class="tssggs-card">
                <h4 class="tssggs-card__title">
                    <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                    {$MGLANG->T('validation_page', 'cardManage')}
                </h4>
                <div class="tssggs-links">
                    <button type="button" class="tssggs-link" data-toggle="modal" data-target="#tssggsViewCertificate">
                        <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M2.06 12.35a1 1 0 0 1 0-.7 10.75 10.75 0 0 1 19.88 0 1 1 0 0 1 0 .7 10.75 10.75 0 0 1-19.88 0"/><circle cx="12" cy="12" r="3"/></svg>
                        {$MGLANG->T('validation_page', 'View Certificate')}
                    </button>
                    <form method="post" action="{$whmcs_url}/clientarea.php?action=productdetails&id={$serviceid}">
                        <input type="hidden" name="mg-action" value="sendCertificate"><input type="hidden" name="token" value="{$csrfToken}">
                        <button type="submit" class="tssggs-link">
                            <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                            {$MGLANG->T('validation_page', 'linkSendCertificate')}
                        </button>
                    </form>
                    <a class="tssggs-link" href="{$whmcs_url}/clientarea.php?action=productdetails&id={$serviceid}&mg-action=reissueSSL">
                        <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                        {$MGLANG->T('validation_page', 'Reissue SSL')}
                    </a>
                    {foreach $cpanel_services as $cpanelService}
                        <a class="tssggs-link" href="{$whmcs_url}/clientarea.php?action=productdetails&id={$cpanelService.id}&dosinglesignon=1" target="_blank" rel="noopener">
                            <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 3h6v6M10 14 21 3M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
                            {$MGLANG->T('validation_page', 'Login to cPanel')}{if $cpanel_services|@count > 1} ({$cpanelService.domain|escape}){/if}
                        </a>
                    {/foreach}
                </div>
                <div class="tssggs-links tssggs-links--danger">
                    <button type="button" class="tssggs-link" data-toggle="modal" data-target="#myModalCancel">
                        <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="m4.9 4.9 14.2 14.2"/></svg>
                        {$MGLANG->T('validation_page', 'CancelCertificate')}
                    </button>
                </div>
            </section>

            {* ---------- Download files ---------- *}
            <section class="tssggs-card">
                <h4 class="tssggs-card__title">
                    <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/><path d="M12 15V3"/></svg>
                    {$MGLANG->T('validation_page', 'cardDownloads')}
                </h4>
                <ul class="tssggs-files">
                    {foreach $download_formats as $format}
                        <li>
                            <a href="{$whmcs_url}/clientarea.php?action=productdetails&id={$serviceid}&mg-action=download&format={$format}">
                                <span>{$MGLANG->T('download_formats', $format)}</span>
                                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/><path d="M12 15V3"/></svg>
                            </a>
                        </li>
                    {/foreach}
                </ul>
            </section>
        {/if}

        {* ---------- Contacts / configurable options / organization ---------- *}
        {assign var=hasSans value=($configurableOptions.sans || $configurableOptions.sans_wildcard)}
        <section class="tssggs-card tssggs-grid__wide tssggs-tabs">
            {if $hasSans || $organization}
                <div class="tssggs-tabs__bar" role="tablist">
                    <button type="button" role="tab" class="tssggs-tab is-active" data-tssggs-tab="contacts" aria-selected="true">
                        <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        {$MGLANG->T('validation_page', 'tabContacts')}
                    </button>
                    {if $hasSans}
                        <button type="button" role="tab" class="tssggs-tab" data-tssggs-tab="options" aria-selected="false">
                            <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M21 4h-7M10 4H3M21 12h-9M8 12H3M21 20h-5M12 20H3M14 2v4M8 10v4M16 18v4"/></svg>
                            {$MGLANG->T('validation_page', 'tabOptions')}
                        </button>
                    {/if}
                    {if $organization}
                        <button type="button" role="tab" class="tssggs-tab" data-tssggs-tab="organization" aria-selected="false">
                            <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><rect width="16" height="20" x="4" y="2" rx="2"/><path d="M9 22v-4h6v4M8 6h.01M12 6h.01M16 6h.01M8 10h.01M12 10h.01M16 10h.01M8 14h.01M12 14h.01M16 14h.01"/></svg>
                            {$MGLANG->T('validation_page', 'Organization Details')}
                        </button>
                    {/if}
                </div>
            {else}
                <h4 class="tssggs-card__title">
                    <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    {$MGLANG->T('validation_page', 'tabContacts')}
                </h4>
            {/if}

            <div class="tssggs-pane is-active" data-tssggs-pane="contacts">
                <div class="tssggs-contacts">
                    {foreach ['administrator' => 'Admin Details', 'technical' => 'Technical Details'] as $contactKey => $contactTitle}
                        {assign var=contact value=$order_details_api.orderData.contacts.$contactKey}
                        <div>
                            <h5>{$MGLANG->T('validation_page', $contactTitle)}</h5>
                            <dl class="tssggs-dl">
                                <dt>{$MGLANG->T('validation_page', 'First Name')}</dt><dd>{if $contact.first_name}{$contact.first_name|escape}{else}—{/if}</dd>
                                <dt>{$MGLANG->T('validation_page', 'Last Name')}</dt><dd>{if $contact.last_name}{$contact.last_name|escape}{else}—{/if}</dd>
                                <dt>{$MGLANG->T('validation_page', 'Email')}</dt><dd>{if $contact.email}{$contact.email|escape}{else}—{/if}</dd>
                                <dt>{$MGLANG->T('validation_page', 'Title')}</dt><dd>{if $contact.title}{$contact.title|escape}{else}—{/if}</dd>
                                <dt>{$MGLANG->T('validation_page', 'Phone')}</dt><dd>{if $contact.phone}{$contact.phone|escape}{else}—{/if}</dd>
                            </dl>
                        </div>
                    {/foreach}
                </div>
            </div>

            {if $hasSans}
                <div class="tssggs-pane" data-tssggs-pane="options" hidden>
                    <dl class="tssggs-dl">
                        {if $configurableOptions.sans}
                            <dt>{$MGLANG->T('validation_page', 'Single SAN items')}</dt><dd>{$configurableOptions.sans} ({$countSAN} {$MGLANG->T('validation_page', 'used')})</dd>
                        {/if}
                        {if $configurableOptions.sans_wildcard}
                            <dt>{$MGLANG->T('validation_page', 'Wildcard SAN items')}</dt><dd>{$configurableOptions.sans_wildcard} ({$countSANWildcard} {$MGLANG->T('validation_page', 'used')})</dd>
                        {/if}
                    </dl>
                    {if $certificateUsable}
                        <a href="{$whmcs_url}/upgrade.php?type=configoptions&id={$serviceid}" class="tssggs-btn" style="margin-top:14px;">{$MGLANG->T('validation_page', 'Purchase additional SANs')}</a>
                    {/if}
                </div>
            {/if}

            {if $organization}
                <div class="tssggs-pane" data-tssggs-pane="organization" hidden>
                    <dl class="tssggs-dl">
                        <dt>{$MGLANG->T('validation_page', 'Name')}</dt><dd>{$organization.name|escape}</dd>
                        {if $organization.duns}<dt>{$MGLANG->T('validation_page', 'DUNS Number')}</dt><dd>{$organization.duns|escape}</dd>{/if}
                        {if $organization.division}<dt>{$MGLANG->T('validation_page', 'Division')}</dt><dd>{$organization.division|escape}</dd>{/if}
                        <dt>{$MGLANG->T('validation_page', 'Country')}</dt><dd>{$organization.country|escape}</dd>
                        <dt>{$MGLANG->T('validation_page', 'City')}</dt><dd>{$organization.city|escape}</dd>
                        <dt>{$MGLANG->T('validation_page', 'Street Address')}</dt><dd>{$organization.address_line_1|escape}{if $organization.address_line_2}, {$organization.address_line_2|escape}{/if}</dd>
                        <dt>{$MGLANG->T('validation_page', 'Postal Code')}</dt><dd>{$organization.postal_code|escape}</dd>
                    </dl>
                </div>
            {/if}
        </section>
    </div>
</div>

<div class="modal fade" id="tssggsViewCertificate" tabindex="-1" role="dialog" aria-labelledby="tssggsViewCertificateLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="display: block;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" style="text-align: left;" id="tssggsViewCertificateLabel">{$MGLANG->T('validation_page', 'View Certificate')}</h4>
            </div>
            <div class="modal-body" style="text-align: left;">
                <label for="tssggsCrtCode">{$MGLANG->T('validation_page', 'Certificate (CRT)')}</label>
                <textarea id="tssggsCrtCode" class="form-control" rows="10" readonly style="font-family:monospace;font-size:12px;">{$crt_code|escape}</textarea>
                {if $ca_code}
                    <label for="tssggsCaCode" style="margin-top:12px;">{$MGLANG->T('validation_page', 'Intermediate certificates (CA bundle)')}</label>
                    <textarea id="tssggsCaCode" class="form-control" rows="10" readonly style="font-family:monospace;font-size:12px;">{$ca_code|escape}</textarea>
                {/if}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->T('validation_page', 'Close')}</button>
            </div>
        </div>
    </div>
</div>

{if $install_available}
<div class="modal fade" id="tssggsInstallCertificate" tabindex="-1" role="dialog" aria-labelledby="tssggsInstallCertificateLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="{$whmcs_url}/clientarea.php?action=productdetails&id={$serviceid}">
                <input type="hidden" name="mg-action" value="installCertificate">
                <input type="hidden" name="token" value="{$csrfToken}">
                <div class="modal-header" style="display: block;">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" style="text-align: left;" id="tssggsInstallCertificateLabel">{$MGLANG->T('validation_page', 'Install on server')}</h4>
                </div>
                <div class="modal-body" style="text-align: left;">
                    <p>{$MGLANG->T('validation_page', 'install_desc')}</p>
                    <div class="form-group">
                        <label for="tssggsInstallDomain">{$MGLANG->T('validation_page', 'Domain')}</label>
                        <input id="tssggsInstallDomain" class="form-control" name="install_domain" value="{$install_domain|escape}" required>
                    </div>
                    {if !$install_key_stored}
                        <div class="form-group">
                            <label for="tssggsInstallKey">{$MGLANG->T('validation_page', 'Private key')}</label>
                            <textarea id="tssggsInstallKey" class="form-control" name="private_key" rows="8" required style="font-family:monospace;font-size:12px;" placeholder="-----BEGIN PRIVATE KEY-----"></textarea>
                            <small class="text-muted">{$MGLANG->T('validation_page', 'install_key_hint')}</small>
                        </div>
                    {/if}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->T('validation_page', 'Close')}</button>
                    <button type="submit" class="btn btn-primary">{$MGLANG->T('validation_page', 'Install')}</button>
                </div>
            </form>
        </div>
    </div>
</div>
{/if}

<div class="modal fade" id="myModalCancel" tabindex="-1" role="dialog" aria-labelledby="myModalCancelLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="mg-action" value="cancel">
                <input type="hidden" name="token" value="{$csrfToken}">
                <input type="hidden" name="remoteid" value="{$api_order_id}">
                <div class="modal-header" style="display: block;">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" style="text-align: left;" id="myModalCancelLabel">{$MGLANG->T('validation_page', 'Cancel Certificate')}</h4>
                </div>
                <div class="modal-body" style="text-align: left;">
                    {$MGLANG->T('validation_page', 'cancelDialogBody')}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->T('validation_page', 'cancelDialogKeep')}</button>
                    <button type="submit" class="btn btn-danger">{$MGLANG->T('validation_page', 'cancelDialogConfirm')}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="myModalRenew" tabindex="-1" role="dialog" aria-labelledby="myModalRenewLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="mg-action" value="renew">
                <input type="hidden" name="token" value="{$csrfToken}">
                <input type="hidden" name="remoteid" value="{$api_order_id}">
                <div class="modal-header" style="display: block;">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" style="text-align: left;" id="myModalRenewLabel">{$MGLANG->T('validation_page', 'Renew Certificate')}</h4>
                </div>
                <div class="modal-body" style="text-align: left;">
                    {$MGLANG->T('validation_page', 'Are you sure you want to renew the certificate?')}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{$MGLANG->T('validation_page', 'Close')}</button>
                    <button type="submit" class="btn btn-success">{$MGLANG->T('validation_page', 'Renew')}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>

    $(document).ready(function (){
        let clone_element = $('.validate-ssl-unified').clone();
        $('.product-details').html(clone_element);
        $('.mg-module .validate-ssl-unified').remove();
    });

    // Contacts / Configurable options / Organization tabs (delegated: the block is moved after load)
    $(document).on('click', '.tssggs-cert [data-tssggs-tab]', function (){
        var card = $(this).closest('.tssggs-tabs'), name = $(this).attr('data-tssggs-tab');
        card.find('[data-tssggs-tab]').removeClass('is-active').attr('aria-selected', 'false');
        $(this).addClass('is-active').attr('aria-selected', 'true');
        card.find('[data-tssggs-pane]').each(function (){
            var on = $(this).attr('data-tssggs-pane') === name;
            $(this).toggleClass('is-active', on).prop('hidden', !on);
        });
    });

</script>
