{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="pages_tree_form">
<input type="hidden" name="redirect_url" value="{$config.current_url}" />
{assign var="come_from" value=$smarty.request.page_type}
{include file="common/pagination.tpl" save_current_page=true save_current_url=true div_id=$smarty.request.content_id}

<div class="items-container multi-level">
    {include file="views/pages/components/pages_tree.tpl" header=true combination_suffix="_list"}
</div>

{include file="common/pagination.tpl" div_id=$smarty.request.content_id}

{assign var="rev" value=$smarty.request.content_id|default:"pagination_contents"}

{capture name="adv_buttons"}
    {capture name="tools_list"}
        {foreach from=$page_types key="_k" item="_p"}
            <li>{btn type="list" text=__($_p.add_name) href="pages.add?page_type=`$_k`&come_from=`$come_from`"}</li>
        {/foreach}
    {/capture}
    {dropdown content=$smarty.capture.tools_list icon="icon-plus" no_caret=true placement="right"}
{/capture}

{capture name="buttons"}
    {if $pages_tree}
        {capture name="tools_list"}
            <li>{btn type="list" text=__("clone_selected") dispatch="dispatch[pages.m_clone]" form="pages_tree_form"}</li>
            <li>{btn type="delete_selected" dispatch="dispatch[pages.m_delete]" form="pages_tree_form"}</li>
        {/capture}
        {dropdown content=$smarty.capture.tools_list}
        {include file="buttons/save.tpl" but_name="dispatch[pages.m_update]" but_role="submit-link" but_target_form="pages_tree_form"}
    {/if}
{/capture}
</form>
{/capture}

{capture name="sidebar"}
    {include file="common/saved_search.tpl" dispatch="pages.manage" view_type="pages"}
    {include file="views/pages/components/pages_search_form.tpl" dispatch="pages.manage"}
{/capture}

{include file="common/mainbox.tpl" title=__("content") content=$smarty.capture.mainbox  buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons sidebar=$smarty.capture.sidebar content_id="manage_pages"}