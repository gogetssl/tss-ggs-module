<div class="validate-ssl-unified tssggs-cert tssggs-contacts">
    {foreach $contacts as $contact}
        <section class="tssggs-card">
            <h4 class="tssggs-card__title">
                <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                {$contact.title|escape}
            </h4>
            <dl class="tssggs-dl">
                {foreach $contact.fields as $field => $value}
                    <dt>{$field|replace:'_':' '|capitalize|escape}</dt><dd>{$value|escape}</dd>
                {/foreach}
            </dl>
        </section>
    {foreachelse}
        <section class="tssggs-card">
            <p class="tssggs-note tssggs-note--plain">-</p>
        </section>
    {/foreach}

    <div class="tssggs-actions">
        <a class="tssggs-btn" href="clientarea.php?action=productdetails&amp;id={$serviceid}">
            <svg class="tssggs-ic" viewBox="0 0 24 24" aria-hidden="true"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
            {$LANG.clientareabacklink|default:'Back'}
        </a>
    </div>
</div>
