{if $feature}
    {assign var="id" value=$feature.feature_id}
{else}
    {if $is_group == true}
        {assign var="id" value=$smarty.const.NEW_FEATURE_GROUP_ID}
    {else}
        {assign var="id" value=0}
    {/if}
{/if}

{assign var="allow_save" value=true}
{if "ULTIMATE"|fn_allowed_for}
    {assign var="allow_save" value=$feature|fn_allow_save_object:"product_features"}
{/if}

{$hide_inputs_class = ""}
{if ""|fn_check_form_permissions || !$allow_save}
{$hide_inputs_class = "cm-hide-inputs"}
{/if}

<div id="content_group{$id}">
<form action="{""|fn_url}" method="post" name="update_features_form_{$id}" class="form-horizontal form-edit  cm-disable-empty-files {$hide_inputs_class}" enctype="multipart/form-data">

<input type="hidden" class="cm-no-hide-input" name="redirect_url" value="{$smarty.request.return_url}" />
<input type="hidden" class="cm-no-hide-input" name="feature_id" value="{$id}" />

<div class="tabs cm-j-tabs">
    <ul class="nav nav-tabs">
        <li id="tab_details_{$id}" class="cm-js active"><a>{__("general")}</a></li>
        <li id="tab_variants_{$id}" class="cm-js cm-ajax {if $feature.feature_type && "SMNE"|strpos:$feature.feature_type === false || !$feature}hidden{/if}"><a href="{"product_features.get_variants?feature_id=`$id`&feature_type=`$feature.feature_type`"|fn_url}">{__("variants")}</a></li>
        <li id="tab_categories_{$id}" class="cm-js {if $feature.parent_id} hidden{/if}"><a>{__("categories")}</a></li>
    </ul>
</div>

