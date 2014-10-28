{assign var="discussion" value=$object_id|fn_get_discussion:$object_type:true:$smarty.request}
{if $object_type == "P"}
{$new_post_title = __("write_review")}
{else}
{$new_post_title = __("new_post")}
{/if}
{if $discussion && $discussion.type != "D"}
<div id="content_discussion" class="discussion-block">
{if $wrap == true}
{capture name="content"}
{include file="common/subheader.tpl" title=$title}
{/if}

{if $subheader}
    <h4>{$subheader}</h4>
{/if}

<div id="posts_list">
{if $discussion.posts}
{include file="common/pagination.tpl" id="pagination_contents_comments_`$object_id`" extra_url="&selected_section=discussion" search=$discussion.search}
{foreach from=$discussion.posts item=post}
<div class="posts{cycle values=", manage-post"}" id="post_{$post.post_id}">
{hook name="discussion:items_list_row"}
        <span class="caret"> <span class="caret-outer"></span> <span class="caret-inner"></span></span>
        <span class="post-author">{$post.name}</span>
        <span class="post-date">{$post.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}</span>

        {if $discussion.type == "R" || $discussion.type == "B" && $post.rating_value > 0}
            <div class="clearfix">
                {include file="addons/discussion/views/discussion/components/stars.tpl" stars=$post.rating_value|fn_get_discussion_rating}
            </div>
        {/if}
        
    
    {if $discussion.type == "C" || $discussion.type == "B"}<p class="post-message">{$post.message|escape|nl2br nofilter}</p>{/if}
    
{/hook}
</div>
{/foreach}


{include file="common/pagination.tpl" id="pagination_contents_comments_`$object_id`" extra_url="&selected_section=discussion" search=$discussion.search}
{else}
<p class="no-items">{__("no_posts_found")}</p>
{/if}
<!--posts_list--></div>

{if "CRB"|strpos:$discussion.type !== false && !$discussion.disable_adding}
<div class="buttons-container">
    {include file="buttons/button.tpl" but_id="opener_new_post" but_text=$new_post_title but_role="submit" but_target_id="new_post_dialog_`$obj_id`" but_meta="cm-dialog-opener cm-dialog-auto-size" but_rel="nofollow"}
</div>

{include file="addons/discussion/views/discussion/components/new_post.tpl" new_post_title=$new_post_title}
{/if}

{if $wrap == true}
    {/capture}
    {$smarty.capture.content nofilter}
{else}
    {capture name="mainbox_title"}{$title}{/capture}
{/if}
</div>
{/if}
