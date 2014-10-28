{if $menu_data}
    {assign var="id" value=$menu_data.menu_id}
{else}
    {assign var="id" value=0}
{/if}


<form action="{""|fn_url}" name="update_product_menu_form_{$id}" method="post" class="cm-skip-check-items  form-horizontal">
<div id="content_group_menu_{$id}">

<input type="hidden" name="menu_data[menu_id]" value="{$id}" />
<input type="hidden" name="result_ids" value="content_group_menu_{$id}" />

<fieldset>
    <div class="control-group">
        <label class="cm-required control-label" for="elm_menu_name_{$id}">{__("name")}:</label>
        <div class="controls">
            <input type="text" name="menu_data[name]" value="{$menu_data.name}" id="elm_menu_name_{$id}" class="input-text" size="18" />
        </div>
    </div>

    {include file="common/select_status.tpl" input_name="menu_data[status]" id="elm_menu_status_{$id}" obj=$menu_data}

</fieldset>

<!--content_group_menu_{$id}--></div>
<div class="buttons-container">
    {include file="buttons/save_cancel.tpl" but_name="dispatch[menus.update]" cancel_action="close" save=$id}
</div>
</form>
