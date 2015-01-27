{** block-description:news **}
<div class="news-sidebox">
{if $items}
    <ul class="news-sidebox-list">
{foreach from=$items item="news" name="site_news"}
        <li>
            <p>{$news.date|date_format:$settings.Appearance.date_format}</p>
            <a href="{"news.view?news_id=`$news.news_id`"|fn_url}">{$news.news}</a>
        </li>
{/foreach}
    </ul>
    <p class="left">
        <a href="{"news.list"|fn_url}" class="extra-link"></a>
        {include file="buttons/button.tpl" but_href="news.list" but_text=__("view_all") but_role="text" but_meta="news-viewall"}
    </p>
{/if}
</div>