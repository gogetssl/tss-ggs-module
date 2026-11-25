{* Automation Queue page: what the list is, then the list itself (reloaded by htmx) *}
{if $showAbout}
    <span class="tssggs-html">
        <div class="tssggs-explain">
            <span class="tssggs-explain__icon">{icon name='information-outline'}</span>
            <div>
                <h3>{t key='automation_queue_container.about_title'}</h3>
                <p>{t key='automation_queue_container.about_intro'}</p>
                <ul>
                    <li>{t key='automation_queue_container.about_validate'}</li>
                    <li>{t key='automation_queue_container.about_install'}</li>
                    <li>{t key='automation_queue_container.about_reissue'}</li>
                    <li>{t key='automation_queue_container.about_status'}</li>
                    <li>{t key='automation_queue_container.about_error'}</li>
                </ul>
            </div>
        </div>
    </span>
{/if}

<div id="tssggs-queue">
    {include file='admin/automationQueue/table.tpl' flash=null}
</div>
