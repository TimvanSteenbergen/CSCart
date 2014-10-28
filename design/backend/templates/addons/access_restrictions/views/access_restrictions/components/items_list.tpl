{foreach from=$items item="item"}
<tr class="cm-row-status-{$item.status|lower}">
    {hook name="access_restrictions:item_fields"}
    <td class="center row-status" width="1%">
        {if $disable_host_ip}
            {assign var="disable_host_ip" value="N"}
        {/if}
        {if !$disable_host_ip && $addons.access_restrictions.admin_reverse_ip_access == "Y" && $selected_section == "admin_area" && $host_ip == $item.ip_from && $host_ip == $item.ip_to}
            {assign var="disable_host_ip" value="Y"}
        {/if}
        <input type="checkbox" name="item_ids[]" value="{$item.item_id}" {if $disable_host_ip == "Y"}disabled="disabled"{/if} class="cm-item" /></td>
    {if $selected_section == "ip" || $selected_section == "admin_panel"}
    <td width="15%" class="row-status">{$item.ip_from|long2ip}{if $item.ip_from != $item.ip_to} - {$item.ip_to|long2ip}{/if}</td>
    {else}
    <td width="50%" class="wrap row-status">{$item.value}</td>
    {/if}
    <td width="10%"><input type="text" name="items_data[{$item.item_id}][reason]" value="{$item.reason}" class="input-hidden span6" /></td>
    <td width="5%" class="row-status nowrap">{$item.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}</td>
    <td width="5%" class="center nowrap">
        {capture name="tools_list"}
            <li>{btn type="list" class="cm-confirm" text=__("delete") href="access_restrictions.delete?item_id=`$item.item_id`&selected_section=`$selected_section`"}</li>
        {/capture}
        <div class="hidden-tools">
            {dropdown content=$smarty.capture.tools_list}
        </div>
    </td>
    <td width="15%" class="right nowrap">
        <input type="hidden" name="items_data[{$item.item_id}][type]" value="{$item.type}" />
        {include file="common/select_popup.tpl" id=$item.item_id status=$item.status hidden="" object_id_name="item_id" table="access_restriction"}
    </td>
    {/hook}
</tr>
{/foreach}