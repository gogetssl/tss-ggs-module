{* Logs page: what the log is, then the cards and the list (list.tpl; put back into #tssggs-logs after a change) *}
{if $showAbout}
    <span class="tssggs-html">
        <div class="tssggs-explain">
            <span class="tssggs-explain__icon">{icon name='information-outline'}</span>
            <div>
                <h3>{t key='aboutTitle'}</h3>
                <p>{t key='aboutIntro'}</p>
                <ul>
                    <li>{t key='aboutApi'}</li>
                    <li>{t key='aboutError'}</li>
                    <li>{t key='aboutDetails'}</li>
                    <li>{t key='aboutRelated'}</li>
                    <li>{t key='aboutLevels'}</li>
                    <li>{t key='aboutSettings'}</li>
                </ul>
            </div>
        </div>
    </span>
{/if}

<div id="tssggs-logs">
    {include file='admin/logs/list.tpl'}
</div>
