{if $option_data.option_id}
    {assign var="id" value=$option_data.option_id}
{else}
    {assign var="id" value=0}
{/if}

{if "ULTIMATE"|fn_allowed_for}
    {if !$runtime.company_id && $shared_product == "Y"}
        {assign var="show_update_for_all" value=true}
    {/if}

    {if $runtime.company_id && $shared_product == "Y"}
        {assign var="cm_no_hide_input" value="cm-no-hide-input"}
    {/if}
{/if}

{assign var="allow_save" value=$option_data|fn_allow_save_object:"product_options"}

<div id="content_group_product_option_{$id}">

<form action="{""|fn_url}" method="post" name="option_form_{$id}" class="form-horizontal form-edit form-highlight cm-disable-empty-files {if !$allow_save}cm-hide-inputs{/if}" enctype="multipart/form-data">
<input type="hidden" name="option_id" value="{$id}" class="{$cm_no_hide_input}" />

{if "MULTIVENDOR"|fn_allowed_for}
    {if !$allow_save}
        {assign var="disable_company_picker" value=true}
    {/if}
{/if}

{if $smarty.request.product_id}
    <input class="cm-no-hide-input" type="hidden" name="option_data[product_id]" value="{$smarty.request.product_id}" />
    {if "ULTIMATE"|fn_allowed_for}
        {assign var="disable_company_picker" value=true}
        {if !$company_id}
            {assign var="company_id" value=$product_company_id}
        {/if}
    {/if}
{/if}

<div class="tabs cm-j-tabs">
    <ul class="nav nav-tabs">
        <li id="tab_option_details_{$id}" class="cm-js active"><a>{__("general")}</a></li>
        {if $option_data.option_type == "S" || $option_data.option_type == "R" || $option_data.option_type == "C" || !$option_data}
            <li id="tab_option_variants_{$id}" class="cm-js"><a>{__("variants")}</a></li>
        {/if}
    </ul>
