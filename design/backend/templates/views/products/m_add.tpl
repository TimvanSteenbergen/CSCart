{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="products_m_addition_form">

<table width="100%"    class="table table-middle">
<thead>
<tr class="cm-first-sibling">
    <th>{__("main_category")}</th>
    <th>{__("product_name")}</th>
    <th>{__("vendor")}</th> 
    <th>{__("price")}</th>
    <th class="wrap" width="12%">{__("list_price_short")}</th>
    <th>{__("position_short")}</th>
    <th>{__("status")}</th>
    <th>&nbsp;</th>
</tr>
</thead>
<tr class="table-row" id="box_new_product">
    <td>
        {if "categories"|fn_show_picker:$smarty.const.CATEGORY_THRESHOLD}
            {include file="pickers/categories/picker.tpl" data_id="location_category" input_name="products_data[0][category_ids][]" item_ids=$default_category_id hide_link=true hide_delete_button=true prepend=true}
        {else}
            {include file="common/select_category.tpl" name="products_data[0][category_ids][]" select_class="input-medium" hide_root=true id=""}
        {/if}
    </td>
    <td><input type="text" name="products_data[0][product]" value="" class="input-medium" /></td>
    
    <td width="18%" class="wrap">
        {include file="views/companies/components/company_field.tpl"
            name="products_data[0][company_id]"
            id="products_data_company_id_0"
            no_wrap=true
        }
    </td>
    <td><input type="text" name="products_data[0][price]" size="4" value="0.00" class="input-mini" /></td>
    <td><input type="text" name="products_data[0][list_price]" size="4" value="0.00" class="input-mini" /></td>
    <td><input type="text" name="products_data[0][position]" size="3" value="0" class="input-micro" /></td>
    <td>
        <select name="products_data[0][status]" class="input-small">
            <option value="A">{__("active")}</option>
            <option value="H">{__("hidden")}</option>
            <option value="D">{__("disabled")}</option>
        </select>
    </td>
    <td class="nowrap">
        <div class="hidden-tools">
            {include file="buttons/multiple_buttons.tpl" item_id="new_product"}
        </div>
    </td>
</tr>
</table>
</form>

{capture name="buttons"}
    {include file="buttons/create.tpl" but_name="dispatch[products.m_add]" but_role="submit-link" but_target_form="products_m_addition_form"}
{/capture}

{/capture}
{include file="common/mainbox.tpl" title=__("add_products") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons}