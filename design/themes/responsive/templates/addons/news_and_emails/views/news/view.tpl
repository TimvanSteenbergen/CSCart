{if $news}
    <div class="ty-news">
        {capture name="tabsbox"}
        <div class="ty-news__item">
            <h3 class="ty-news__title">
                <span class="ty-news__date">{__("date_added")}: {$news.date|date_format:"`$settings.Appearance.date_format`"}</span>
                {$news.news}
            </h3>
            <div class="ty-news__description">
                {$news.description nofilter}
            </div>
        </div>
        {hook name="news:view"}
        {/hook}

        {/capture}
        {include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox active_tab=$smarty.request.selected_section}
    </div>
    {capture name="mainbox_title"}{__("news")}{/capture}
{/if}