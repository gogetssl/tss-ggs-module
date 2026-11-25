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
<div class="lu-modal show modal--zoomIn{if $size|default:'' !== ''} lu-modal--{$size}{/if}" data-dialog>
    <div class="lu-modal__dialog">
        <div class="lu-modal__content">
            <div class="lu-modal__top lu-top">
                <div class="lu-top__title lu-type-6"><span class="lu-text-faded lu-font-weight-normal">{$title nofilter}</span></div>
                <div class="lu-top__toolbar">
                    <button type="button" class="lu-btn lu-btn--xs lu-btn--default lu-btn--icon lu-btn--link lu-btn--plain closeModal" aria-label="{t key='admin.kernel.cancel'}" data-dialog-close>
                        <span class="lu-btn__icon tssggs-icobox">{icon name='close'}</span>
                    </button>
                </div>
            </div>
            <div class="lu-modal__body lu-single-element">
                <form id="tssggs-dialog-form" method="post" action="{$action nofilter}" class="lu-w-100"
                      hx-post="{$action nofilter}" hx-target="{$target}" hx-swap="innerHTML">
                    <input type="hidden" name="token" value="{$token}">
                    {if $error|default:'' !== ''}<div class="tssggs-note is-bad">{$error}</div>{/if}