<div class="cm-tabs-content" id="tabs_content_{$id}">
    
    <div id="content_tab_details_{$id}">
    <fieldset>
        <div class="control-group">
            <label class="control-label cm-required" for="elm_feature_name_{$id}">{__("name")}</label>
            <div class="controls">
            <input class="span9" type="text" name="feature_data[description]" value="{$feature.description}" id="elm_feature_name_{$id}" />
            </div>
        </div>
        
        {if "ULTIMATE"|fn_allowed_for}
            {include file="views/companies/components/company_field.tpl"
                name="feature_data[company_id]"
                id="elm_feature_data_`$id`"
                selected=$feature.company_id
            }
        {/if}

        <div class="control-group">
            <label class="control-label" for="elm_feature_code_{$id}">{__("feature_code")}</label>
            <div class="controls">
                <input type="text" size="3" name="feature_data[feature_code]" value="{$feature.feature_code}" class="input-medium" id="elm_feature_code_{$id}" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="elm_feature_position_{$id}">{__("position")}</label>
            <div class="controls">
                <input type="text" size="3" name="feature_data[position]" value="{$feature.position}" class="input-medium" id="elm_feature_position_{$id}" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="elm_feature_description_{$id}">{__("description")}</label>
            <div class="controls">
                <textarea name="feature_data[full_description]" cols="55" rows="4" class="span9 cm-wysiwyg" id="elm_feature_description_{$id}">{$feature.full_description}</textarea>
            </div>
        </div>

        {if $is_group || $feature.feature_type == "G"}
            <input type="hidden" name="feature_data[feature_type]" value="G" />
        {else}
        <div class="control-group">
            <label class="control-label cm-required" for="elm_feature_type_{$id}">{__("type")}</label>
            <div class="controls">
            {if $feature.feature_type == "G"}{__("group")}{else}
                <select name="feature_data[feature_type]" id="elm_feature_type_{$id}" data-ca-feature-id="{$id}" class="cm-feature-type {if !$id}cm-new-feature{/if}">
                    <optgroup label="{__("checkbox")}">
                        <option value="C" {if $feature.feature_type == "C"}selected="selected"{/if}>{__("single")}</option>
                        <option value="M" {if $feature.feature_type == "M"}selected="selected"{/if}>{__("multiple")}</option>
                    </optgroup>
                    <optgroup label="{__("selectbox")}">
                        <option value="S" {if $feature.feature_type == "S"}selected="selected"{/if}>{__("text")}</option>
                        <option value="N" {if $feature.feature_type == "N"}selected="selected"{/if}>{__("number")}</option>
                        <option value="E" {if $feature.feature_type == "E"}selected="selected"{/if}>{__("brand_type")}</option>
                    </optgroup>
                    <optgroup label="{__("others")}">
                        <option value="T" {if $feature.feature_type == "T"}selected="selected"{/if}>{__("text")}</option>
                        <option value="O" {if $feature.feature_type == "O"}selected="selected"{/if}>{__("number")}</option>
                        <option value="D" {if $feature.feature_type == "D"}selected="selected"{/if}>{__("date")}</option>
                    </optgroup>
                </select>
                <div class="error-message feature_type_{$id}" style="display: none" id="warning_feature_change_{$id}"><div class="arrow"></div><div class="message"><p>{__("warning_variants_removal")}</p></div></div>
            {/if}
            </div>
        </div>
            <div class="control-group">
            <label class="control-label" for="elm_feature_group_{$id}">{__("group")}</label>
            <div class="controls">
            {if $feature.feature_type == "G"}-{else}
                <select name="feature_data[parent_id]" id="elm_feature_group_{$id}" data-ca-feature-id="{$id}" class="cm-feature-group">
                    <option value="0">-{__("none")}-</option>
                    {foreach from=$group_features item="group_feature"}
                        {if $group_feature.feature_type == "G"}
                            <option data-ca-display-on-product="{$group_feature.display_on_product}" data-ca-display-on-catalog="{$group_feature.display_on_catalog}" data-ca-display-on-header="{$group_feature.display_on_header}" value="{$group_feature.feature_id}"{if $group_feature.feature_id == $feature.parent_id}selected="selected"{/if}>{$group_feature.description}</option>
                        {/if}
                    {/foreach}
                </select>
            {/if}
            </div>
        </div>
        {/if}
        <div class="control-group">
            <label class="control-label" for="elm_feature_display_on_product_{$id}">{__("feature_display_on_product")}</label>
            <div class="controls">
            <input type="hidden" name="feature_data[display_on_product]" value="N" />
            <input type="checkbox" name="feature_data[display_on_product]" value="Y" data-ca-display-id="OnProduct" {if $feature.display_on_product == "Y"}checked="checked"{/if} {if $feature.parent_id && $group_features[$feature.parent_id].display_on_product == "Y"}disabled="disabled"{/if}/>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="elm_feature_display_on_catalog_{$id}">{__("feature_display_on_catalog")}</label>
            <div class="controls">
            <input type="hidden" name="feature_data[display_on_catalog]" value="N" />
            <input type="checkbox" name="feature_data[display_on_catalog]" value="Y"  data-ca-display-id="OnCatalog" {if $feature.display_on_catalog == "Y"}checked="checked"{/if} {if $feature.parent_id && $group_features[$feature.parent_id].display_on_catalog == "Y"}disabled="disabled"{/if} />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="elm_feature_display_on_header_{$id}">{__("feature_display_on_header")}</label>
            <div class="controls">
            <input type="hidden" name="feature_data[display_on_header]" value="N" />
            <input type="checkbox" name="feature_data[display_on_header]" value="Y"  data-ca-display-id="OnHeader" {if $feature.display_on_header == "Y"}checked="checked"{/if} {if $feature.parent_id && $group_features[$feature.parent_id].display_on_header == "Y"}disabled="disabled"{/if} />
            </div>
        </div>

        {if (!$feature && !$is_group) || ($feature.feature_type && $feature.feature_type != "G")}
        <div class="control-group">
            <label class="control-label" for="elm_feature_prefix_{$id}">{__("prefix")}</label>
            <div class="controls">
            <input type="text" name="feature_data[prefix]" value="{$feature.prefix}" id="elm_feature_prefix_{$id}" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="elm_feature_suffix_{$id}">{__("suffix")}</label>
            <div class="controls">
            <input type="text" name="feature_data[suffix]" value="{$feature.suffix}" id="elm_feature_suffix_{$id}" /></div>
        </div>
        {/if}
        
        {hook name="product_features:properties"}
        {/hook}
    </fieldset>
    <!--content_tab_details_{$id}--></div>
    {if $id && $id != "0G"}
        {include file="views/product_features/components/variants_list.tpl"}
    {/if}
    {if !$feature.parent_id}
    <div class="hidden" id="content_tab_categories_{$id}">
    {if $feature.categories_path}
        {assign var="items" value=","|explode:$feature.categories_path}
    {/if}
    {include file="pickers/categories/picker.tpl" company_ids=$picker_selected_companies multiple=true input_name="feature_data[categories_path]" item_ids=$items data_id="category_ids_`$id`" no_item_text=__("text_all_categories_included") use_keys="N" owner_company_id=$feature.company_id but_meta="pull-right"}

    <!--content_tab_categories_{$id}--></div>
    {/if}

</div>

<div class="buttons-container">
    {if "ULTIMATE"|fn_allowed_for && !$allow_save}
        {assign var="hide_first_button" value=true}
    {/if}
    {include file="buttons/save_cancel.tpl" but_name="dispatch[product_features.update]" cancel_action="close" hide_first_button=$hide_first_button save=$feature.feature_id}
</div>
</form>
<!--content_group{$id}--></div>