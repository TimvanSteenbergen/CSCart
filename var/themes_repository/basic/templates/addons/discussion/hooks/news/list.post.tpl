{assign var="discussion" value=$n.news_id|fn_get_discussion:"N"}

{if $discussion && $discussion.type != "D"}
    <p><a href="{"news.view?news_id=`$n.news_id`"|fn_url}">{__("more_w_ellipsis")}</a></p>
{/if}