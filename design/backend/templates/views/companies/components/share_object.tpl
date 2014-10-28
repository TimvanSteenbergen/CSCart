<div id="{$result_ids}">
    <input type="hidden" value="{$selected_companies|@count}" name="selected_companies_count">
    {if !$runtime.company_id}
        {assign var="show_add_button" value=true}
    {/if}
    {include file="pickers/companies/picker.tpl" data_id="share" input_name="share_objects[`$object`][`$object_id`]" item_ids=$selected_companies no_js=true positions=false view_mode="list" hide_edit_button=true view_only=$runtime.company_id multiple=true hidden_field=true show_add_button=$show_add_button no_item_text=$no_item_text}
<!--{$result_ids}--></div>