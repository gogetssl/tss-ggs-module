{* Information box above a page's content ($text: escaped HTML, e.g. from {t}; $type: info | success | warning | danger, default info) *}
<div class="mod-alert mod-alert--soft mod-alert--{$type|default:'info'}" data-component="alert">
    <div class="mod-alert-body">{$text nofilter}</div>
</div>
