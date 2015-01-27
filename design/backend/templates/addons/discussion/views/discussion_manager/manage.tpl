{capture name="mainbox"}

{capture name="tabsbox"}
<div id="content_{$discussion_object_types.$discussion_object_type}">

{if $discussion_object_type == 'E' && $addons.discussion.home_page_testimonials == 'D'}
    {__("text_enabled_testimonials_notice")}
{/if}

<form action="{""|fn_url}" method="POST" name="update_posts_form_{$discussion_object_type|lower}">
<input type="hidden" name="redirect_url" value="{"discussion_manager.manage?selected_section=`$discussion_object_types.$discussion_object_type`"|fn_url}">
{include file="common/pagination.tpl" save_current_page=true save_current_url=true div_id="pagination_contents_`$discussion_object_type`"}

{if $posts}

{script src="js/addons/discussion/discussion.js"}
<div class="posts-container">
{foreach from=$posts item=post}
    <div class="post-item">
        {hook name="discussion_manager:items_list_row"}
            {include file="addons/discussion/views/discussion_manager/components/post.tpl" post=$post type=$post.type show_object_link=true allow_save=true}
        {/hook}
    </div>
{/foreach}
</div>
{else}
<p class="no-items">{__("no_data")}</p>
{/if}

{include file="common/pagination.tpl" div_id="pagination_contents_`$discussion_object_type`"}

{if $posts}
{capture name="adv_buttons"}
    {if $discussion_object_type == 'E'}
        {if $addons.discussion.home_page_testimonials != 'D'}
            {include file="buttons/button.tpl" title=__("add_post") but_icon="icon-plus" but_role="action" but_href="discussion.update?discussion_type=E#add_new_post"}
        {/if}
    {/if}
{/capture}
{/if}

{capture name="buttons"}
    {if $posts}
        {capture name="tools_list"}
            <li>{btn type="delete_selected" dispatch="dispatch[discussion.m_delete]" form="update_posts_form_{$discussion_object_type|lower}"}</li>
        {/capture}
        {dropdown content=$smarty.capture.tools_list}

        {include file="buttons/save.tpl" but_name="dispatch[discussion.update]" but_role="submit-link" but_target_form="update_posts_form_`$discussion_object_type|lower`"}
    {/if}
{/capture}

{capture name="sidebar"}
    {include file="common/saved_search.tpl" dispatch="discussion_manager.manage" view_type="discussion"}
    {include file="addons/discussion/views/discussion_manager/components/discussion_search_form.tpl"}
{/capture}

</form>

<!--content_{$discussion_object_types.$discussion_object_type}--></div>

{/capture}
{include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox active_tab=$discussion_object_types.$discussion_object_type track=true}

{/capture}
{include file="common/mainbox.tpl" title=__("discussion") content=$smarty.capture.mainbox title_extra=$smarty.capture.title_extra sidebar=$smarty.capture.sidebar buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons}
