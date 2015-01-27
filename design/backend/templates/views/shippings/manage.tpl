{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="shippings_form" class="{if ""|fn_check_form_permissions} cm-hide-inputs{/if}">
{if $shippings}
<table width="100%" class="table table-middle">
<thead>
<tr>
    <th width="1%">
        {include file="common/check_items.tpl"}</th>
    <th width="1%">{__("position_short")}</th>
    <th width="20%">{__("name")}</th>
    <th>{__("delivery_time")}</th>
    <th>{__("weight_limit")}&nbsp;({$settings.General.weight_symbol})</th>
    {if !"ULTIMATE:FREE"|fn_allowed_for}
        <th>{__("usergroups")}</th>
    {/if}
    <th width="5%">&nbsp;</th>
    <th width="10%" class="right">{__("status")}</th>
</tr>
</thead>
{foreach from=$shippings item=shipping}

{assign var="allow_save" value=$shipping|fn_allow_save_object:"shippings"}

{if $allow_save}
    {assign var="status_display" value=""}
    {assign var="link_text" value=__("edit")}
{else}
    {assign var="status_display" value="text"}
    {assign var="link_text" value=__("view")}
{/if}

<tr class="cm-row-status-{$shipping.status|lower} {if !$allow_save}cm-hide-inputs{else}cm-no-hide-input{/if}">
<input type="hidden" name="shipping_data[{$shipping.shipping_id}][tax_ids][{$shipping.tax_ids}]" value="{$shipping.tax_ids}" />
    <td>
        <input type="checkbox" name="shipping_ids[]" value="{$shipping.shipping_id}" class="cm-item" /></td>
    <td>
        <input type="text" name="shipping_data[{$shipping.shipping_id}][position]" size="3" value="{$shipping.position}" class="input-micro input-hidden" /></td>
    <td data-ct-shipping-name="{$shipping.shipping}">
        <a href="{"shippings.update?shipping_id=`$shipping.shipping_id`"|fn_url}">{$shipping.shipping}</a>
        {include file="views/companies/components/company_name.tpl" object=$shipping}
    </td>
    <td>
        <input type="text" name="shipping_data[{$shipping.shipping_id}][delivery_time]" size="20" value="{$shipping.delivery_time}" class="input-mini input-hidden" /></td>
    <td class="nowrap">
        <input type="text" name="shipping_data[{$shipping.shipping_id}][min_weight]" size="4" value="{$shipping.min_weight}" class="input-mini input-hidden" />&nbsp;-&nbsp;<input type="text" name="shipping_data[{$shipping.shipping_id}][max_weight]" size="4" value="{if $shipping.max_weight != "0.00"}{$shipping.max_weight}{/if}" class="input-mini input-hidden right" /></td>
    {if !"ULTIMATE:FREE"|fn_allowed_for}
        <td class="nowrap">
            {include file="common/select_usergroups.tpl" select_mode=true title=__("usergroup") id="ship_data_`$shipping.shipping_id`" name="shipping_data[`$shipping.shipping_id`][usergroup_ids]" usergroups=$usergroups usergroup_ids=$shipping.usergroup_ids input_extra=""}
        </td>
    {/if}
    <td class="nowrap">
        {capture name="tools_list"}
            <li>{btn type="list" text=$link_text href="shippings.update?shipping_id=`$shipping.shipping_id`"}</li>
            {if $allow_save}
                <li>{btn type="list" text=__("delete") class="cm-confirm" href="shippings.delete?shipping_id=`$shipping.shipping_id`"}</li>
            {/if}
        {/capture}
        <div class="hidden-tools">
            {dropdown content=$smarty.capture.tools_list}
        </div>
    </td>
    <td class="right">
        {include file="common/select_popup.tpl" id=$shipping.shipping_id display=$status_display status=$shipping.status hidden="" object_id_name="shipping_id" table="shippings"}        
    </td>
</tr>
{/foreach}
</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}
</form>

{capture name="buttons"}
    {capture name="tools_list"}
        {if $shippings}
            <li>{btn type="delete_selected" dispatch="dispatch[shippings.m_delete]" form="shippings_form"}</li>
        {/if}
    {/capture}
    {dropdown content=$smarty.capture.tools_list}

    {if $shippings}
        {include file="buttons/save.tpl" but_name="dispatch[shippings.m_update]" but_role="submit-link" but_target_form="shippings_form"}
    {/if}
{/capture}

{capture name="adv_buttons"}
    {include file="common/tools.tpl" tool_href="shippings.add" prefix="top" hide_tools=true link_text="" title=__("add_shipping_method") icon="icon-plus"}
{/capture}

{/capture}
{include file="common/mainbox.tpl" title=__("manage_shippings") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons select_languages=true}