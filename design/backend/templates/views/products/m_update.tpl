<script type="text/javascript">
    {literal}

    function fn_get_field_type (element) {
        if (Tygh.$(element).is('input[type=radio], input[type=checkbox], select')) {
            return 'elm-disabled';
        } else if (Tygh.$(element).is('div')){
            return '';
        } else {
            return 'input-text-disabled';
        }
    }

    (function($) {
        $(document).ready(function(){
            $('[id*=elements-switcher-]').click(function(){
                id = $(this).prop('id');
                id_template = /elements-switcher-(\S+)/i;
                id = 'field_' + id.match(id_template)[1];

                var checked = $(this).prop('checked');
                $('[id*=' + id + ']').each(function(index, element){
                    $(element).toggleClass(fn_get_field_type(element), !checked);
                    $(element).prop('disabled', !checked);
                    if (!checked) {
                        $(element).prop('checked', false);
                    }
                });
                $('#' + id + ' .correct-picker-but input').prop('disabled', !checked);
                $('#' + id + ' .correct-picker-but a').toggle(checked);
            });
            $('[id*=field_] .correct-picker-but a').hide();

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
    {/literal}
</script>

{assign var="all_categories_list" value=0|fn_get_plain_categories_tree:false}
{capture name="mainbox"}

{capture name="extra_tools"}
    {include file="buttons/button.tpl" but_text=__("override_product_data") but_onclick="Tygh.$('#override_box').toggle()" but_role="tool"}
{/capture}

<div id="override_box" class="hidden">

<form action="{""|fn_url}" method="post" name="override_form" class="form-horizontal form-edit" enctype="multipart/form-data">
<input type="hidden" name="fake" value="1" />
<input type="hidden" name="redirect_url" value="{"products.m_update"|fn_url}" />

<table>
<tr>
    <td>
        <div class="scroll-x scroll-border">
        <table class="table-fixed">
        <tr>
            {foreach from=$filled_groups item=v}
            <th>&nbsp;</th>
            {/foreach}
            {foreach from=$field_names item="field_name" key="field_key"}
            {if $field_key == "company_id"}
            <th>{__("vendor")}</th>
            {else}
            <th>{if $field_name|is_array}{__($field_key)}{else}{$field_name}{/if}</th>
            {/if}
            {/foreach}
        </tr>
        <tr >
            {foreach from=$filled_groups item=v key=type}
            <td valign="top" class="pad">
            {if $type != "L" || $type == "L" && $localizations}
                <table>
                {foreach from=$field_groups.$type item=name key=field}
                {if $v.$field}
                <tr>
                    <td valign="top" class="nowrap pad {if $field == "product"}strong{/if}"><label class="checkbox" for="elements-switcher-{$field}__"><input type="checkbox" name="" id="elements-switcher-{$field}__" value="Y" />{$v.$field}:&nbsp;</label></td>
                    <td valign="top" class="pad">
                        {if $type == "A"}
                        <input id="field_{$field}__" type="text" value="" name="override_{$name}[{$field}]" disabled="disabled" />
                        {elseif $type == "B"}
                        <input id="field_{$field}__" type="text" value=""  size="3" name="override_{$name}[{$field}]" disabled="disabled" />
                        {elseif $type == "C"}
                        <input id="field_{$field}__h" type="hidden" name="override_{$name}[{$field}]" value="N" disabled="disabled" />
                        <input id="field_{$field}__" type="checkbox" class="elm-disabled" name="override_{$name}[{$field}]" value="Y" disabled="disabled" />
                        {elseif $type == "D"}
                        <textarea id="field_{$field}__" name="override_{$name}[{$field}]" rows="3" cols="40" disabled="disabled"></textarea>
                        {elseif $type == "S"}
                        <select id="field_{$field}__" name="override_{$name.name}[{$field}]" class="elm-disabled" disabled="disabled">
                        {foreach from=$name.variants key=v_id item=v_name}
                        <option value="{$v_id}">{__($v_name)}</option>
                        {/foreach}
                        </select>
                        {elseif $type == "T"}
                            <div class="correct-picker-but">
                            {if $field == "timestamp"}
                            {include file="common/calendar.tpl" date_id="field_`$field`__date" date_name="override_$name[$field]" date_val=$smarty.const.TIME start_year=$settings.Company.company_start_year extra=" disabled=\"disabled\"" date_meta="input-text-disabled"}
                            {elseif $field == "avail_since"}
                            {include file="common/calendar.tpl" date_id="field_`$field`__date" date_name="override_$name[$field]" date_val=$smarty.const.TIME start_year=$settings.Company.company_start_year extra=" disabled=\"disabled\"" date_meta="input-text-disabled"}
                            {/if}
                            </div>
                        {elseif $type == "L"}
                            {include file="views/localizations/components/select.tpl" no_div=true disabled=true id="field_`$field`__" data_name="override_products_data[localization]"}
                        {elseif $type == "E"} {* Categories *}
                        <div class="clear" id="field_{$field}__">
                            <div class="correct-picker-but">
                                {include file="pickers/categories/picker.tpl" data_id="categories" input_name="override_`$name`[category_ids]" radio_input_name="override_`$name`[main_category]" item_ids="" hide_link=true display_input_id="category_ids" view_mode="list"}
                            </div>
                        </div>
                        {elseif $type == "W"} {* Product details layout *}
                            <select id="field_{$field}__" name="override_{$name}[{$field}]" class="elm-disabled" disabled="disabled">
                            {foreach from=$product_data.product_id|fn_get_product_details_views key="layout" item="item"}
                                <option value="{$layout}">{$item}</option>
                            {/foreach}
                            </select>
                        {/if}
                    </td>
                </tr>
                {/if}
                {/foreach}
                </table>
            {/if}
            </td>
            {/foreach}


            {foreach from=$field_names key="field" item=v}
            <td valign="top" class="pad">
            {if $field != "localization" || $field == "localization" && $localizations}
                <table class="no-border">
                <tr>
                    <td valign="top" class="pad">{if $field != "main_pair" && $field != "features"}<input type="checkbox" name="" value="Y" id="elements-switcher-{$field}__" />{else}&nbsp;{/if}</td>
                    <td valign="top" class="pad">
                    {if $field == "main_pair"}
                        <table width="420">
                        <tr>
                            <td>{include file="common/attach_images.tpl" image_name="product_main" image_object_type="product" image_type="M" no_thumbnail=true}</td>
                        </tr>
                        </table>
                    {elseif $field == "tracking"}
                        <select    id="field_{$field}__" name="override_products_data[{$field}]" class="elm-disabled" disabled="disabled">
                            <option value="{"ProductTracking::TRACK_WITH_OPTIONS"|enum}">{__("track_with_options")}</option>
                            <option value="{"ProductTracking::TRACK_WITHOUT_OPTIONS"|enum}">{__("track_without_options")}</option>
                            <option value="{"ProductTracking::DO_NOT_TRACK"|enum}">{__("dont_track")}</option>
                        </select>
                    {elseif $field == "zero_price_action"}
                        <select id="field_{$field}__" name="override_products_data[{$field}]" class="elm-disabled" disabled="disabled">
                            <option value="R">{__("zpa_refuse")}</option>
                            <option value="P">{__("zpa_permit")}</option>
                            <option value="A">{__("zpa_ask_price")}</option>
                        </select>
                    {elseif $field == "taxes"}
                        <input id="field_{$field}__h" type="hidden" name="override_products_data[tax_ids]" value="" disabled="disabled" />
                        {foreach from=$taxes item="tax"}
                        <div class="select-field nowrap no-padding">
                            <label class="checkbox" for="field_{$field}__{$tax.tax_id}"><input type="checkbox" name="override_products_data[tax_ids][{$tax.tax_id}]" id="field_{$field}__{$tax.tax_id}"  value="{$tax.tax_id}" disabled="disabled" />{$tax.tax}</label>
                        </div>
                        {/foreach}
                    {elseif $field == "features"}
                        {if $all_product_features}
                        <table>
                        {foreach from=$all_product_features item="pf"}
                        {if $pf.feature_type !== "G"}
                        <tr>
                            <td><label class="checkbox" for="elements-switcher-{$field}__{$pf.feature_id}_"><input type="checkbox" id="elements-switcher-{$field}__{$pf.feature_id}_" />&nbsp;{$pf.description}:&nbsp;</label></td>
                            <td>
                                {include file="views/products/components/products_m_update_feature.tpl" feature=$pf data_name="override_products_data" over=true}
                            </td>
                        </tr>
                        {/if}
                        {/foreach}
                        {foreach from=$all_product_features item="pf"}
                        {if $pf.subfeatures}
                        <tr>
                            <td colspan="2"><span>{$pf.description}</span></td>
                        </tr>
                        {foreach from=$pf.subfeatures item="subfeature"}
                        <tr>
                            <td class="nowrap"><label class="checkbox" for="elements-switcher-{$field}__{$subfeature.feature_id}_"><input type="checkbox" id="elements-switcher-{$field}__{$subfeature.feature_id}_"/>&nbsp;{$subfeature.description}</label></td>
                            <td>
                                {include file="views/products/components/products_m_update_feature.tpl" feature=$subfeature data_name="override_products_data" over=true}
                            </td>
                        </tr>
                        {/foreach}
                        {/if}
                        {/foreach}
                        </table>
                        {/if}
                    {elseif $field == "timestamp"}
                        <div class="correct-picker-but">
                        {include file="common/calendar.tpl" date_id="field_`$field`" date_name="override_products_data[`$field`]" date_val=$smarty.const.TIME extra=" disabled=\"disabled\"" start_year=$settings.Company.company_start_year}
                        </div>
                    {elseif $field == "localization"}
                        {include file="views/localizations/components/select.tpl" no_div=true data_name="products_data[`$product.product_id`][localization]" data_from=$product.localization}
                    {elseif $field == "usergroup_ids"}
                        {if !"ULTIMATE:FREE"|fn_allowed_for}
                            {include file="common/select_usergroups.tpl" id="field_`$field`_" name="override_products_data[`$field`]" usergroups="C"|fn_get_usergroups:$smarty.const.DESCR_SL usergroup_ids="" input_extra=" disabled=\"disabled\"" list_mode=true}
                        {/if}
                    {elseif $field == "company_id"}
                        <div class="clear" id="field_{$field}__">
                            <div class="correct-picker-but">
                            {include file="views/products/components/products_m_update_company.tpl" override_box="Y"}
                            </div>
                        </div>
                    {else}
                        {hook name="products:update_fields"}
                            {hook name="products:update_fields_inner"}
                                <input id="field_{$field}__" type="text" value="" name="override_products_data[{$field}]" disabled="disabled" />
                            {/hook}
                        {/hook}
                    {/if}
                    </td>
                </tr>
                </table>
            {/if}
            </td>
            {/foreach}
        </tr>
        </table>
        </div>
    </td>
</tr>
</table>

<div class="buttons-container">
    {include file="buttons/button.tpl" but_text=__("apply") but_name="dispatch[products.m_override]" but_role="button_main"}
</div>

</form>
</div>
{* ================================ *}

<form action="{""|fn_url}" method="post" name="products_m_update_form" enctype="multipart/form-data">
<input type="hidden" name="fake" value="1" />
<input type="hidden" name="redirect_url" value="{"products.m_update"|fn_url}" />

<table>
<tr>
    <td>

        <div id="scrolled_div_top" class="scroll-x scroll-top"></div>
        <div id="scrolled_div" class="scroll-x scroll-border">
        <table class="table-fixed">
        <tr>
            {foreach from=$filled_groups item=v}
            <th>&nbsp;</th>
            {/foreach}
            {foreach from=$field_names item="field_name" key=field_key}
            {if $field_key == "company_id"}
            <th>{__("vendor")}</th>
            {else}
            <th>{if $field_name|is_array}{__($field_key)}{else}{$field_name}{/if}</th>
            {/if}
            {/foreach}
        </tr>
        {foreach from=$products_data item="product"}
        <tr >
            {foreach from=$filled_groups item=v key=type}
            <td valign="top" class="pad">
            {if $type != "L" || $type == "L" && $localizations}
                <table class="no-border">
                {foreach from=$field_groups.$type item=name key=field}
                {if $v.$field}
                <tr>
                    <td valign="top" class="nowrap pad {if $field == "product"}strong{/if}">{$v.$field}:&nbsp;</td>
                    <td valign="top" class="pad nowrap">
                        {if $type == "A"}
                            <input type="text" value="{$product.$field}" class="input-medium" name="{$name}[{$product.product_id}][{$field}]"/>
                        {elseif $type == "B"}
                            <input type="text" value="{$product.$field|default:0}" class="input-medium" size="5" name="{$name}[{$product.product_id}][{$field}]" />
                        {elseif $type == "C"}
                            <input type="hidden" name="{$name}[{$product.product_id}][{$field}]" value="N" />
                        <input type="checkbox" name="{$name}[{$product.product_id}][{$field}]" value="Y" {if $product.$field == "Y"}checked="checked"{/if} />
                        {elseif $type == "D"}
                            <textarea class="input-xlarge" name="{$name}[{$product.product_id}][{$field}]" rows="3" cols="40">{$product.$field}</textarea>
                        {elseif $type == "S"}
                            <select name="{$name.name}[{$product.product_id}][{$field}]">
                                {foreach from=$name.variants key=v_id item=v_name}
                                <option value="{$v_id}" {if $product.$field == $v_id}selected="selection"{/if}>{__($v_name)}</option>
                                {/foreach}
                            </select>
                        {elseif $type == "T"}
                            <div class="correct-picker-but">
                            {if $field == "timestamp"}
                            {include file="common/calendar.tpl" date_id="date_timestamp_holder_`$product.product_id`" date_name="$name[`$product.product_id`][$field]" date_val=$product.$field start_year=$settings.Company.company_start_year}
                            {elseif $field == "avail_since"}
                            {include file="common/calendar.tpl" date_id="date_avail_holder_`$product.product_id`" date_name="$name[`$product.product_id`][$field]" date_val=$product.$field start_year=$settings.Company.company_start_year}
                            {/if}
                            </div>
                        {elseif $type == "L"}
                            {include file="views/localizations/components/select.tpl" no_div=true data_from=$product.localization data_name="products_data[`$product.product_id`][localization]"}
                        {elseif $type == "E"} {* Categories *}
                            <div class="correct-picker-but">
                                {include file="pickers/categories/picker.tpl" data_id="categories" input_name="`$name`[`$product.product_id`][category_ids]" radio_input_name="`$name`[`$product.product_id`][main_category]" item_ids=$product.category_ids main_category=$product.main_category hide_link=true display_input_id="category_ids" view_mode="list"}
                            </div>
                        {elseif $type == "W"} {* Product details layout *}
                            <select name="{$name}[{$product.product_id}][{$field}]">
                            {foreach from=$product_data.product_id|fn_get_product_details_views key="layout" item="item"}
                                <option {if $product.details_layout == $layout}selected="selected"{/if} value="{$layout}">{$item}</option>
                            {/foreach}
                            </select>
                        {/if}
                    </td>
                </tr>
                {/if}
                {/foreach}
                </table>
            {/if}
            </td>
            {/foreach}

            {foreach from=$field_names key="field" item=v}
            {if $field != "product_id" && ($field != "localization" || $field == "localization" && $localizations)}
            <td valign="top" class="pad">
                    {if $field == "main_pair"}
                        <table width="420"><tr><td>{include file="common/attach_images.tpl" image_name="product_main" image_key=$product.product_id image_pair=$product.main_pair image_object_id=$product.product_id image_object_type="product" image_type="M" no_thumbnail=true}</td></tr></table>
                    {elseif $field == "tracking"}
                        <select    name="products_data[{$product.product_id}][{$field}]">
                            <option value="{"ProductTracking::TRACK_WITH_OPTIONS"|enum}" {if $product.tracking == "ProductTracking::TRACK_WITH_OPTIONS"|enum}selected="selected"{/if}>{__("track_with_options")}</option>
                            <option value="{"ProductTracking::TRACK_WITHOUT_OPTIONS"|enum}" {if $product.tracking == "ProductTracking::TRACK_WITHOUT_OPTIONS"|enum}selected="selected"{/if}>{__("track_without_options")}</option>
                            <option value="{"ProductTracking::DO_NOT_TRACK"|enum}" {if $product.tracking == "ProductTracking::DO_NOT_TRACK"|enum}selected="selected"{/if}>{__("dont_track")}</option>
                        </select>
                    {elseif $field == "zero_price_action"}
                        <select name="products_data[{$product.product_id}][{$field}]">
                            <option value="R" {if $product.zero_price_action == "R"}selected="selected"{/if}>{__("zpa_refuse")}</option>
                            <option value="P" {if $product.zero_price_action == "P"}selected="selected"{/if}>{__("zpa_permit")}</option>
                            <option value="A" {if $product.zero_price_action == "A"}selected="selected"{/if}>{__("zpa_ask_price")}</option>
                        </select>
                    {elseif $field == "taxes"}
                        <input type="hidden" name="products_data[{$product.product_id}][tax_ids]" value="" />
                        {foreach from=$taxes item="tax"}
                        <div class="select-field nowrap">
                            <label class="checkbox" for="products_taxes_{$product.product_id}_{$tax.tax_id}"><input type="checkbox" name="products_data[{$product.product_id}][tax_ids][{$tax.tax_id}]" id="products_taxes_{$product.product_id}_{$tax.tax_id}" {if $tax.tax_id|in_array:$product.tax_ids}checked="checked"{/if}  value="{$tax.tax_id}" />
                            {$tax.tax}</label>
                        </div>
                        {/foreach}
                    {elseif $field == "features"}
                        {if $product.product_features}
                        <table >
                        {foreach from=$product.product_features item="pf" key="feature_id"}
                        {if $pf.feature_type != "G"}
                        <tr>
                            <td>{$pf.description}:</td>
                            <td >
                                {include file="views/products/components/products_m_update_feature.tpl" feature=$pf data_name="products_data[`$product.product_id`]" pid=$product.product_id}
                            </td>
                        </tr>
                        {/if}
                        {/foreach}
                        {foreach from=$product.product_features item="pf" key="feature_id"}
                        {if $pf.feature_type == "G" && $pf.subfeatures}
                        <tr>
                            <td colspan="2"><span>{$pf.description}</span></td>
                        </tr>
                        {foreach from=$pf.subfeatures item=subfeature}
                        <tr>
                            <td>{$subfeature.description}:</td>
                            <td>{include file="views/products/components/products_m_update_feature.tpl" feature=$subfeature data_name="products_data[`$product.product_id`]" pid=$product.product_id}</td>
                        </tr>
                        {/foreach}
                        {/if}
                        {/foreach}
                        </table>
                        <input type="hidden" name="products_data[{$product.product_id}][features_exist]" value="Y" />
                        {/if}
                    {elseif $field == "timestamp"}
                        <div class="correct-picker-but">
                        {include file="common/calendar.tpl" date_id="prod_date" date_name="products_data[`$product.product_id`][$field]" date_val=$product.timestamp|default:$smarty.const.TIME start_year=$settings.Company.company_start_year}
                        </div>
                    {elseif $field == "localization"}
                        {include file="views/localizations/components/select.tpl" no_div=true data_name="products_data[`$product.product_id`][localization]" data_from=$product.localization}
                    {elseif $field == "usergroup_ids"}
                        {if !"ULTIMATE:FREE"|fn_allowed_for}
                            {include file="common/select_usergroups.tpl" id="product_ug_`$product.product_id`" name="products_data[`$product.product_id`][`$field`]" usergroups="C"|fn_get_usergroups:$smarty.const.DESCR_SL usergroup_ids=$product.usergroup_ids input_extra="" list_mode=true}
                        {/if}
                    {elseif $field == "company_id"}
                        {include file="views/products/components/products_m_update_company.tpl"}
                    {else}
                        {hook name="products:update_fields_extra"}
                            {hook name="products:update_fields_inner_extra"}
                                <input type="text" value="{$product.$field}" class="input-medium" name="products_data[{$product.product_id}][{$field}]" />
                            {/hook}
                        {/hook}
                    {/if}
            </td>
            {/if}
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
    {include file="buttons/save.tpl" but_name="dispatch[products.m_update]" but_role="submit-link" but_target_form="products_m_update_form"}
{/capture}

{include file="common/mainbox.tpl" title=__("update_products") content=$smarty.capture.mainbox select_languages=true extra_tools=$smarty.capture.extra_tools buttons=$smarty.capture.buttons}
