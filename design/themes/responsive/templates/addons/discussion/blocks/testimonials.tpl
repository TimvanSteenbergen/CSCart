{** block-description:discussion_title_home_page **}

{assign var="discussion" value=0|fn_get_discussion:"E":true:$block.properties}

{if $discussion && $discussion.type != "D" && $discussion.posts}

{foreach from=$discussion.posts item=post}

<div class="discussion-testimonial-post">
    {if $discussion.type == "C" || $discussion.type == "B"}
        <div class="ty-discussion-post__message">
            <a href="{"discussion.view?thread_id=`$discussion.thread_id`&post_id=`$post.post_id`"|fn_url}#post_{$post.post_id}">"{$post.message|truncate:100|nl2br nofilter}"</a>
        </div>
    {/if}

    <div class="clearfix">
        {if $discussion.type == "R" || $discussion.type == "B"}
            <div class="ty-right">{include file="addons/discussion/views/discussion/components/stars.tpl" stars=$post.rating_value|fn_get_discussion_rating}</div>
        {/if}
    </div>
</div>

{/foreach}

<div class="ty-mtb-s ty-right">
    <a href="{"discussion.view?thread_id=`$discussion.thread_id`"|fn_url}">{__("more_w_ellipsis")}</a>
</div>
{/if}
