{*
    Buttons on the right of the log search: Clean log, and the "Actions" menu (Settings, Export CSV, Delete).
    The menu opens on a click and closes on a click anywhere else (Alpine).
*}
<button type="button" class="lu-btn lu-btn--danger lu-text-white" hx-post="{url action='clean'}" hx-target="#tssggs-logs" hx-swap="innerHTML"
        hx-confirm="{t key='cleanConfirm'}" data-confirm-title="{t key='clean'}">
    <span class="lu-btn__icon tssggs-icobox">{icon name='delete'}</span><span class="lu-btn__text">{t key='clean'}</span>
</button>
<div class="lu-has-dropdown lu-d-inline-flex tssggs-menu" x-data="{ open: false }" @click.outside="open = false" @keydown.escape="open = false">
    <button type="button" class="lu-btn lu-btn--icon lu-btn--link lu-btn--default" aria-label="{t key='actions'}" data-title="{t key='actions'}" @click="open = !open" :aria-expanded="open">
        <span class="lu-btn__icon tssggs-icobox">{icon name='dots-vertical'}</span>
    </button>
    <div class="drop lu-drop-element drop-enabled drop-open drop-after-open tssggs-menu__drop" x-show="open" x-cloak>
        <div class="drop-content">
            <div class="lu-dropdown">
                <ul class="lu-dropdown__menu" @click="open = false">
                    <li class="lu-dropdown__header"><span class="lu-dropdown__title">{t key='actions'}</span></li>
                    <li class="lu-dropdown__item lu-text-button">
                        <a class="lu-dropdown__link lu-text-button" href="#" hx-get="{url action='settings'}" hx-target="#tssggs-dialog" hx-swap="innerHTML">
                            <span class="lu-dropdown__link-icon tssggs-icobox">{icon name='cog'}</span><span class="lu-dropdown__link-text">{t key='settings'}</span>
                        </a>
                    </li>
                    <li class="lu-dropdown__item lu-text-button">
                        <a class="lu-dropdown__link lu-text-button" href="#" hx-get="{url action='export'}" hx-target="#tssggs-dialog" hx-swap="innerHTML">
                            <span class="lu-dropdown__link-icon tssggs-icobox">{icon name='upload'}</span><span class="lu-dropdown__link-text">{t key='exportCsv'}</span>
                        </a>
                    </li>
                    <li class="lu-dropdown__item lu-text-button">
                        <a class="lu-dropdown__link lu-text-button" href="#" hx-get="{url action='prune'}" hx-target="#tssggs-dialog" hx-swap="innerHTML">
                            <span class="lu-dropdown__link-icon tssggs-icobox">{icon name='delete'}</span><span class="lu-dropdown__link-text">{t key='delete'}</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
