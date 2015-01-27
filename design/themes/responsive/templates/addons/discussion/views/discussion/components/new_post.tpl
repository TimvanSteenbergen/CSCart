<div class="ty-discussion-post-popup hidden" id="new_post_dialog_{$obj_prefix}{$obj_id}" title="{$new_post_title}">
<form action="{""|fn_url}" method="post" class="{if !$post_redirect_url}cm-ajax cm-form-dialog-closer{/if} posts-form" name="add_post_form" id="add_post_form_{$obj_prefix}{$obj_id}">
<input type="hidden" name="result_ids" value="posts_list,new_post,average_rating*">
<input type ="hidden" name="post_data[thread_id]" value="{$discussion.thread_id}" />
<input type ="hidden" name="redirect_url" value="{$post_redirect_url|default:$config.current_url}" />
<input type="hidden" name="selected_section" value="" />

<div id="new_post_{$obj_prefix}{$obj_id}">

<div class="ty-control-group">
    <label for="dsc_name_{$obj_prefix}{$obj_id}" class="ty-control-group__title cm-required">{__("your_name")}</label>
    <input type="text" id="dsc_name_{$obj_prefix}{$obj_id}" name="post_data[name]" value="{if $auth.user_id}{$user_info.firstname} {$user_info.lastname}{elseif $discussion.post_data.name}{$discussion.post_data.name}{/if}" size="50" class="ty-input-text-large" />
</div>

{if $discussion.type == "R" || $discussion.type == "B"}
<div class="ty-control-group">
    {$rate_id = "rating_`$obj_prefix``$obj_id`"}
    <label class="ty-control-group__title cm-required cm-multiple-radios">{__("your_rating")}</label>
    {include file="addons/discussion/views/discussion/components/rate.tpl" rate_id=$rate_id rate_name="post_data[rating_value]"}
</div>
{/if}

{hook name="discussion:add_post"}
{if $discussion.type == "C" || $discussion.type == "B"}
<div class="ty-control-group">
    <label for="dsc_message_{$obj_prefix}{$obj_id}" class="ty-control-group__title cm-required">{__("your_message")}</label>
    <textarea id="dsc_message_{$obj_prefix}{$obj_id}" name="post_data[message]" class="ty-input-textarea ty-input-text-large" rows="5" cols="72">{$discussion.post_data.message}</textarea>
</div>
{/if}
{/hook}

{include file="common/image_verification.tpl" option="use_for_discussion"}

<!--new_post_{$obj_prefix}{$obj_id}--></div>

<div class="buttons-container">
    {include file="buttons/button.tpl" but_text=__("submit") but_meta="ty-btn__secondary" but_role="submit" but_name="dispatch[discussion.add]"}
</div>

</form>
</div>
