
{if $container}
    {assign var="id" value=$container.container_id}
{else}
    {assign var="id" value=0}
{/if}

<div id="container_properties_{$id}">
<form action="{""|fn_url}" method="post" class="form-horizontal form-edit " name="container_update_form">
<input type="hidden" name="container_data[container_id]" value="{$id}" />

<div class="tabs cm-j-tabs">
    <ul class="nav nav-tabs">
        <li class="cm-js active"><a>{__("general")}</a></li>
    </ul>
</div>

<div class="cm-tabs-content">
<fieldset>
    <div class="control-group cm-no-hide-input">
        <label class="control-label" for="elm_container_user_class_{$id}">{__("user_class")}</label>
        <div class="controls">
        <input class="input-text" type="text" id="elm_container_user_class_{$id}" name="container_data[user_class]" value="{$container.user_class}"/>
        </div>
    </div>

</fieldset>
</div>

<div class="buttons-container">
    {include file="buttons/save_cancel.tpl" but_name="dispatch[block_manager.update_location]" cancel_action="close" but_meta="cm-dialog-closer" save=$id}
</div>
</form>
<!--container_properties_{$id}--></div>
