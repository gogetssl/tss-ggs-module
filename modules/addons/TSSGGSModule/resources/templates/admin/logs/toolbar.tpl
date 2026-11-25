{*
    Buttons on the right of the log search: Clean log, and the "Actions" menu (Settings, Export CSV, Delete).
    The menu opens on a click and closes on a click anywhere else (Alpine).
*}
<button type="button" class="mod-btn mod-btn--danger mod-text-white" hx-post="{url action='clean'}" hx-target="#tssggs-logs" hx-swap="innerHTML"
        hx-confirm="{t key='cleanConfirm'}" data-confirm-title="{t key='clean'}">
    <span class="mod-btn-icon tssggs-icobox">{icon name='delete'}</span><span class="mod-btn-text">{t key='clean'}</span>
</button>
<div class="mod-has-menu mod-flex-inline tssggs-menu" x-data="{ open: false }" @click.outside="open = false" @keydown.escape="open = false">
    <button type="button" class="mod-btn mod-btn--icon mod-btn--link mod-btn--default" aria-label="{t key='actions'}" data-title="{t key='actions'}" @click="open = !open" :aria-expanded="open">
        <span class="mod-btn-icon tssggs-icobox">{icon name='dots-vertical'}</span>
    </button>
    <div class="mod-pop mod-pop-owner mod-pop--on mod-pop--open tssggs-menu__drop" x-show="open" x-cloak>
        <div class="mod-pop-body">
            <div class="mod-menu">
                <ul class="mod-menu-list" @click="open = false">
                    <li class="mod-menu-head"><span class="mod-menu-title">{t key='actions'}</span></li>
                    <li class="mod-menu-item mod-btn-plain">
                        <a class="mod-menu-link mod-btn-plain" href="#" hx-get="{url action='settings'}" hx-target="#tssggs-dialog" hx-swap="innerHTML">
                            <span class="mod-menu-icon tssggs-icobox">{icon name='cog'}</span><span class="mod-menu-text">{t key='settings'}</span>
                        </a>
                    </li>
                    <li class="mod-menu-item mod-btn-plain">
                        <a class="mod-menu-link mod-btn-plain" href="#" hx-get="{url action='export'}" hx-target="#tssggs-dialog" hx-swap="innerHTML">
                            <span class="mod-menu-icon tssggs-icobox">{icon name='upload'}</span><span class="mod-menu-text">{t key='exportCsv'}</span>
                        </a>
                    </li>
                    <li class="mod-menu-item mod-btn-plain">
                        <a class="mod-menu-link mod-btn-plain" href="#" hx-get="{url action='prune'}" hx-target="#tssggs-dialog" hx-swap="innerHTML">
                            <span class="mod-menu-icon tssggs-icobox">{icon name='delete'}</span><span class="mod-menu-text">{t key='delete'}</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
