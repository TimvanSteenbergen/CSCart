{if $item.type == "news"}
    <a href="{"news.update?news_id=`$item.content.id`"|fn_url}">{$item.content.news}</a><br>                        
{/if}