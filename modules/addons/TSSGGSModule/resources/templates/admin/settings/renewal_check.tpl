{*
    What the renewal invoice lead and the payment term mean together, in words ($check from SslScreen). Shown as
    saved; resources/assets/js/custom/renewalCheck.js redraws it while the two fields change - every text is given
    here, translated, the script only puts the numbers in.
*}
<span>
    <div class="tssggs-renewal-check" data-type="{$check.type}" data-lead="{$check.lead}" data-term="{$check.term}"
         data-text-before="{$check.texts.before}" data-text-on="{$check.texts.on}" data-text-after="{$check.texts.after}"
         data-text-amber="{$check.texts.amber}" data-text-red="{$check.texts.red}">{$check.body nofilter}</div>
</span>
