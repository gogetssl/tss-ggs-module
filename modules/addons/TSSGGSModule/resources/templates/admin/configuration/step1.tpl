{* Wizard page 1 "Welcome": what the module does. $intro and the texts with <b> are the module's own HTML. *}
<span>{$intro nofilter}<br><br></span>
<span>{t key='step1.second_p'}<br><br></span>
<span class="tssggs-html">
    <div class="tssggs-features">
        {foreach [1, 2, 3] as $number}
            <div class="tssggs-feature">
                <div class="tssggs-feature__title">{t key="step1.feature{$number}_title"}</div>
                <div class="tssggs-feature__desc">{t key="step1.feature{$number}_desc"}</div>
            </div>
        {/foreach}
    </div>
</span>
<span>{t key='step1.third_p' html=true}</span>
<div class="lu-row"></div>
<a class="lu-btn lu-btn--success mt30_custom" href="{$next}"><span class="lu-btn__text">{t key='step1.next_step_btn'}</span></a>
