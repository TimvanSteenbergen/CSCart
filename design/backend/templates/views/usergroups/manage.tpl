{script src="js/tygh/tabs.js"}

{capture name="mainbox"}

{$hide_inputs = ""|fn_check_form_permissions}
<form action="{""|fn_url}" method="post" name="usergroups_form" class="{if $hide_inputs} cm-hide-inputs{/if}">

{hook name="usergroups:manage"}
{if $usergroups}
<table class="table table-middle">
<thead>
<tr>
    <th width="1%">
        {include file="common/check_items.tpl"}</th>
    <th width="20%">{__("usergroup")}</th>
    <th width="45%">{__("type")}</th>
    <th width="5%">&nbsp;</th>
    <th class="right" width="15%">{__("status")}</th>
</tr>
</thead>
{foreach from=$usergroups item=usergroup}
<tr class="cm-row-status-{$usergroup.status|lower}">
    <td width="1%">
        <input type="checkbox" name="usergroup_ids[]" value="{$usergroup.usergroup_id}" class="checkbox cm-item" /></td>
    <td class="row-status">
        {if $hide_inputs}
            {$usergroup.usergroup}
        {else}
            <a class="row-status cm-external-click" data-ca-external-click-id="{"opener_group`$usergroup.usergroup_id`"}">{$usergroup.usergroup}</a>
        {/if}
    </td>
    <td class="row-status">
        {if $usergroup.type == "C"}{__("customer")}{/if}
        {if $usergroup.type == "A"}{__("administrator")}{/if}
    </td>
    <td class="row-status">
        {if $usergroup.type == "A"}
            {assign var="_href" value="usergroups.assign_privileges?usergroup_id=`$usergroup.usergroup_id`"}
            {assign var="_link_text" value=__("privileges")}
        {else}
            {assign var="_href" value=""}
            {assign var="_link_text" value=""}
        {/if}
        {capture name="tools_list"}
            <li>{include file="common/popupbox.tpl" id="group`$usergroup.usergroup_id`" text=$usergroup.usergroup link_text=__("edit") act="link" href="usergroups.update?usergroup_id=`$usergroup.usergroup_id`&group_type=`$usergroup.type`"}</li>
            <li>{btn type="list" text=__("delete") class="cm-confirm" href="usergroups.delete?usergroup_id=`$usergroup.usergroup_id`"}</li>
        {/capture}
        <div class="hidden-tools cm-hide-with-inputs">
            {dropdown content=$smarty.capture.tools_list}
        </div>
    </td>
    <td class="nowrap right">
        {assign var="hide_for_vendor" value=false}
        {if !"usergroups.manage"|fn_check_view_permissions:"POST"}
            {assign var="hide_for_vendor" value=true}
        {/if}
        {include file="common/select_popup.tpl" id=$usergroup.usergroup_id status=$usergroup.status hidden=true object_id_name="usergroup_id" table="usergroups" hide_for_vendor=$hide_for_vendor}
    </td>
</tr>
{/foreach}
</table>
{else}
    <p class="no-items">{__("no_items")}</p>
{/if}
{/hook}

</form>

{capture name="buttons"}
    {if "usergroups.update"|fn_check_view_permissions}
        {capture name="tools_list"}
            <li>{btn type="list" text=__("user_group_requests") href="usergroups.requests"}</li>
            {if $usergroups}
                <li class="divider"></li>
                <li>{btn type="delete_selected" dispatch="dispatch[usergroups.m_delete]" form="usergroups_form"}</li>
            {/if}
        {/capture}
        {dropdown content=$smarty.capture.tools_list}
    {/if}
{/capture}

{capture name="adv_buttons"}
    {if "usergroups.update"|fn_check_view_permissions}
        {capture name="add_new_picker"}
            {include file="views/usergroups/update.tpl" usergroup=[]}
        {/capture}
        {include file="common/popupbox.tpl" id="add_new_usergroups" text=__("new_usergroups") title=__("new_usergroups") content=$smarty.capture.add_new_picker act="general" icon="icon-plus"}
    {/if}
{/capture}

{/capture}
{include file="common/mainbox.tpl" title=__("usergroups") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons select_languages=true}