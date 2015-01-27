{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="taxes_form" class="{if ""|fn_check_form_permissions} cm-hide-inputs{/if}">

{if $taxes}
<table width="100%" class="table table-middle">
<thead>
<tr>
    <th>{include file="common/check_items.tpl"}</th>
    <th width="15%">{__("name")}</th>
    <th>{__("regnumber")}</th>
    <th>{__("priority")}</th>
    <th>{__("rates_depend_on")}</th>
    <th class="center">{__("price_includes_tax")}</th>
    <th width="5%">&nbsp;</th>
    <th width="10%" class="right">{__("status")}</th>
</tr>
</thead>
{foreach from=$taxes item=tax}
<tr class="cm-row-status-{$tax.status|lower}" data-ct-tax-id="{$tax.tax_id}">
    <td class="center" width="1%">
        <input type="checkbox" name="tax_ids[]" value="{$tax.tax_id}" class="cm-item" /></td>
    <td class="nowrap" data-ct-tax-name="{$tax.tax}">
        <a href="{"taxes.update?tax_id=`$tax.tax_id`"|fn_url}">{$tax.tax}</a>
    </td>
    <td>
        <input type="text" name="tax_data[{$tax.tax_id}][regnumber]" size="10" value="{$tax.regnumber}" class="input-mini input-hidden" /></td>
    <td class="center">
        <input type="text" name="tax_data[{$tax.tax_id}][priority]" size="3" value="{$tax.priority}" class="input-micro input-hidden" /></td>
    <td><select name="tax_data[{$tax.tax_id}][address_type]">
            <option value="S" {if $tax.address_type == "S"}selected="selected"{/if}>{__("shipping_address")}</option>
            <option value="B" {if $tax.address_type == "B"}selected="selected"{/if}>{__("billing_address")}</option>
        </select>
    </td>
    <td class="center">
        <input type="hidden" name="tax_data[{$tax.tax_id}][price_includes_tax]" value="N" />
        <input type="checkbox" name="tax_data[{$tax.tax_id}][price_includes_tax]" value="Y" {if $tax.price_includes_tax == "Y"}checked="checked"{/if} class="checkbox" />
    </td>
    <td class="nowrap">
        {capture name="tools_list"}
            <li>{btn type="list" text=__("edit") href="taxes.update?tax_id=`$tax.tax_id`"}</li>
            <li>{btn type="list" text=__("delete") class="cm-confirm" href="taxes.delete?tax_id=`$tax.tax_id`"}</li>
        {/capture}
        <div class="hidden-tools">
            {dropdown content=$smarty.capture.tools_list}
        </div>
    </td>
    <td class="right nowrap">
        {$has_permission = fn_check_permissions("tools", "update_status", "admin", "GET", ["table" => "taxes"])}
        {include file="common/select_popup.tpl" id=$tax.tax_id status=$tax.status object_id_name="tax_id" table="taxes" non_editable=!$has_permission}
    </td>
</tr>
{/foreach}
</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}

</form>

{capture name="buttons"}
    {if $taxes}
        {capture name="tools_list"}
            {hook name="taxes:manage_tools_list"}
                <li>{btn type="list" text=__("apply_tax_to_products") dispatch="dispatch[taxes.apply_selected_taxes]" form="taxes_form"}</li>
                <li>{btn type="list" text=__("unset_tax_to_products") dispatch="dispatch[taxes.unset_selected_taxes]" form="taxes_form"}</li>
                <li class="divider"></li>
                <li>{btn type="delete_selected" dispatch="dispatch[taxes.m_delete]" form="taxes_form"}</li>
            {/hook}
        {/capture}
        {dropdown content=$smarty.capture.tools_list}
    {/if}
    {if $taxes}
        {include file="buttons/save.tpl" but_name="dispatch[taxes.m_update]" but_role="submit-link" but_target_form="taxes_form"}
    {/if}
{/capture}

{capture name="adv_buttons"}
    {include file="common/tools.tpl" tool_href="taxes.add" prefix="top" hide_tools=true title=__("add_tax") icon="icon-plus"}
{/capture}

{/capture}
{include file="common/mainbox.tpl" title=__("taxes") content=$smarty.capture.mainbox adv_buttons=$smarty.capture.adv_buttons buttons=$smarty.capture.buttons select_languages=true}