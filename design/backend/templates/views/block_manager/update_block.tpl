{if $block}
    {assign var="id" value=$block.block_id}
{else}
    {assign var="id" value=0}
{/if}

{assign var="snapping_id" value=$snapping_data.snapping_id|default:"0"}
{assign var="html_id" value="`$id`_`$snapping_id`_`$block.type`"}

{if $id == 0}
    {assign var="hide_status" value=true}
{/if}

{if $smarty.request.active_tab}
    {assign var="active_tab" value=$smarty.request.active_tab}
{else}
    {assign var="active_tab" value='block_general'}
{/if}

{script src="js/tygh/tabs.js"}

{if $smarty.request.dynamic_object.object_id > 0}
    {assign var="dynamic_object" value=$smarty.request.dynamic_object}
{/if}

{capture name="block_content"}
    {if $block_scheme.content}
        {include file="views/block_manager/components/block_content.tpl" content_type=$block.properties.content_type block_scheme=$block_scheme block=$block editable=$editable_content tab_id="`$html_id`"}
    {/if}
{/capture}

<form action="{""|fn_url}" method="post" class="form-horizontal form-edit cm-skip-check-items {if $dynamic_object}cm-hide-inputs{/if}  {if $smarty.request.ajax_update}cm-ajax cm-form-dialog-closer{/if}" name="block_{$id}_update_form">
<div id="block_properties_{$html_id}">
    {if $smarty.request.dynamic_object.object_id > 0}
        <input type="hidden" name="dynamic_object[object_id]" value="{$smarty.request.dynamic_object.object_id}" class="cm-no-hide-input"/>    
        <input type="hidden" name="dynamic_object[object_type]" value="{$smarty.request.dynamic_object.object_type}" class="cm-no-hide-input"/>    
    {/if}
    <input type="hidden" name="block_data[type]" value="{$block.type}" class="cm-no-hide-input"/>
    <input type="hidden" name="block_data[block_id]" value="{$id}" class="cm-no-hide-input"/>
    <input type="hidden" name="block_data[content_data][snapping_id]" value="{$snapping_data.snapping_id}" class="cm-no-hide-input"/>    

    {if !$block_scheme.multilanguage}
        <input type="hidden" name="block_data[apply_to_all_langs]" value="Y" />
    {/if}
    
    <input type="hidden" name="snapping_data[snapping_id]" value="{$snapping_data.snapping_id}" class="cm-no-hide-input"/>
    <input type="hidden" name="snapping_data[grid_id]" value="{$snapping_data.grid_id}" class="cm-no-hide-input"/>
    <input type="hidden" name="selected_location" value="{$smarty.request.selected_location|default:0}" class="cm-no-hide-input" />
    {if $smarty.request.assign_to}
        <input type="hidden" name="assign_to" value="{$smarty.request.assign_to}" class="cm-no-hide-input"/>
    {/if}
    <input type="hidden" name="result_ids" value="block_properties_{$html_id}" class="cm-no-hide-input"/>

    
    {* Redirect to product tabs *}
    {if $smarty.request.r_url}
        <input type="hidden" name="r_url" value="{$smarty.request.r_url}" class="cm-no-hide-input"/>
    {/if}
    <div class="tabs cm-j-tabs cm-track">
        <ul class="nav nav-tabs">
            <li id="block_general_{$html_id}" class="cm-js{if $active_tab == "block_general_`$html_id`"} active{/if}"><a>{__("general")}</a></li>
            {if $smarty.capture.block_content|trim}<li id="block_contents_{$html_id}" class="cm-js{if $active_tab == "block_contents_`$html_id`"} active{/if}"><a>{__("content")}</a></li>{/if}
            {if $block_scheme.settings}
                <li id="block_settings_{$html_id}" class="cm-js{if $active_tab == "block_settings_`$html_id`"} active{/if}"><a>{__("block_settings")}</a></li>
            {/if}
            {if $dynamic_object_scheme && !$hide_status}
                <li id="block_status_{$html_id}" class="cm-js{if $active_tab == "block_status_`$html_id`"} active{/if}"><a>{__("status")}</a></li>
            {/if}
        </ul>
    </div>

    <div class="cm-tabs-content" id="tabs_content_block_{$html_id}">
        <div id="content_block_general_{$html_id}">
            <div class="control-group {if $editable_template_name}cm-no-hide-input{/if}">
                <label for="block_{$html_id}_name" class="control-label cm-required">{__("name")}</label>
                <div class="controls">
                {if $smarty.request.html_id && $id > 0}
                    <div class="text-type-value">{$block.name}</div>
                {else}
                    <input type="text" name="block_data[description][name]" id="block_{$html_id}_name" class="span9" size="25" value="{$block.name}" />
                {/if}
                </div>
            </div>
            {if $block_scheme.templates}
                <div class="control-group {if $editable_template_name}cm-no-hide-input{/if}">
                    <label class="control-label" for="block_{$html_id}_template">{__("template")}</label>
                    <div class="controls">
                    {if $block_scheme.templates|is_array}
                        <select id="block_{$html_id}_template" name="block_data[properties][template]"  class="cm-reload-form">
                            {foreach from=$block_scheme.templates item=v key=k}
                                <option value="{$k}" {if $block.properties.template == $k}selected="selected"{/if}>{if $v.name}{$v.name}{else}{$k}{/if}</option>
                            {/foreach}
                        </select>
                    {/if}
                    {if $dynamic_object}
                        <input type="hidden" name="block_data[properties][template]" value="{$block.properties.template}" class="cm-no-hide-input" />
                    {/if}
                    {if $block_scheme.templates[$block.properties.template].settings|is_array}
                        <a href="#" id="sw_case_settings_{$html_id}" class="open cm-combination" onclick="return false">
                            {__("settings")}
                            <span class="combo-arrow"></span>
                        </a>
                    {/if}
                    </div>
                </div>
            {/if}
            
            {if $block_scheme.templates[$block.properties.template].settings|is_array}        
                <div id="case_settings_{$html_id}" class="hidden">
                {foreach from=$block_scheme.templates[$block.properties.template].settings item=setting_data key=name}
                    {include file="views/block_manager/components/setting_element.tpl" option=$setting_data name=$name block=$block html_id="block_`$html_id`_properties_`$name`" html_name="block_data[properties][`$name`]" editable=$editable_template_name value=$block.properties.$name}
                {/foreach}
                </div>
            {/if}
            {if $editable_wrapper}
                <div class="control-group">
                    <label class="control-label" for="block_{$html_id}_wrapper">{__("wrapper")}</label>
                    <div class="controls">
                    <select name="snapping_data[wrapper]" id="block_{$html_id}_wrapper">
                        <option value="">--</option>
                        {foreach from=$block_scheme.wrappers item=w key=k}                            
                            <option value="{$k}" {if $block.wrapper == $k}selected="selected"{/if}>{$w.name}</option>
                        {/foreach}
                    </select>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="block_{$html_id}_user_class">{__("user_class")}</label>
                    <div class="controls">
                    <input type="text" name="snapping_data[user_class]" id="block_{$html_id}_user_class" size="25" value="{$block.user_class}"/>
                    </div>
                </div>
            {/if}            
            {hook name="block_manager:settings"}
            {/hook}
        </div>
        {if $smarty.capture.block_content}
            <div id="content_block_contents_{$html_id}" >
                {if $dynamic_object.object_id > 0}
                    <input type="hidden" name="block_data[content_data][object_id]" value="{$dynamic_object.object_id}" class="cm-no-hide-input" />
                    <input type="hidden" name="block_data[content_data][object_type]" value="{$dynamic_object.object_type}" class="cm-no-hide-input" />
                {/if}
                {if $block.object_id > 0}
                    <div class="text-tip">                
                        {assign var="url" value="`$dynamic_object_scheme.customer_dispatch`&`$dynamic_object_scheme.key`=`$dynamic_object.object_id`"|fn_url:'C':'http':$smarty.const.DESCR_SL}
                        {__("dynamic_content", ["[url]" => $url])}
                    </div>
                {/if}

                {$smarty.capture.block_content nofilter}

                {capture name="content_stat"}{strip}
                    {foreach from=$changed_content_stat item=stat}
                        {if $stat.object_type != ''}
                            <div>
                                {include file="common/popupbox.tpl"
                                    id="show_objects_`$block.block_id`_`$stat.object_type`"
                                    text=__($stat.object_type)
                                    link_text="`$stat.contents_count`"
                                    act="link"
                                    href="block_manager.show_objects?object_type=`$stat.object_type`&block_id=`$block.block_id`"
                                    opener_ajax_class="cm-ajax"
                                    link_class="cm-ajax-force"
                                    content=""
                                } {$stat.object_type}
                            </div>
                        {/if}
                    {/foreach}
                {/strip}{/capture}

                {if $smarty.capture.content_stat}
                <div class="control-group">
                    <label class="control-label" for="block_{$html_id}_override_by_this">{__("override_by_this")}</label>
                    <div class="controls">
                        <input type="hidden" class="cm-no-hide-input" name="block_data[content_data][override_by_this]" value="N" />
                        <input id="block_{$html_id}_override_by_this" type="checkbox" class="cm-no-hide-input" name="block_data[content_data][override_by_this]" value="Y" />
                    </div>
                </div>
                <div class="statistics-box">
                    <div class="statistics-body">
                        <p class="strong">{__("content_changed_for")}</p>
                        {$smarty.capture.content_stat nofilter}
                    </div>
                </div>
                {/if}
            </div>
        {/if}
        {if $block_scheme.settings}
            <div id="content_block_settings_{$html_id}" >
                    {foreach from=$block_scheme.settings item=setting_data key=name}
                        {include file="views/block_manager/components/setting_element.tpl" option=$setting_data name=$name block=$block html_id="block_`$html_id`_properties_`$name`" html_name="block_data[properties][`$name`]" editable=$editable_template_name value=$block.properties.$name}
                    {/foreach}
            </div>
        {/if}
        {if $dynamic_object_scheme && !$hide_status}
        <div id="content_block_status_{$html_id}" >
            <div class="control-group">
                <label class="control-label">{__("global_status")}:</label>
                <div class="controls">
                    <p>
                        {if $block.status == 'A'}{__("active")}{else}{__("disabled")}{/if}
                    </p>
                </div>
            </div>
            <input type="hidden" class="cm-no-hide-input" name="snapping_data[object_type]" value="{$dynamic_object_scheme.object_type}" />
            <div class="control-group cm-no-hide-input">                        
                <label class="control-label">{if $block.status == 'A'}{__("disable_for")}{else}{__("enable_for")}{/if}</label>
                <div class="controls">
                {include_ext
                    file=$dynamic_object_scheme.picker
                    data_id="block_`$html_id`_object_ids_d"
                    input_name="snapping_data[object_ids]"
                    item_ids=$block.object_ids
                    view_mode="links"
                    params_array=$dynamic_object_scheme.picker_params
                    start_pos=$start_position
                }
                </div>
            </div>
        </div>
        {/if}
    </div>
    <!--block_properties_{$html_id}--></div>
    <div class="buttons-container">
        {if $smarty.request.force_close}
            {include file="buttons/save_cancel.tpl" but_name="dispatch[block_manager.update_block]" cancel_action="close" save=$id}
        {else}
            {include file="buttons/save_cancel.tpl" but_name="dispatch[block_manager.update_block]" cancel_action="close" save=$id}
        {/if}
    </div>
</form>
