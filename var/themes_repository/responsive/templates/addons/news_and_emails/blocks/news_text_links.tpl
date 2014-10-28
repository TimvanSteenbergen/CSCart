{** block-description:text_links **}

{if $items}
<div class="ty-news-text-links">
    <ul>
    {foreach from=$items item="news" name="site_news"}
        <li class="ty-news-text-links__item">
            <div class="ty-news-text-links__date">{$news.date|date_format:$settings.Appearance.date_format}</div>
            <a href="{"news.view?news_id=`$news.news_id`"|fn_url}" class="ty-news-text-links__a">{$news.news}</a>
            {if !$smarty.foreach.site_news.last}
                <hr class="ty-news-text-links__delim" />
            {/if}
        </li>
    {/foreach}
    </ul>

    <div class="ty-mtb-xs ty-right">
        {include file="buttons/button.tpl" but_href="news.list" but_text=__("view_all") but_role="text" but_meta="news-ty-text-links__button"}
    </div>
</div>
{/if}