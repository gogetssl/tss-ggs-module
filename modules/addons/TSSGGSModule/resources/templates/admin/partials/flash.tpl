{* The message an action left for this view ($flash: text, tone ok | warn | bad) *}
{if $flash}
    <div class="tssggs-note is-{$flash.tone}">{$flash.text}</div>
{/if}
