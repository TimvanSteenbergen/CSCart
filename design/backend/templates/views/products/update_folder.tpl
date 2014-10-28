{if $product_file_folder}
    {assign var="id" value=$product_file_folder.folder_id}
{else}
    {assign var="id" value=0}
{/if}

<form action="{""|fn_url}" method="post" class="form-horizontal form-edit cm-check-changes {if !$product_file_folder|fn_allow_save_object:""} cm-hide-inputs{/if}" name="folders_form_{$id}" enctype="multipart/form-data">
<input type="hidden" name="product_id" value="{$product_id}" />
<input type="hidden" name="selected_section" value="files" />
<input type="hidden" name="folder_id" value="{$id}" />
<input type="hidden" name="product_file_folder[product_id]" value="{$product_id}" />

<div class="tabs cm-j-tabs">
    <ul class="nav nav-tabs">
        <li id="tab_details_{$id}" class="cm-js active"><a>{__("general")}</a></li>
    </ul>
</div>

<div class="cm-tabs-content" id="tabs_content_{$id}">
    <div id="content_tab_details_{$id}">

        <div class="control-group ">
            <label for="elm_folder_name_{$id}" class="control-label cm-required">{__("name")}</label>
            <div class="controls">
                <input type="text" name="product_file_folder[folder_name]" id="elm_folder_name_{$id}" value="{$product_file_folder.folder_name}" class="span9" />
            </div>
        </div>

        <div class="control-group ">
            <label class="control-label" for="elm_folder_position_{$id}">{__("position")}</label>
            <div class="controls">
                <input type="text" name="product_file_folder[position]" id="elm_folder_position_{$id}" value="{$product_file_folder.position}" size="3" class="input-medium" />
            </div>
        </div>

        {hook name="product_file_folders:properties"}
        {/hook}
    </div>
</div>

<div class="modal-footer buttons-container">
{if $product_file_folder|fn_allow_save_object:""}
    {include file="buttons/save_cancel.tpl" but_name="dispatch[products.update_folder]" cancel_action="close" save=$id}
{/if}

</div>

</form>