{if $result.object == "news"}
    {assign var=n value=$result}
    {include file="addons/news_and_emails/views/news/components/one_news.tpl" n=$result}
{/if}