</div>
<div class="cm-tabs-content" id="tabs_content_{$id}">
    <div id="content_tab_option_details_{$id}">
    <fieldset>
        <div class="control-group">
            <input class="cm-no-hide-input" type="hidden" value="{$object}" name="object">
            <label for="elm_option_name_{$id}" class="control-label cm-required">{__("name")}</label>
            <div class="controls">
            <input class="span9" type="text" name="option_data[option_name]" id="elm_option_name_{$id}" value="{$option_data.option_name}"/>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="elm_position_{$id}">{__("position")}</label>
            <div class="controls">
            <input type="text" name="option_data[position]" id="elm_position_{$id}" value="{$option_data.position}" size="3" class="input-small" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="elm_inventory_{$id}">{__("inventory")}</label>
            <input type="hidden" name="option_data[inventory]" value="N" />
            <div class="controls">
            {if "SRC"|strpos:$option_data.option_type !== false}
            <label class="checkbox">
                <input type="checkbox" name="option_data[inventory]" id="elm_inventory_{$id}" value="Y" {if $option_data.inventory == "Y"}checked="checked"{/if}/>
            </label>
            {else}
                <p>-</p>
            {/if}
            </div>
        </div>

        {if "MULTIVENDOR"|fn_allowed_for}
            {assign var="zero_company_id_name_lang_var" value="none"}
        {/if}
        {include file="views/companies/components/company_field.tpl"
            name="option_data[company_id]"
            id="elm_option_data_`$id`"
            disable_company_picker=$disable_company_picker
            selected=$option_data.company_id|default:$company_id
            zero_company_id_name_lang_var=$zero_company_id_name_lang_var
        }

        {if "ULTIMATE"|fn_allowed_for && $runtime.company_id && $shared_product == "Y"}
            <input type="hidden" name="option_data[option_type]" value="{$option_data.option_type}" class="cm-no-hide-input" />
        {/if}
        <div class="control-group">
            <label class="control-label" for="elm_option_type_{$id}">{__("type")}</label>
            <div class="controls">
            {include file="views/product_options/components/option_types.tpl"  name="option_data[option_type]" value=$option_data.option_type display="select" tag_id="elm_option_type_`$id`" check=true}
            </div>
        </div>
        
        <div class="control-group">
            <label class="control-label" for="elm_option_description_{$id}">{__("description")}</label>
            <div class="controls">
            <textarea id="elm_option_description_{$id}" name="option_data[description]" cols="55" rows="8" class="cm-wysiwyg span9">{$option_data.description}</textarea>
            </div>
        </div>
        
        <div class="control-group">
            <label class="control-label" for="elm_option_comment_{$id}">{__("comment")}</label>
            <div class="controls">
            <input type="text" name="option_data[comment]" id="elm_option_comment_{$id}" value="{$option_data.comment}" class="span9" />
            <p class="description">{__("comment_hint")}</p>
            </div>
        </div>
        
        <div class="control-group">
            <label class="control-label" for="elm_option_file_required_{$id}">{__("required")}</label>
            <div class="controls">
            <label class="checkbox">
            <input type="hidden" name="option_data[required]" value="N" /><input type="checkbox" id="elm_option_file_required_{$id}" name="option_data[required]" value="Y" {if $option_data.required == "Y"}checked="checked"{/if}  />
            </label>
            </div>
        </div>
        
        {if !$option_data.option_type || "SRC"|strpos:$option_data.option_type !== false}
            <div class="control-group">
                <label class="control-label" for="elm_option_missing_variants_{$id}">{__("missing_variants_handling")}</label>
                <div class="controls">
                {if "SRC"|strpos:$option_data.option_type !== false}
                    <select id="elm_option_missing_variants_{$id}" name="option_data[missing_variants_handling]">
                        <option value="M" {if $option_data.missing_variants_handling == "M"}selected="selected"{/if}>{__("display_message")}</option>
                        <option value="H" {if $option_data.missing_variants_handling == "H"}selected="selected"{/if}>{__("hide_option_completely")}</option>
                    </select>
                {else}
                <p>-</p>
                {/if}
                </div>
            </div>
        {/if}
        
        <div id="extra_options_{$id}" {if $option_data.option_type != "I" && $option_data.option_type != "T"}class="hidden"{/if}>
            <div class="control-group">
                <label class="control-label" for="elm_option_regexp_{$id}">{__("regexp")}</label>
                <div class="controls">
                <input type="text" name="option_data[regexp]" id="elm_option_regexp_{$id}" value="{$option_data.regexp nofilter}" class="span9" />
                <p class="description">{__("regexp_hint")}</p>
                </div>
            </div>
            
            <div class="control-group">
                <label class="control-label" for="elm_option_inner_hint_{$id}">{__("inner_hint")}</label>
                <div class="controls">
                <input type="text" name="option_data[inner_hint]" id="elm_option_inner_hint_{$id}" value="{$option_data.inner_hint}" class="span9" />
                </div>
            </div>
            
            <div class="control-group">
                <label class="control-label" for="elm_option_incorrect_message_{$id}">{__("incorrect_filling_message")}</label>
                <div class="controls">
                <input type="text" name="option_data[incorrect_message]" id="elm_option_incorrect_message_{$id}" value="{$option_data.incorrect_message}" class="span9" />
            </div>
            </div>
        </div>
        
        <div id="file_options_{$id}" {if $option_data.option_type != "F"}class="hidden"{/if}>
            <div class="control-group">
                <label class="control-label" for="elm_option_allowed_extensions_{$id}">{__("allowed_extensions")}</label>
                <div class="controls">
                <input type="text" name="option_data[allowed_extensions]" id="elm_option_allowed_extensions_{$id}" value="{$option_data.allowed_extensions}" class="span9" />
                <p class="description">{__("allowed_extensions_hint")}</p>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="elm_option_max_uploading_file_size_{$id}">{__("max_uploading_file_size")}</label>
                <div class="controls">
                <input type="text" name="option_data[max_file_size]" id="elm_option_max_uploading_file_size_{$id}" value="{$option_data.max_file_size}" class="span9" />
                <p class="description">{__("max_uploading_file_size_hint")}</p>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="elm_option_multiupload_{$id}">{__("multiupload")}</label>
                <div class="controls">
                <label class="checkbox">
                <input type="hidden" name="option_data[multiupload]" value="N" /><input type="checkbox" id="elm_option_multiupload_{$id}" name="option_data[multiupload]" value="Y" {if $option_data.multiupload == "Y"}checked="checked"{/if}/>
                </label>
                </div>
            </div>
        </div>
        
        {hook name="product_options:properties"}
        {/hook}
    </fieldset>
    <!--content_tab_option_details_{$id}--></div>

     <div class="hidden" id="content_tab_option_variants_{$id}">
     <fieldset>
        <table class="table table-middle">
        <thead>
        <tr class="first-sibling">
            <th class="cm-non-cb{if $option_data.option_type == "C"} hidden{/if}">{__("position_short")}</th>
            <th class="cm-non-cb{if $option_data.option_type == "C"} hidden{/if}">{__("name")}</th>
            <th>{__("modifier")}&nbsp;/&nbsp;{__("type")}</th>
            <th>{__("weight_modifier")}&nbsp;/&nbsp;{__("type")}</th>
            <th class="cm-non-cb{if $option_data.option_type == "C"} hidden{/if}">{__("status")}</th>
            <th>
                <div id="on_st_{$id}" alt="{__("expand_collapse_list")}" title="{__("expand_collapse_list")}" class="hand cm-combinations-options-{$id} exicon-expand"></div><div id="off_st_{$id}" alt="{__("expand_collapse_list")}" title="{__("expand_collapse_list")}" class="hand hidden cm-combinations-options-{$id} exicon-collapse"></div>
            </th>
            <th class="cm-non-cb{if $option_data.option_type == "C"} hidden{/if}">&nbsp;</th>
        </tr>
        </thead>
        {foreach from=$option_data.variants item="vr" name="fe_v"}
        {assign var="num" value=$smarty.foreach.fe_v.iteration}
        <tbody class="hover cm-row-item" id="option_variants_{$id}_{$num}">
        <tr>
            <td class="cm-non-cb{if $option_data.option_type == "C"} hidden{/if}">
                <input type="text" name="option_data[variants][{$num}][position]" value="{$vr.position}" size="3" class="input-micro" /></td>
            <td class="cm-non-cb{if $option_data.option_type == "C"} hidden{/if}">
                <input type="text" name="option_data[variants][{$num}][variant_name]" value="{$vr.variant_name}" class="input-medium" /></td>
            <td class="nowrap {if $runtime.company_id && $shared_product == "Y"} cm-no-hide-input{/if}">
                <input type="text" name="option_data[variants][{$num}][modifier]" value="{$vr.modifier}" size="5" class="input-mini" />&nbsp;/&nbsp;<select class="input-mini" name="option_data[variants][{$num}][modifier_type]">
                    <option value="A" {if $vr.modifier_type == "A"}selected="selected"{/if}>{$currencies.$primary_currency.symbol nofilter}</option>
                    <option value="P" {if $vr.modifier_type == "P"}selected="selected"{/if}>%</option>
                </select>
                {include file="buttons/update_for_all.tpl" display=$show_update_for_all object_id=$vr.variant_id name="update_all_vendors[`$num`]"}
            </td>
            <td class="nowrap">
                <input type="text" name="option_data[variants][{$num}][weight_modifier]" value="{$vr.weight_modifier}" size="5" class="input-mini" />&nbsp;/&nbsp;<select class="input-mini" name="option_data[variants][{$num}][weight_modifier_type]">
                    <option value="A" {if $vr.weight_modifier_type == "A"}selected="selected"{/if}>{$settings.General.weight_symbol}</option>
                    <option value="P" {if $vr.weight_modifier_type == "P"}selected="selected"{/if}>%</option>
                </select>
            </td>
            <td class="cm-non-cb{if $option_data.option_type == "C"} hidden{/if}">
                {include file="common/select_status.tpl" input_name="option_data[variants][`$num`][status]" display="select" obj=$vr meta="input-small"}</td>
            <td class="nowrap">
                <span id="on_extra_option_variants_{$id}_{$num}" alt="{__("expand_collapse_list")}" title="{__("expand_collapse_list")}" class="hand cm-combination-options-{$id}"><span class="exicon-expand"></span></span>
                <span id="off_extra_option_variants_{$id}_{$num}" alt="{__("expand_collapse_list")}" title="{__("expand_collapse_list")}" class="hand hidden cm-combination-options-{$id}"><span class="exicon-collapse"></span> </span>
                <a id="sw_extra_option_variants_{$id}_{$num}" class="cm-combination-options-{$id}">{__("extra")}</a>
                <input type="hidden" name="option_data[variants][{$num}][variant_id]" value="{$vr.variant_id}" class="{$cm_no_hide_input}" />
             </td>
             <td class="right cm-non-cb{if $option_data.option_type == "C"} hidden{/if}">
                {include file="buttons/multiple_buttons.tpl" item_id="option_variants_`$id`_`$num`" tag_level="3" only_delete="Y"}
            </td>
        </tr>
        <tr id="extra_option_variants_{$id}_{$num}" class="cm-ex-op hidden">
            <td colspan="7">
                {hook name="product_options:edit_product_options"}
                <div class="control-group cm-non-cb{if $option_data.option_type == "C"} hidden{/if}">
                    <label class="control-label">{__("icon")}</label>
                    <div class="controls">
                        {include file="common/attach_images.tpl" image_name="variant_image" image_key=$num hide_titles=true no_detailed=true image_object_type="variant_image" image_type="V" image_pair=$vr.image_pair prefix=$id}
                    </div>
                </div>
                {/hook}

            </td>
        </tr>
        </tbody>
        {/foreach}

        {math equation="x + 1" assign="num" x=$num|default:0}{assign var="vr" value=""}
        <tbody class="hover cm-row-item {if $option_data.option_type == "C"}hidden{/if}" id="box_add_variant_{$id}">
        <tr>
            <td class="cm-non-cb{if $option_data.option_type == "C"} hidden{/if}">
                <input type="text" name="option_data[variants][{$num}][position]" value="" size="3" class="input-micro" /></td>
            <td class="cm-non-cb{if $option_data.option_type == "C"} hidden{/if}">
                <input type="text" name="option_data[variants][{$num}][variant_name]" value="" class="input-medium" /></td>
            <td>
                <input type="text" name="option_data[variants][{$num}][modifier]" value="" size="5" class="input-mini" />&nbsp;/
                <select class="input-mini" name="option_data[variants][{$num}][modifier_type]">
                    <option value="A">{$currencies.$primary_currency.symbol nofilter}</option>
                    <option value="P">%</option>
                </select>
            </td>
            <td>
                <input type="text" name="option_data[variants][{$num}][weight_modifier]" value="" size="5" class="input-mini" />&nbsp;/&nbsp;<select class='input-mini' name="option_data[variants][{$num}][weight_modifier_type]">
                    <option value="A">{$settings.General.weight_symbol}</option>
                    <option value="P">%</option>
                </select>
            </td>
            <td class="cm-non-cb{if $option_data.option_type == "C"} hidden{/if}">
                {include file="common/select_status.tpl" input_name="option_data[variants][`$num`][status]" display="select" meta="input-small"}</td>
            <td>
                <span id="on_extra_option_variants_{$id}_{$num}" alt="{__("expand_collapse_list")}" title="{__("expand_collapse_list")}" class="hand cm-combination-options-{$id}"><span class="exicon-expand"></span></span>
                <span id="off_extra_option_variants_{$id}_{$num}" alt="{__("expand_collapse_list")}" title="{__("expand_collapse_list")}" class="hand hidden cm-combination-options-{$id}"><span class="exicon-collapse"></span></span>
                <a id="sw_extra_option_variants_{$id}_{$num}" class="cm-combination-options-{$id}">{__("extra")}</a>
            </td>
            <td class="right cm-non-cb{if $option_data.option_type == "C"} hidden{/if}">
                {include file="buttons/multiple_buttons.tpl" item_id="add_variant_`$id`" tag_level="2"}
            </td>
        </tr>
        <tr id="extra_option_variants_{$id}_{$num}" class="cm-ex-op hidden">
            <td colspan="7">
                {hook name="product_options:edit_product_options"}
                <div class="control-group cm-non-cb">
                    <label class="control-label">{__("icon")}</label>
                    <div class="controls">
                        {include file="common/attach_images.tpl" image_name="variant_image" image_key=$num hide_titles=true no_detailed=true image_object_type="variant_image" image_type="V" prefix=$id}
                    </div>
                </div>
                {/hook}
            </td>
        </tr>
        </tbody>
        </table>
    </fieldset>
    <!--content_tab_option_variants_{$id}--></div>
</div>

<div class="buttons-container">
    {if $id}
        {if !$allow_save && $shared_product != "Y"}
            {assign var="hide_first_button" value=true}
        {/if}
    {/if}
    {include file="buttons/save_cancel.tpl" but_name="dispatch[product_options.update]" cancel_action="close" extra="" hide_first_button=$hide_first_button save=$id}
</div>

</form>

<!--content_group_product_option_{$id}--></div>
