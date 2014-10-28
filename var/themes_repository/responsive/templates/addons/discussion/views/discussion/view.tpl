{assign var="discussion" value=$object_id|fn_get_discussion:$object_type:true:$smarty.request}
{if $object_type == "P"}
{$new_post_title = __("write_review")}
{else}
{$new_post_title = __("new_post")}
{/if}
{if $discussion && $discussion.type != "D"}
    <div class="discussion-block" id="{if $container_id}{$container_id}{else}content_discussion{/if}">
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
                    <div class="ty-discussion-post__content">
                        {hook name="discussion:items_list_row"}
                        <span class="ty-discussion-post__author">{$post.name}</span>
                        <span class="ty-discussion-post__date">{$post.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}</span>
                        <div class="ty-discussion-post {cycle values=", ty-discussion-post_even"}" id="post_{$post.post_id}">
                            <span class="ty-caret"> <span class="ty-caret-outer"></span> <span class="ty-caret-inner"></span></span>

                            {if $discussion.type == "R" || $discussion.type == "B" && $post.rating_value > 0}
                                <div class="clearfix ty-discussion-post__rating">
                                    {include file="addons/discussion/views/discussion/components/stars.tpl" stars=$post.rating_value|fn_get_discussion_rating}
                                </div>
                            {/if}

                            {if $discussion.type == "C" || $discussion.type == "B"}
                                <div class="ty-discussion-post__message">{$post.message|escape|nl2br nofilter}</div>
                            {/if}
                    
                        </div>
                        {/hook}
                    </div>
                {/foreach}


                {include file="common/pagination.tpl" id="pagination_contents_comments_`$object_id`" extra_url="&selected_section=discussion" search=$discussion.search}
            {else}
                <p class="ty-no-items">{__("no_posts_found")}</p>
            {/if}
        <!--posts_list--></div>

        {if "CRB"|strpos:$discussion.type !== false && !$discussion.disable_adding}
            <div class="ty-discussion-post__buttons buttons-container">
                {include file="buttons/button.tpl" but_id="opener_new_post" but_text=$new_post_title but_role="submit" but_target_id="new_post_dialog_`$obj_id`" but_meta="cm-dialog-opener cm-dialog-auto-size ty-btn__primary" but_rel="nofollow"}
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