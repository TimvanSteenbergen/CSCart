{capture name="mainbox"}
    <form action="{""|fn_url}" method="POST" class=" form-horizontal {if $discussion.object_type == "M" && $runtime.company_id}cm-hide-inputs{/if}" name="update_posts_form">
    <input type="hidden" name="redirect_url" value="{$config.current_url}&amp;selected_section=discussion" />
    <input type="hidden" name="selected_section" value="" />
        {include file="addons/discussion/views/discussion_manager/components/discussion.tpl"}
    </form>
{/capture}

{capture name="buttons"}
    {$smarty.capture.buttons_insert nofilter}
    {if $discussion.posts}
        {include file="buttons/save.tpl" but_name="dispatch[discussion.update]" but_role="submit-link" but_target_form="update_posts_form"}
    {/if}
{/capture}

{capture name="adv_buttons"}
    {$smarty.capture.adv_buttons nofilter}
{/capture}

{include file="common/mainbox.tpl" title=__("discussion_title_home_page") content=$smarty.capture.mainbox adv_buttons=$smarty.capture.adv_buttons buttons=$smarty.capture.buttons}