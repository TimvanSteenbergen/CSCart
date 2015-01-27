<div class="sidebar-row">
<h6>{__("search")}</h6>

<form action="{""|fn_url}" name="product_features_search_form" method="get">

{capture name="simple_search"}
<div class="sidebar-field">
    <label>{__("category")}:</label>
    <div class="break clear correct-picker-but">
    {if "categories"|fn_show_picker:$smarty.const.CATEGORY_THRESHOLD}
        {if $search.category_ids}
            {assign var="s_cid" value=$search.category_ids}
        {else}
            {assign var="s_cid" value="0"}
        {/if}
        {include file="pickers/categories/picker.tpl" data_id="location_category" input_name="category_ids" item_ids=$s_cid hide_link=true hide_delete_button=true default_name=__("all_categories") extra=""}
    {else}
        {include file="common/select_category.tpl" name="category_ids" id=$search.category_ids}
    {/if}
    </div>
</div>
<div class="sidebar-field">
    <label for="fname">{__("feature")}:</label>
    <input type="text" name="description" id="fname" value="{$search.description}" size="30" />
</div>
{/capture}

{capture name="advanced_search"}
<div class="group form-horizontal">
    {__("type")}
    
        <table width="100%">
            <tr class="nowrap">
                <td><label for="elm_checkbox_single" class="checkbox"><input id="elm_checkbox_single"  type="checkbox" name="feature_types[]" {if "C"|in_array:$search.feature_types}checked="checked"{/if} value="C"/>{__("checkbox")}:&nbsp;{__("single")}</label></td>
                <td><label for="elm_checkbox_multiple" class="checkbox"><input id="elm_checkbox_multiple" type="checkbox" name="feature_types[]" {if "M"|in_array:$search.feature_types}checked="checked"{/if} value="M"/>{__("checkbox")}:&nbsp;{__("multiple")}</label></td>
                <td><label for="elm_selectbox_text" class="checkbox"><input id="elm_selectbox_text"  type="checkbox" name="feature_types[]" {if "S"|in_array:$search.feature_types}checked="checked"{/if} value="S"/>{__("selectbox")}:&nbsp;{__("text")}</label></td>
                <td><label for="elm_selectbox_number" class="checkbox"><input id="elm_selectbox_number"  type="checkbox" name="feature_types[]" {if "N"|in_array:$search.feature_types}checked="checked"{/if} value="N"/>{__("selectbox")}:&nbsp;{__("number")}</label></td>
            </tr>
            <tr>
            <td><label for="elm_selectbox_brand_type" class="checkbox"><input id="elm_selectbox_brand_type"  type="checkbox" name="feature_types[]" {if "E"|in_array:$search.feature_types}checked="checked"{/if} value="E"/>{__("selectbox")}:&nbsp;{__("brand_type")}</label></td>
                <td><label for="elm_others_text" class="checkbox"><input id="elm_others_text"  type="checkbox" name="feature_types[]" {if "T"|in_array:$search.feature_types}checked="checked"{/if} value="T"/>{__("others")}:&nbsp;{__("text")}</label></td>
                <td><label for="elm_others_number" class="checkbox"><input id="elm_others_number"  type="checkbox" name="feature_types[]" {if "O"|in_array:$search.feature_types}checked="checked"{/if} value="O"/>{__("others")}:&nbsp;{__("number")}</label></td>
                <td><label for="elm_others_date" class="checkbox"><input id="elm_others_date"  type="checkbox" name="feature_types[]" {if "D"|in_array:$search.feature_types}checked="checked"{/if} value="D"/>{__("others")}:&nbsp;{__("date")}</label></td>
            </tr>
        </table>
    
</div>

<div class="group form-horizontal">
    <div class="control-group">
    <label class="control-label" for="elm_display_on">{__("display_on")}:</label>
    <div class="controls">
    <select name="display_on" id="elm_display_on">
        <option value="">--</option>
        <option value="product" {if $search.display_on == "product"}selected="selected"{/if}>{__("product")}</option>
        <option value="catalog" {if $search.display_on == "catalog"}selected="selected"{/if}>{__("catalog_pages")}</option>
    </select>
    </div>
    </div>

    <div class="control-group">
    <label for="elm_parent_id" class="control-label">{__("group")}:</label>
    <div class="controls">
    <select name="parent_id" id="elm_parent_id">
        <option value="">--</option>
        <option {if $search.parent_id === "0"}selected="selected"{/if} value="0">{__("ungroupped_features")}</option>
        {foreach from=$group_features item="group_feature"}
            <option value="{$group_feature.feature_id}"{if $group_feature.feature_id == $search.parent_id}selected="selected"{/if}>{$group_feature.description}</option>
        {/foreach}
    </select>
    </div>
    </div>

    {hook name="product_features:search_form"}
    {/hook}
</div>
{/capture}

{include file="common/advanced_search.tpl" simple_search=$smarty.capture.simple_search advanced_search=$smarty.capture.advanced_search dispatch=$dispatch view_type="product_features"}

</form>
</div>