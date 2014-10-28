{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="destinations_form" class="{if ""|fn_check_form_permissions}cm-hide-inputs{/if}">

{if $destinations}
<table width="100%" class="table table-middle">
<thead>
<tr>
    <th width="1%">
        {include file="common/check_items.tpl"}</th>
    <th width="80%">{__("name")}</th>
    <th width="5%">&nbsp;</th>
    <th class="right" width="10%">{__("status")}</th>
</tr>
</thead>
{foreach from=$destinations item=destination}
<tr class="cm-row-status-{$destination.status|lower}" data-ct-destination-id="{$destination.destination_id}">
    <td class="left">
        <input name="destination_ids[]" type="checkbox" value="{$destination.destination_id}" {if $destination.destination_id == 1}disabled="disabled"{/if} class=" cm-item" /></td>
    <td data-ct-destination-name="{$destination.destination}">
       <a class="row-status" href="{"destinations.update?destination_id=`$destination.destination_id`"|fn_url}">{$destination.destination}</a>
    </td>
    <td class="nowrap" >
        {capture name="tools_list"}
            {hook name="destinations:manage_tools_list"}
                <li>{btn type="list" text=__("edit") href="destinations.update?destination_id=`$destination.destination_id`"}</li>
                {if $destination.destination_id != 1}
                    <li>{btn type="list" text=__("delete") class="cm-confirm" href="destinations.delete?destination_id=`$destination.destination_id`"}</li>
                {/if}
            {/hook}
        {/capture}
        <div class="hidden-tools">
        {dropdown content=$smarty.capture.tools_list}
        </div>
    </td>
    <td class="right">
        {$has_permission = fn_check_permissions("tools", "update_status", "admin", "GET", ["table" => "destinations"])}

        {include file="common/select_popup.tpl" id=$destination.destination_id status=$destination.status hidden="" object_id_name="destination_id" table="destinations" non_editable=!$has_permission}
    </td>
</tr>
{/foreach}
</table>
</form>
{else}
    <p class="no-items">{__("no_items")}</p>
{/if}
{/capture}

{capture name="buttons"}
    {if $destinations}
        {capture name="tools_list"}
            <li>{btn type="delete_selected" dispatch="dispatch[destinations.m_delete]" form="destinations_form"}</li>
        {/capture}
        {dropdown content=$smarty.capture.tools_list}
    {/if}
{/capture}

{capture name="adv_buttons"}
    {include file="common/tools.tpl" tool_href="destinations.add" prefix="top" hide_tools="true" title=__("add_location") icon="icon-plus"}
{/capture}

{include file="common/mainbox.tpl" title=__("locations") content=$smarty.capture.mainbox adv_buttons=$smarty.capture.adv_buttons buttons=$smarty.capture.buttons select_languages=true}