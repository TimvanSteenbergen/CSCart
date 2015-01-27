{if $news}
    <div class="ty-news">
        {include file="common/pagination.tpl"}

        {foreach from=$news item=n}
            <div class="ty-news__item">
                <a name="{$n.news_id}"></a>
                <h3 class="ty-news__title"><span class="ty-news__date">{__("date_added")}: {$n.date|date_format:"`$settings.Appearance.date_format`"}</span>
                    {$n.news}
                </h3>
                <div class="ty-news__description">
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

        {capture name="mainbox_title"}{__("news")}{/capture}
    </div>
{/if}