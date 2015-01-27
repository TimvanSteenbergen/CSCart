{capture name="mainbox"}
<form action="{""|fn_url}" method="post" name="categories_m_addition_form">

<table width="100%" class="table table-middle">
<thead>
<tr class="cm-first-sibling">
    <th width="15%">{__("category_location")}</th>
    <th width="15%">{__("category_name")}</th>
    {if "ULTIMATE"|fn_allowed_for}
        <th width="15%">{__("vendor")}</th>
    {/if}
    {if !"ULTIMATE:FREE"|fn_allowed_for}
        <th width="15%">{__("usergroup")}</th>
    {/if}
    <th width="10%">{__("position")}</th>
    <th width="15%">{__("status")}</th>
    <th width="7%">&nbsp;</th>
</tr>
</thead>
<tr id="box_new_cat_tag">
    <td>
        {if "categories"|fn_show_picker:$smarty.const.CATEGORY_THRESHOLD}
            {include file="pickers/categories/picker.tpl" data_id="location_category" input_name="categories_data[0][parent_id]" item_ids=0 hide_link=true hide_delete_button=true default_name=__("root_level")}
        {else}
            {include file="common/select_category.tpl" name="categories_data[0][parent_id]" select_class="input-medium" root_text=__("root_level") id=""}
        {/if}
    </td>
    <td>
        <input class="span3" type="text" name="categories_data[0][category]" size="40" value="" /></td>
    {if "ULTIMATE"|fn_allowed_for}
        <td>
            {include file="views/companies/components/company_field.tpl"
                name="categories_data[0][company_id]"
                id="categories_data_company_id_0"
                no_wrap=true
            }
        </td>
    {/if}

    {if !"ULTIMATE:FREE"|fn_allowed_for}
        <td>     
            {include file="common/select_usergroups.tpl" id="ug" select_mode=true title=__("usergroup") id="ship_data_`$shipping.shipping_id`" name="categories_data[0][usergroup_ids]" usergroups="C"|fn_get_usergroups:$smarty.const.DESCR_SL input_extra=""}
        </td>
    {/if}
    <td>
        <input class="input-micro" type="text" name="categories_data[0][position]" size="3" value="" /></td>
    <td>
        <select name="categories_data[0][status]" class="input-small">
            <option value="A">{__("active")}</option>
            <option value="H">{__("hidden")}</option>
            <option value="D">{__("disabled")}</option>
        </select>
    </td>
    <td class="right nowrap">
    <div class="hidden-tools">
        {include file="buttons/multiple_buttons.tpl" item_id="new_cat_tag" on_add="fn_calculate_usergroups(Tygh.$(this).next('tr'));"}
    </div>
    </td>
</tr>
</table>
</form>
{/capture}

{capture name="buttons"}
    {include file="buttons/create.tpl" but_name="dispatch[categories.m_add]" but_role="submit-link" but_target_form="categories_m_addition_form"}
{/capture}

{include file="common/mainbox.tpl" title=__("add_categories") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons}
