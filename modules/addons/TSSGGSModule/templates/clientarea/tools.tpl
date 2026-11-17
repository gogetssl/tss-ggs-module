<div class="tssggs-client tssggs-tools">
    {* texts: $t = $_LANG['sslTools'] of the server module language files (already HTML-encoded) *}
    <p class="text-muted">{$t.intro}</p>

    {if $error}<div class="alert alert-danger">{$error|escape}</div>{/if}

    {* ---------- CSR decoder ---------- *}
    <div class="panel panel-default">
        <div class="panel-heading"><h3 class="panel-title">{$t.csrDecoder}</h3></div>
        <div class="panel-body">
            <form method="post" action="{$moduleUrl}&amp;page=tools">
                <input type="hidden" name="token" value="{$token}"><input type="hidden" name="action" value="decodeCsr">
                <textarea name="csr" class="form-control" rows="6" placeholder="-----BEGIN CERTIFICATE REQUEST-----">{if $active == 'decodeCsr'}{$form.csr|escape}{/if}</textarea>
                <button type="submit" class="btn btn-primary" style="margin-top:8px;">{$t.decode}</button>
            </form>
            {if $active == 'decodeCsr' && $result}
                <table class="table table-condensed" style="margin-top:12px;">
                    {foreach $result.subject as $key => $value}<tr><th style="width:30%">{$key|escape}</th><td>{$value|escape}</td></tr>{/foreach}
                    <tr><th>{$t.sans}</th><td>{if $result.sans}{foreach $result.sans as $san}{$san|escape}<br>{/foreach}{else}-{/if}</td></tr>
                    <tr><th>{$t.key}</th><td>{$result.keyType|escape} {$result.keyBits|escape} {$t.bit}</td></tr>
                </table>
            {/if}
        </div>
    </div>

    {* ---------- certificate decoder ---------- *}
    <div class="panel panel-default">
        <div class="panel-heading"><h3 class="panel-title">{$t.certDecoder}</h3></div>
        <div class="panel-body">
            <form method="post" action="{$moduleUrl}&amp;page=tools">
                <input type="hidden" name="token" value="{$token}"><input type="hidden" name="action" value="decodeCert">
                <textarea name="certificate" class="form-control" rows="6" placeholder="-----BEGIN CERTIFICATE-----">{if $active == 'decodeCert'}{$form.certificate|escape}{/if}</textarea>
                <button type="submit" class="btn btn-primary" style="margin-top:8px;">{$t.decode}</button>
            </form>
            {if $active == 'decodeCert' && $result}
                <table class="table table-condensed" style="margin-top:12px;">
                    <tr><th style="width:30%">{$t.subject}</th><td>{foreach $result.subject as $key => $value}{$key|escape}={$value|escape} {/foreach}</td></tr>
                    <tr><th>{$t.issuer}</th><td>{foreach $result.issuer as $key => $value}{$key|escape}={$value|escape} {/foreach}</td></tr>
                    <tr><th>{$t.names}</th><td>{if $result.sans}{foreach $result.sans as $san}{$san|escape}<br>{/foreach}{else}-{/if}</td></tr>
                    <tr><th>{$t.valid}</th><td>{$result.validFrom|escape} - {$result.validTo|escape} ({$t.daysLeft|replace:':days:':($result.daysLeft|intval)})</td></tr>
                    <tr><th>{$t.keySignature}</th><td>{$result.keyType|escape} {$result.keyBits|escape} {$t.bit}, {$result.signature|escape}</td></tr>
                    <tr><th>{$t.serial}</th><td><code>{$result.serial|escape}</code></td></tr>
                    <tr><th>SHA-1</th><td><code>{$result.sha1|escape}</code></td></tr>
                    <tr><th>SHA-256</th><td><code>{$result.sha256|escape}</code></td></tr>
                </table>
            {/if}
        </div>
    </div>

    {* ---------- key match ---------- *}
    <div class="panel panel-default">
        <div class="panel-heading"><h3 class="panel-title">{$t.keyMatch}</h3></div>
        <div class="panel-body">
            <p class="text-muted">{$t.keyMatchIntro}</p>
            <form method="post" action="{$moduleUrl}&amp;page=tools" autocomplete="off">
                <input type="hidden" name="token" value="{$token}"><input type="hidden" name="action" value="match">
                <div class="row">
                    <div class="col-md-4"><textarea name="certificate" class="form-control" rows="5" placeholder="{$t.certificatePem}">{if $active == 'match'}{$form.certificate|escape}{/if}</textarea></div>
                    <div class="col-md-4"><textarea name="csr" class="form-control" rows="5" placeholder="{$t.csrPem}">{if $active == 'match'}{$form.csr|escape}{/if}</textarea></div>
                    <div class="col-md-4"><textarea name="key" class="form-control" rows="5" placeholder="{$t.keyPem}"></textarea></div>
                </div>
                <button type="submit" class="btn btn-primary" style="margin-top:8px;">{$t.compare}</button>
            </form>
            {if $active == 'match' && $result}
                <div class="alert {if $result.match}alert-success{else}alert-danger{/if}" style="margin-top:12px;">
                    {if $result.match}{$t.match}{else}{$t.noMatch}{/if}
                    <ul style="margin:8px 0 0;">{foreach $result.items as $label => $fingerprint}<li>{$label|escape}: <code>{$fingerprint|truncate:16:'...'|escape}</code></li>{/foreach}</ul>
                </div>
            {/if}
        </div>
    </div>

    {* ---------- converter ---------- *}
    <div class="panel panel-default">
        <div class="panel-heading"><h3 class="panel-title">{$t.converter}</h3></div>
        <div class="panel-body">
            <form method="post" action="{$moduleUrl}&amp;page=tools" enctype="multipart/form-data" autocomplete="off">
                <input type="hidden" name="token" value="{$token}"><input type="hidden" name="action" value="convert">
                <div class="form-group">
                    <select name="mode" class="form-control" id="tssggs-convert-mode">
                        <option value="pem2der">{$t.pem2der}</option>
                        <option value="der2pem">{$t.der2pem}</option>
                        <option value="pem2pfx">{$t.pem2pfx}</option>
                        <option value="pfx2pem">{$t.pfx2pem}</option>
                    </select>
                </div>
                <div class="form-group"><label>{$t.file}</label><input type="file" name="file" class="form-control"></div>
                <div class="form-group"><textarea name="certificate" class="form-control" rows="5" placeholder="{$t.certificatePem}"></textarea></div>
                <div class="form-group"><textarea name="chain" class="form-control" rows="3" placeholder="{$t.chainPem}"></textarea></div>
                <div class="form-group"><textarea name="key" class="form-control" rows="4" placeholder="{$t.keyPfx}"></textarea></div>
                <div class="form-group"><input type="password" name="password" class="form-control" placeholder="{$t.pfxPassword}" autocomplete="new-password"></div>
                <button type="submit" class="btn btn-primary">{$t.convert}</button>
            </form>
        </div>
    </div>

    {* ---------- CSR generator ---------- *}
    <div class="panel panel-default">
        <div class="panel-heading"><h3 class="panel-title">{$t.generator}</h3></div>
        <div class="panel-body">
            <p class="text-muted">{$t.generatorIntro}</p>
            <form method="post" action="{$moduleUrl}&amp;page=tools" autocomplete="off">
                <input type="hidden" name="token" value="{$token}"><input type="hidden" name="action" value="generate">
                <div class="row">
                    <div class="col-md-6 form-group"><label>{$t.commonName} *</label><input name="commonName" class="form-control" value="{if $active == 'generate'}{$form.commonName|escape}{/if}" placeholder="www.example.com"></div>
                    <div class="col-md-6 form-group"><label>{$t.organization}</label><input name="organization" class="form-control" value="{if $active == 'generate'}{$form.organization|escape}{/if}"></div>
                    <div class="col-md-6 form-group"><label>{$t.department}</label><input name="unit" class="form-control" value="{if $active == 'generate'}{$form.unit|escape}{/if}"></div>
                    <div class="col-md-6 form-group"><label>{$t.city}</label><input name="city" class="form-control" value="{if $active == 'generate'}{$form.city|escape}{/if}"></div>
                    <div class="col-md-4 form-group"><label>{$t.state}</label><input name="state" class="form-control" value="{if $active == 'generate'}{$form.state|escape}{/if}"></div>
                    <div class="col-md-2 form-group"><label>{$t.country}</label><input name="country" class="form-control" maxlength="2" value="{if $active == 'generate'}{$form.country|escape}{/if}"></div>
                    <div class="col-md-4 form-group"><label>{$t.email}</label><input name="email" class="form-control" value="{if $active == 'generate'}{$form.email|escape}{/if}"></div>
                    <div class="col-md-2 form-group"><label>{$t.keySize}</label>
                        <select name="bits" class="form-control"><option value="2048">2048</option><option value="3072">3072</option><option value="4096">4096</option></select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">{$t.generate}</button>
            </form>
            {if $active == 'generate' && $result}
                <div class="row" style="margin-top:12px;">
                    <div class="col-md-6"><label>{$t.csr}</label><textarea class="form-control" rows="10" readonly>{$result.csr|escape}</textarea></div>
                    <div class="col-md-6"><label>{$t.privateKeySave}</label><textarea class="form-control" rows="10" readonly>{$result.key|escape}</textarea></div>
                </div>
            {/if}
        </div>
    </div>

    {* ---------- SSL checker ---------- *}
    <div class="panel panel-default">
        <div class="panel-heading"><h3 class="panel-title">{$t.checker}</h3></div>
        <div class="panel-body">
            <form method="post" action="{$moduleUrl}&amp;page=tools" class="form-inline">
                <input type="hidden" name="token" value="{$token}"><input type="hidden" name="action" value="check">
                <input name="host" class="form-control" value="{$form.host|escape}" placeholder="www.example.com" style="min-width:260px;">
                <select name="port" class="form-control">{foreach [443, 8443, 465, 993, 995] as $port}<option value="{$port}"{if $form.port == $port} selected{/if}>{$port}</option>{/foreach}</select>
                <button type="submit" class="btn btn-primary">{$t.check}</button>
            </form>
            {if $active == 'check' && $result}
                <div class="alert {if $result.hostMatches}alert-success{else}alert-warning{/if}" style="margin-top:12px;">
                    {$result.host|escape}:{$result.port|escape} ({$result.address|escape}) -
                    {if $result.hostMatches}{$t.covers}{else}{$t.notCovers}{/if}
                </div>
                {foreach $result.chain as $index => $certificate}
                    <table class="table table-condensed">
                        <tr><th style="width:30%">{if $index == 0}{$t.serverCertificate}{else}{$t.chainCertificate|replace:':n:':$index}{/if}</th><td>{foreach $certificate.subject as $key => $value}{$key|escape}={$value|escape} {/foreach}</td></tr>
                        <tr><th>{$t.issuer}</th><td>{foreach $certificate.issuer as $key => $value}{$key|escape}={$value|escape} {/foreach}</td></tr>
                        {if $certificate.sans}<tr><th>{$t.names}</th><td>{foreach $certificate.sans as $san}{$san|escape} {/foreach}</td></tr>{/if}
                        <tr><th>{$t.valid}</th><td>{$certificate.validFrom|escape} - {$certificate.validTo|escape} ({$t.daysLeft|replace:':days:':($certificate.daysLeft|intval)})</td></tr>
                    </table>
                {/foreach}
            {/if}
        </div>
    </div>
</div>
