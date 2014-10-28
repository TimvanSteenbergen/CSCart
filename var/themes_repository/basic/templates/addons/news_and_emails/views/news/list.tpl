<div class="news">
{if $news}
    {include file="common/pagination.tpl"}
{foreach from=$news item=n}
<div class="news-item">
<a name="{$n.news_id}"></a>
<h1><span>{__("date_added")}: {$n.date|date_format:"`$settings.Appearance.date_format`"}</span>
    {$n.news}
</h1>
<div class="news-content">
{if $n.separate == "Y"}
    <a href="{"news.view?news_id=`$n.news_id`"|fn_url}">{__("more_w_ellipsis")}</a>
{else}
    {hook name="news:list"}
        {$n.description nofilter}
    {/hook}
{/if}
</div>
</div>
{/foreach}
{include file="common/pagination.tpl"}
{/if}
{capture name="mainbox_title"}{__("news")}{/capture}
</div>