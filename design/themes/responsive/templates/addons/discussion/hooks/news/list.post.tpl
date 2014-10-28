{assign var="discussion" value=$n.news_id|fn_get_discussion:"N"}

{if $discussion && $discussion.type != "D"}
    <div class="ty-mtb-s"><a href="{"news.view?news_id=`$n.news_id`"|fn_url}" class="discussion-news-link">{__("more_w_ellipsis")}</a></div>
{/if}