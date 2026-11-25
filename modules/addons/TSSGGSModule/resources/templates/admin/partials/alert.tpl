{* Information box above a page's content ($text: escaped HTML, e.g. from {t}; $type: info | success | warning | danger, default info) *}
<div class="lu-alert lu-alert--faded lu-alert--{$type|default:'info'}" data-component="alert">
    <div class="lu-alert__body">{$text nofilter}</div>
</div>
