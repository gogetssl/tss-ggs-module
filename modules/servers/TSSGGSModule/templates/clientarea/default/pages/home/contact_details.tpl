<div class="tssggs-contacts">
    <h2>{if isset($LANG.tssggsContactDetails)}{$LANG.tssggsContactDetails}{else}Contact details{/if}</h2>
    {foreach $contacts as $contact}
        <div class="panel panel-default card mb-3">
            <div class="panel-heading card-header"><strong>{$contact.title|escape}</strong></div>
            <table class="table table-striped mb-0">
                {foreach $contact.fields as $field => $value}
                    <tr><th style="width:35%">{$field|replace:'_':' '|capitalize|escape}</th><td>{$value|escape}</td></tr>
                {/foreach}
            </table>
        </div>
    {foreachelse}
        <p>-</p>
    {/foreach}
    <a class="btn btn-default" href="clientarea.php?action=productdetails&amp;id={$serviceid}">{$LANG.clientareabacklink|default:'Back'}</a>
</div>
