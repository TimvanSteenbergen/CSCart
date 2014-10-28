{if $usergroup.usergroup_id}
    {assign var="id" value=$usergroup.usergroup_id}
{else}
    {assign var="id" value=0}
{/if}

<div id="content_group{$id}">

<form action="{""|fn_url}" method="post" name="update_usergroups_form_{$id}" class="form-horizontal form-edit ">
<input type="hidden" name="usergroup_id" value="{$id}" />

{capture name="tabsbox"}
    <div id="content_general_{$id}">
        <div class="control-group">
            <label class="control-label cm-required" for="elm_usergroup_{$id}">{__("usergroup")}</label>
            <div class="controls">
                <input type="text" id="elm_usergroup_{$id}" name="usergroup_data[usergroup]" size="35" value="{$usergroup.usergroup}" class="input-medium" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="elm_usergroup_type_{$id}">{__("type")}</label>
            <div class="controls">
                {if $smarty.const.RESTRICTED_ADMIN == 1}
                <input type="hidden" name="usergroup_data[type]" value="C" />
                {__("customer")}
                {else}
                <select id="elm_usergroup_type_{$id}" name="usergroup_data[type]">
                    <option value="C"{if $usergroup.type == "C"} selected="selected"{/if}>{__("customer")}</option>
                    <option value="A"{if $usergroup.type == "A"} selected="selected"{/if}>{__("administrator")}</option>
                </select>
                {/if}
            </div>
        </div>

        {include file="common/select_status.tpl" input_name="usergroup_data[status]" id="usergroup_data_`$id`" obj=$usergroup hidden=true}
    </div>
    
    {if $usergroup.type == "A"}
        <div id="content_privilege_{$id}">
            <input type="hidden" name="usergroup_data[privileges]" value="" />
            <table width="100%" class="table table-middle table-group">
            <thead>
            <tr>
                <th width="1%" class="table-group-checkbox">
                    {include file="common/check_items.tpl"}</th>
                <th width="100%" colspan="5">{__("privilege")}</th>
            </tr>
            </thead>
            {foreach from=$privileges item=privilege}
            <tr class="table-group-header">
                <td colspan="6">{$privilege.0.section}</td>
            </tr>

            {split data=$privilege size=3 assign="splitted_privilege"}
            {math equation="floor(100/x)" x=3 assign="cell_width"}
            {foreach from=$splitted_privilege item=sprivilege}
            <tr class="object-group-elements">
                {foreach from=$sprivilege item="p"}
                    {if $p && $p.description}
                        {assign var="pr_id" value=$p.privilege}
                        <td width="1%" class="table-group-checkbox">
                            <input type="checkbox" name="usergroup_data[privileges][{$pr_id}]" value="Y" {if $usergroup_privileges.$pr_id}checked="checked"{/if} class="checkbox cm-item" id="set_privileges_{$id}_{$pr_id}" /></td>
                        <td width="{$cell_width}%"><label for="set_privileges_{$id}_{$pr_id}">{$p.description}</label></td>
                    {else}
                        <td colspan="2">&nbsp;</td>
                    {/if}
                {/foreach}
            </tr>
            {/foreach}
            {/foreach}
            </table>
        </div>
    {/if}
    {hook name="usergroups:tabs_content"}{/hook}
{/capture}
{include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox}

<div class="buttons-container">
    {include file="buttons/save_cancel.tpl" but_name="dispatch[usergroups.update]" cancel_action="close" save=$id}
</div>

</form>
<!--content_group{$id}--></div>