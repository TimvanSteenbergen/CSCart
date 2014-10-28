{capture name="mainbox"}
<script type="text/javascript">
    (function($) {
        $(document).ready(function(){
            // Double scroll
            var elm_orig = $("#scrolled_div");
            var elm_scroller = $("#scrolled_div_top");

            var dummy = $("<div></div>");
            dummy.width(elm_orig.get(0).scrollWidth);
            dummy.height(24);
            elm_scroller.append(dummy);

            elm_scroller.scroll(function(){
                elm_orig.scrollLeft(elm_scroller.scrollLeft());
            });
            elm_orig.scroll(function(){
                elm_scroller.scrollLeft(elm_orig.scrollLeft());
            });
        });
    }(Tygh.$));
</script>

<form action="{""|fn_url}" method="post" enctype="multipart/form-data" name="categories_m_update_form">
<input type="hidden" name="fake" value="1" />
<input type="hidden" name="redirect_url" value="{"categories.m_update"|fn_url}" />

<table width="100%">
<tr>
    <td width="100%">
        <div id="scrolled_div_top" class="scroll-x scroll-top"></div>
        <div id="scrolled_div" class="scroll-x scroll-border">
        <table width="100%" class="table-fixed">
        <tr>
            {foreach from=$filled_groups item=v}
            <th>&nbsp;</th>
            {/foreach}
            {foreach from=$field_names item="field_name" key=field_key}
            <th>{if $field_name|is_array}{__($field_key)}{else}{$field_name}{/if}</th>
            {/foreach}
        </tr>
        {foreach from=$categories_data item="category"}

        <tr>
            {foreach from=$filled_groups item=v key=type}
            <td valign="top" class="pad">
                <table>
                {foreach from=$field_groups.$type item=name key=field}
                {if $v.$field}
                <tr valign="top">
                    <td class="nowrap {if $field == "category"}strong{/if}">{$v.$field}:&nbsp;</td>
                    <td>
                        {if $type == "A"}
                        <input type="text" value="{$category.$field}" class="input-text" name="{$name}[{$category.category_id}][{$field}]" />
                        {elseif $type == "C"}
                        <textarea class="input-text" name="{$name}[{$category.category_id}][{$field}]" rows="3" cols="40">{$category.$field}</textarea>
                        {/if}
                    </td>
                </tr>
                {/if}
                {/foreach}
                </table>
            </td>
            {/foreach}

            {foreach from=$field_names key="field" item=v}
            <td valign="top" class="pad">
                    {if $field == "status"}
                        <select name="categories_data[{$category.category_id}][{$field}]">
                            <option value="A" {if $category.status == "A"}selected="selected"{/if}>{__("active")}</option>
                            <option value="D" {if $category.status == "D"}selected="selected"{/if}>{__("disabled")}</option>
                            <option value="H" {if $category.status == "H"}selected="selected"{/if}>{__("hidden")}</option>
                        </select>
                    
                    {elseif $field == "usergroup_ids"}
                        {if !"ULTIMATE:FREE"|fn_allowed_for}
                            {include file="common/select_usergroups.tpl" id="category_ug_`$category.category_id`" name="categories_data[`$category.category_id`][`$field`]" usergroups="C"|fn_get_usergroups:$smarty.const.DESCR_SL usergroup_ids=$category.usergroup_ids input_extra="" list_mode=true}
                        {/if}
                    {elseif $field == "discussion_type"}
                        {include file="addons/discussion/views/discussion_manager/components/bulk_allow_discussion.tpl" prefix="categories_data" object_id=$category.category_id object_type="C"}
                    {elseif $field == "image_pair"}
                        <table width="420">
                        <tr>
                            <td>{include file="common/attach_images.tpl" image_key=$category.category_id image_name="category_main" image_object_type="category" image_pair=$category.main_pair image_object_id=$category.category_id no_thumbnail=true}</td>
                        </tr>
                        </table>
                    {elseif $field == "timestamp"}
                        {include file="common/calendar.tpl" date_id="date_`$category.category_id`" date_name="categories_data[`$category.category_id`][$field]" date_val=$category.timestamp|default:$smarty.const.TIME start_year=$settings.Company.company_start_year}
                    {elseif $field == "localization"}
                        {include file="views/localizations/components/select.tpl" no_div=true data_from=$category.localization data_name="categories_data[`$category.category_id`][`$field`]"}
                    {elseif $field == "product_details_layout"}
                        <select name="categories_data[{$category.category_id}][{$field}]">
                        {foreach from="category"|fn_get_product_details_views key="layout" item="item"}
                            <option {if $category.product_details_layout == $layout}selected="selected"{/if} value="{$layout}">{$item}</option>
                        {/foreach}
                        </select>
                    {else}
                    {assign var="f_category" value=$category.$field}
                    <input type="text" value="{$f_category}" class="input-mupdate-{$field}" name="categories_data[{$category.category_id}][{$field}]" />
                    {/if}

            </td>
            {/foreach}
        </tr>
        {/foreach}
        </table>
        </div>
    </td>
</tr>
</table>
</form>
{/capture}

{capture name="buttons"}
    {include file="buttons/save.tpl" but_name="dispatch[categories.m_update]" but_target_form="categories_m_update_form" but_role="submit-link"}
{/capture}

{include file="common/mainbox.tpl" title=__("update_categories") content=$smarty.capture.mainbox select_languages=true buttons=$smarty.capture.buttons}
