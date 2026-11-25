{*
    A dialog with a form. $title and $submit are escaped HTML (from {t}).
    A page's dialog template is:

        {include file='admin/partials/dialog_start.tpl' title=... action={url action='save'} target='#tssggs-products'}
            ... the fields ...
        {include file='admin/partials/dialog_end.tpl'}

    A button opens it:  hx-get="{url action='edit' id=...}" hx-target="#tssggs-dialog" hx-swap="innerHTML"
    The form posts to $action (escaped by {url}); what comes back goes into $target, and the dialog closes when the
    action answers with ->closesDialog(). An action that fails answers with the dialog again (->intoDialog()), with
    $error set.  $size: sm | lg | xlg (default: medium).  $submit: text of the green button.
*}
<div class="mod-modal show mod-modal--zoom{if $size|default:'' !== ''} mod-modal--{$size}{/if}" data-dialog>
    <div class="mod-modal-frame">
        <div class="mod-modal-sheet">
            <div class="mod-modal-head mod-bar">
                <div class="mod-bar-title mod-text-md"><span class="mod-text-dim mod-text-regular">{$title nofilter}</span></div>
                <div class="mod-bar-tools">
                    <button type="button" class="mod-btn mod-btn--xs mod-btn--default mod-btn--icon mod-btn--link mod-btn--plain mod-modal-close" aria-label="{t key='admin.kernel.cancel'}" data-dialog-close>
                        <span class="mod-btn-icon tssggs-icobox">{icon name='close'}</span>
                    </button>
                </div>
            </div>
            <div class="mod-modal-body mod-solo">
                <form id="tssggs-dialog-form" method="post" action="{$action nofilter}" class="mod-w-full"
                      hx-post="{$action nofilter}" hx-target="{$target}" hx-swap="innerHTML">
                    <input type="hidden" name="token" value="{$token}">
                    {if $error|default:'' !== ''}<div class="tssggs-note is-bad">{$error}</div>{/if}
