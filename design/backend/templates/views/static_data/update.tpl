{if $static_data}
    {assign var="id" value=$static_data.param_id}
{else}
    {assign var="id" value="0"}
{/if}

<div id="content_group{$id}">

<form action="{""|fn_url}" method="post" name="static_data_form_{$id}" enctype="multipart/form-data" class=" form-horizontal">
<input name="section" type="hidden" value="{$section}" />
<input name="param_id" type="hidden" value="{$id}" />

<div class="tabs cm-j-tabs">
    <ul class="nav nav-tabs">
        <li id="tab_general_{$id}" class="cm-js active"><a>{__("general")}</a></li>
    </ul>
</div>

<div class="cm-tabs-content" id="content_tab_general_{$id}">
<fieldset>

    {if $section_data.owner_object}
        {assign var="param_name" value=$section_data.owner_object.param}
        {assign var="request_key" value=$section_data.owner_object.key}    
        {assign var="value" value=$smarty.request.$request_key}

        {*if $static_data[$param_name]}
            {assign var="value" value=$static_data[$param_name]}        
        {/if*}
        
        <input type="hidden" name="static_data[{$param_name}]" value="{$value}" class="input-text-large" />
        <input type="hidden" name="{$request_key}" value="{$value}" class="input-text-large" />
    {/if}

    {if $section_data.multi_level}
    <div class="control-group">
        <label for="parent_{$id}" class="cm-required control-label">{__("parent_item")}:</label>
            <div class="controls">
                <select id="parent_{$id}" name="static_data[parent_id]">
                    <option value="0">- {__("root_level")} -</option>
                    {foreach from=$parent_items item="i"}
                        {if ($i.id_path|strpos:"`$static_data.id_path`/" === false || $static_data.id_path == "") && $i.param_id != $static_data.param_id || !$id}
                            <option value="{$i.param_id}" {if $static_data.parent_id == $i.param_id}selected="selected"{/if}>{$i.descr|escape|indent:$i.level:"&#166;&nbsp;&nbsp;&nbsp;&nbsp;":"&#166;--&nbsp;" nofilter}</option>
                        {/if}
                    {/foreach}
                </select>
            </div>
    </div>
    {/if}

    <div class="control-group">
        <label for="descr_{$id}" class="cm-required control-label">{__($section_data.descr)}:</label>
        <div class="controls">
            <input type="text" size="40" id="descr_{$id}" name="static_data[descr]" value="{$static_data.descr}" class="input-text-large main-input">
        </div>
    </div>
    {if $section_data.multi_level}
    <div class="control-group">
        <label for="position_{$id}" class="control-label">{__("position_short")}:</label>
        <div class="controls">
            <input type="text" size="2" id="position_{$id}" name="static_data[position]" value="{$static_data.position}" class="input-text-short">
        </div>
    </div>
    {/if}
    <div class="control-group">
        <label for="param_{$id}" class="control-label">{__($section_data.param)}{if $section_data.tooltip}{include file="common/tooltip.tpl" tooltip=__($section_data.tooltip)}{/if}:</label>
        <div class="controls">
            <input type="text" size="40" id="param_{$id}" name="static_data[param]" value="{$static_data.param}" class="input-text-large">
        </div>
    </div>

    {if $section_data.icon}
    <div class="control-group">
        <label class="control-label">{__($section_data.icon.title)}:</label>
        <div class="controls">
            {include file="common/attach_images.tpl" image_name="static_data_icon" image_object_type="static_data_`$section`" image_pair=$static_data.icon no_detailed="Y" hide_titles="Y" image_key=$id image_object_id=$id}
        </div>
    </div>
    {/if}

    {if $section_data.additional_params}
    {foreach from=$section_data.additional_params key="k" item="p"}
        {if $p.type == "hidden"}    
            <input type="hidden" id="param_{$k}_{$id}" name="static_data[{$p.name}]" value="{$static_data[$p.name]}" class="input-text-large" />
        {else}
            <div class="control-group">
                <label for="param_{$k}_{$id}" class="control-label">{__($p.title)}{if $p.tooltip}{include file="common/tooltip.tpl" tooltip=__($p.tooltip)}{/if}:</label>
                <div class="controls mixed-controls cm-bs-group">
                    {if $p.type == "checkbox"}
                        <input type="hidden" name="static_data[{$p.name}]" value="N" />
                        <input type="checkbox" id="param_{$k}_{$id}" name="static_data[{$p.name}]" value="Y" {if $static_data[$p.name] == "Y"}checked="checked"{/if} class="checkbox" />
                    {elseif $p.type == "megabox"}
                        {assign var="_megabox_values" value=$static_data[$p.name]|fn_static_data_megabox}
                        <div class="cm-bs-container form-inline clearfix">
                            <label class="radio pull-left">
                                <input type="radio" class="cm-bs-trigger" name="static_data[megabox][type][{$p.name}]" {if !$_megabox_values}checked="checked"{/if} value="" onclick="Tygh.$('#un_{$id}').prop('disabled', true);">
                                {__("none")}
                            </label>
                        </div>
                        
                        <div class="cm-bs-container form-inline clearfix">
                            <label class="radio pull-left">
                                <input type="radio" class="cm-bs-trigger" name="static_data[megabox][type][{$p.name}]" {if $_megabox_values.types.C}checked="checked"{/if} value="C" onclick="Tygh.$('#un_{$id}').prop('disabled', false);">
                                {__("category")}:
                            </label>
                            <div class="cm-bs-block pull-left disable-overlay-wrap">
                                {include file="pickers/categories/picker.tpl" data_id="megabox_category_`$id`" input_name="static_data[`$p.name`][C]" item_ids=$_megabox_values.types.C.value hide_link=true hide_delete_button=true default_name=__("all_categories") extra=""}
                                <div class="disable-overlay cm-bs-off"></div>
                            </div>
                        </div>
                
                        <div class="cm-bs-container form-inline clearfix">
                            <label class="radio pull-left">
                                <input type="radio" class="cm-bs-trigger" name="static_data[megabox][type][{$p.name}]" {if $_megabox_values.types.A}checked="checked"{/if} value="A" onclick="Tygh.$('#un_{$id}').prop('disabled', false);">
                                {__("page")}:
                            </label>
                            <div class="cm-bs-block pull-left disable-overlay-wrap">
                                {include file="pickers/pages/picker.tpl" data_id="megabox_page_`$id`" input_name="static_data[`$p.name`][A]" item_ids=$_megabox_values.types.A.value hide_link=true hide_delete_button=true default_name=__("all_pages") extra="" no_container=true prepend=true}
                                <div class="disable-overlay cm-bs-off"></div>
                            </div>
                        </div>
                        <br />
                        <label for="un_{$id}" class="checkbox clearfix">
                            <input type="hidden" name="static_data[megabox][use_item][{$p.name}]" value="N" />
                            <input type="checkbox" name="static_data[megabox][use_item][{$p.name}]" id="un_{$id}" {if $_megabox_values.use_item == "Y"}checked="checked"{/if} value="Y" {if !$_megabox_values}disabled="disabled"{/if}>{__("static_data_use_item")}
                        </label>

                    {elseif $p.type == "select"}
                        <select id="param_{$k}_{$id}" name="static_data[{$p.name}]">
                        {foreach from=$p.values key="vk" item="vv"}
                        <option    value="{$vk}" {if $static_data[$p.name] == $vk}selected="selected"{/if}>{__($vv)}</option>
                        {/foreach}
                        </select>
                    {elseif $p.type == "input"}
                        <input type="text" id="param_{$k}_{$id}" name="static_data[{$p.name}]" value="{$static_data[$p.name]}" class="input-text-large" />
                    {/if}
                </div>
            </div>        
        {/if}
    {/foreach}
    {/if}

    {if $section_data.has_localization}
        {include file="views/localizations/components/select.tpl" data_name="static_data[localization]" data_from=$static_data.localization}
    {/if}
</fieldset>
<!--content_tab_general_{$id}--></div>

{if ""|fn_allow_save_object:"static_data":$section_data.skip_edition_checking}
    <div class="buttons-container">
        {include file="buttons/save_cancel.tpl" but_name="dispatch[static_data.update]" cancel_action="close" save=$id}
    </div>
{/if}

</form>
<!--content_group{$id}--></div>
