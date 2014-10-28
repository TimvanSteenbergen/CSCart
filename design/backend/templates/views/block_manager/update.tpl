{if $block}
    {assign var="id" value=$block.block_id}
{else}
    {assign var="id" value=0}
{/if}

{assign var="block_type" value=$block.block_type|default:$block_type}
<div id="content_group{$id}{$block_type}_{$location}">
<form action="{""|fn_url}" method="post" class="form-horizontal form-edit " name="block_{$location}_{$id}{$block_type}_update_form">
<input type="hidden" name="block[block_type]" value="{$block_type}" />
{assign var="js_param" value="false"}
{if !$id}
    {assign var="js_param" value="true"}
    <input type="hidden" name="add_selected_section" id="add_selected_section_{$id}{$block_type}" value="{$location|default:"all_pages"}" />
{else}
    <input type="hidden" name="block[block_id]" value="{$id}" />
    <input type="hidden" name="redirect_location" value="{$location}" />
    <input type="hidden" name="block[location]" value="{$block.location}" />


    <script type="text/javascript">
    block_properties['{$location}_{$id}{$block_type}_'] = {$block.properties|json_encode};
    block_location['{$location}_{$id}{$block_type}_'] = '{$block.location}';
    block_properties_used['{$location}_{$id}{$block_type}_'] = false;
    </script>
{/if}
{if $redirect_url}
    <input type="hidden" name="redirect_url" value="{$redirect_url}" />
{/if}

    <div class="tabs cm-j-tabs">
        <ul class="nav nav-tabs">
            <li class="cm-js active"><a>{__("general")}</a></li>
        </ul>
    </div>

    <div class="cm-tabs-content">
            <div class="control-group">
            <label for="{$location}_{$id}{$block_type}_block_name" class="control-label cm-required">{__("name")}</label>
            <div class="controls">
            <input type="text" name="block[description]" id="{$location}_{$id}{$block_type}_block_name" size="25" value="{$block.description}" />
            </div>
        </div>
    {if $block.text_id != "central_content"}
        {if $block_type == "B"}
        <div class="control-group pull-left">
            <label class="control-label" for="{$location}_{$id}{$block_type}_block_object">{__("block_content")}</label>
            <div class="controls">
            <select name="block[list_object]" id="{$location}_{$id}{$block_type}_block_object" onchange="fn_check_block_params({$js_param}, '{$location}', {$id}, this, '{$block_type}'); fn_get_specific_settings(this.value, {$id}, 'list_object', '{$block_type}');">
            <optgroup label="{__("list_objects")}">
                {foreach from=$block_settings.dynamic key="object_name" item="listed_block"}
                    <option value="{$object_name}"{if $block.properties.list_object == $object_name} selected="selected"{/if}>{if $listed_block.object_description}{__($listed_block.object_description)}{else}{__($object_name)}{/if}</option>
                {/foreach}
            </optgroup>
            <optgroup label="{__("standard_sidebox")}">
                {foreach from=$block_settings.static item="static_block"}
                    <option value="{$static_block.template}" {if $block.properties.list_object == $static_block.template}selected="selected"{/if}>{$static_block.name}</option>
                {/foreach}
            </optgroup>
            {foreach from=$block_settings.additional key="section" item="section_data"}
                <optgroup label="{__($section)}">
                {foreach from=$section_data.items key="object_name" item="additional_block"}
                    <option value="{$additional_block.template}" {if $block.properties.list_object == $additional_block.template}selected="selected"{/if}>{$additional_block.name}</option>
                {/foreach}
                </optgroup>
            {/foreach}
            </select>
            </div>
        </div>
        {assign var="index" value=$block.properties.list_object|default:"products"}
        {include file="views/block_manager/specific_settings.tpl" spec_settings=$specific_settings.list_object[$index] s_set_id="`$id``$block_type`_list_object"}

        <div class="control-group pull-left">
            <label class="control-label" for="{$location}_{$id}{$block_type}_id_fillings">{__("filling")}</label>
            <div class="controls">
            <select name="block[fillings]" id="{$location}_{$id}{$block_type}_id_fillings" onchange="fn_check_block_params({$js_param}, '{$location}', {$id}, this, '{$block_type}');">
            </select>
            </div>
        </div>

        {assign var="index" value=$block.properties.fillings|default:"manually"}
        {include file="views/block_manager/specific_settings.tpl" spec_settings=$specific_settings.fillings[$index] s_set_id="`$id``$block_type`_fillings"}
        {/if}

        {if $location != "product_details" && $block.text_id == ""}
            <div class="control-group">
                <label class="control-label" for="{$location}_{$id}{$block_type}_id_positions">{__("group")}</label>
                <div class="controls">
                <select name="block[group_id]" id="{$location}_{$id}{$block_type}_id_positions"{if $id} onchange="fn_check_block_parent({$id}, this, '{$location}', {$object_id});"{/if}>
                {foreach from=$avail_positions item="pos"}
                    {if !$pos.parent_id}
                    <option value="{$pos.block_id}"{if $block_parent == $pos.block_id} selected="selected"{/if}>{$pos.description}</option>
                        {if $block_type == "B"}
                        {foreach from=$avail_positions item="pos_child"}
                            {if $pos_child.parent_id == $pos.block_id}
                            <option value="{$pos_child.block_id}"{if $block_parent == $pos_child.block_id} selected="selected"{/if}>{$pos_child.description|escape|indent:1:"&#166;&nbsp;&nbsp;&nbsp;&nbsp;":"&#166;--&nbsp;" nofilter}</option>
                            {/if}
                        {/foreach}
                        {/if}
                    {/if}
                {/foreach}
                </select>
                {if $id}
                <input id="{$location}_{$id}{$block_type}_id_positions_rewrite" type="hidden" name="block[rewrite_positions]" value="N" />
                {/if}
                </div>
            </div>
        {/if}

        {if $block_type == "B"}
        <div class="control-group pull-left">
            <label class="control-label" for="{$location}_{$id}{$block_type}_id_appearances">{__("appearance_type")}</label>
            <div class="controls">
            <select name="block[appearances]" id="{$location}_{$id}{$block_type}_id_appearances" onchange="fn_get_specific_settings(this.value, {$id}, 'appearances', '{$block_type}');">
            </select>
            </div>
        </div>

        {assign var="index" value=$block.properties.appearances|default:"blocks/products_text_links.tpl"}
        {include file="views/block_manager/specific_settings.tpl" spec_settings=$specific_settings.appearances[$index] s_set_id="`$id``$block_type`_appearances"}

        {else}
        <div class="control-group">
            <label class="control-label" for="{$location}_{$id}{$block_type}_id_block_order">{__("block_order")}</label>
            <div class="controls">
            <select name="block[block_order]" id="{$location}_{$id}{$block_type}_id_block_order">
                <option value="H"{if $block.properties.block_order == "H"} selected="selected"{/if}>{__("horizontal")}</option>
                <option value="V"{if $block.properties.block_order == "V"} selected="selected"{/if}>{__("vertical")}</option>
            </select>
            </div>
        </div>
        {/if}
    {/if}

        <div class="control-group">
            <label class="control-label" for="{$location}_{$id}{$block_type}_id_wrapper">{__("wrapper")}</label>
            <div class="controls">
            <select name="block[wrapper]" id="{$location}_{$id}{$block_type}_id_wrapper">
                <option value="">--</option>
                {foreach from=$block_settings.wrappers item="w"}
                <option value="{$w}" {if $block.properties.wrapper == $w}selected="selected"{/if}>{$w}</option>
                {/foreach}
            </select>
            </div>
        </div>

        {if $block_type == "B"}
        <div class="control-group">
            <label class="control-label" for="{$location}_{$id}{$block_type}_block_width">{__("block_width")}</label>
            <div class="controls">
            <input type="text" name="block[width]" id="{$location}_{$id}{$block_type}_block_width" size="25" value="{$block.properties.width}" class="input-text-short cm-value-integer" />
            <select name="block[width_unit]">
                <option value="P"{if $block.properties.width_unit == "P"} selected="selected"{/if}>{__("percent")|lower}</option>
                <option value="A"{if $block.properties.width_unit == "A"} selected="selected"{/if}>{__("pixels")}</option>
            </select>
            </div>
        </div>
        {/if}

       {hook name="block_manager:settings"}
        {/hook}
    </div>

    {if $block.text_id != "central_content"}
    <script type="text/javascript">
    Tygh.$(document).ready(function() {$ldelim}
        fn_check_block_params({$js_param}, '{$location}', {$id}, null, '{$block_type}');
    {$rdelim});
    </script>
    {/if}
    <div class="buttons-container">
        {if !$id}
            {include file="buttons/save_cancel.tpl" but_name="dispatch[block_manager.add]" cancel_action="close"}
        {else}
            {include file="buttons/save_cancel.tpl" but_name="dispatch[block_manager.update]" cancel_action="close" save=$id}
        {/if}
    </div>
</form>
<!--content_group{$id}{$block_type}_{$location}--></div>