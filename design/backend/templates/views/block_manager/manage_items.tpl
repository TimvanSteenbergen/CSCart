<div id="content_edit_block_picker_{$block.block_id}">
<form action="{""|fn_url}" method="post" class="" name="block_{$location}_{$block_data.block_id}_update_form">
<input type="hidden" name="block_id" value="{$block.block_id}" />
<input type="hidden" name="block_location" value="{$block.location}" />
<input type="hidden" name="redirect_location" value="{$location}" />
<input type="hidden" name="object_id" value="{$object_id}" />
<input type="hidden" name="redirect_url" value="{$redir_url}" />
<input type="hidden" name="is_manage" value="Y" />

{if $block.properties.per_object}
    <fieldset>
        <textarea id="block_text_{$block.block_id}" name="block_items[block_data][block_text]" cols="65" rows="8" class="cm-wysiwyg input-textarea">{$block.item_ids.block_text}</textarea>
    <fieldset>
{else}
    {include file="common/pagination.tpl" save_current_page=true disable_history=true div_id="block_content_`$block.block_id`_picker"}

    {include_ext file=$block_settings.dynamic[$block.properties.list_object].picker_props.picker data_id="added_`$location`_`$block.block_id`" input_name="block_items" item_ids=$block_items no_js=true positions=true view_mode=$block_settings.dynamic[$block.properties.list_object].picker_props.params.view_mode|default:"list" params_array=$block_settings.dynamic[$block.properties.list_object].picker_props.params start_pos=$start_position}

    {include file="common/pagination.tpl" disable_history=true div_id="block_content_`$block.block_id`_picker"}
{/if}

<div class="buttons-container">
    {include file="buttons/save_cancel.tpl" but_name="dispatch[block_manager.add_items]" cancel_action="close"}
</div>
</form>
<!--content_edit_block_picker_{$block.block_id}--></div>
