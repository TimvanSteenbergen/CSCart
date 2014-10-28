{** block-description:news **}
{if $items}
<div class="ty-news-sidebox">
    <ul class="ty-news-sidebox__list">
{foreach from=$items item="news" name="site_news"}
        <li class="ty-news-sidebox__item">
            <div class="ty-news-sidebox__item-date">{$news.date|date_format:$settings.Appearance.date_format}</div>
            <a href="{"news.view?news_id=`$news.news_id`"|fn_url}">{$news.news}</a>
        </li>
{/foreach}
    </ul>
    <div class="ty-mtb-xs">
        {include file="buttons/button.tpl" but_href="news.list" but_text=__("view_all") but_role="text" but_meta="ty-news-sidebox__button"}
    </div>
</div>
{/if}