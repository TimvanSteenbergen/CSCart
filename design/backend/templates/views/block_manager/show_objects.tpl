<div id="show_objects_{$block_id}_{$object_type}">

        {include file="common/pagination.tpl" save_current_page=true disable_history=true div_id="block_content_`$block.block_id`_picker"}

        {include_ext
            file=$dynamic_object_scheme.picker
            checkbox_name=$dynamic_object_scheme.key
            input_name="snapping_data[object_ids]"
            item_ids=$object_ids
            view_mode=$dynamic_object_scheme.picker_params.view_mode|default:"list"
            params_array=$dynamic_object_scheme.picker_params
            hide_delete_button=true}

    {include file="common/pagination.tpl" disable_history=true div_id="block_content_`$block.block_id`_picker"}

    <div class="buttons-container">
    {include file="buttons/save_cancel.tpl" hide_first_button=true but_name="dispatch[block_manager.update_block]" cancel_action="close" save=true}
    </div>
<!--show_objects_{$block_id}_{$bloc_type}--></div>
