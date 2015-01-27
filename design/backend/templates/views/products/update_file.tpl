{if $product_file}
    {assign var="id" value=$product_file.file_id}
{else}
    {assign var="id" value=0}
{/if}

<form action="{""|fn_url}" method="post" class="form-horizontal form-edit cm-check-changes {if !$product_file|fn_allow_save_object:""}  cm-hide-inputs{/if}" name="files_form_{$id}" enctype="multipart/form-data">
<input type="hidden" name="product_id" value="{$product_id}" />
<input type="hidden" name="selected_section" value="files" />
<input type="hidden" name="file_id" value="{$id}" />
<input type="hidden" name="product_file[product_id]" value="{$product_id}" />

<div class="tabs cm-j-tabs">
    <ul class="nav nav-tabs">
        <li id="tab_details_{$id}" class="cm-js active"><a>{__("general")}</a></li>
    </ul>
</div>

<div class="cm-tabs-content" id="tabs_content_{$id}">
    <div id="content_tab_details_{$id}">

        <div class="control-group ">
            <label for="elm_file_name_{$id}" class="control-label cm-required">{__("name")}</label>
            <div class="controls">
                <input type="text" name="product_file[file_name]" id="elm_file_name_{$id}" value="{$product_file.file_name}" class="span9" />
            </div>
        </div>

        <div class="control-group ">
            <label class="control-label" for="elm_file_position_{$id}">{__("position")}</label>
            <div class="controls">
                <input type="text" name="product_file[position]" id="elm_file_position_{$id}" value="{$product_file.position}" size="3" class="input-medium" />
            </div>
        </div>

        <div class="control-group ">
            <label class="control-label" for="elm_file_filder_{$id}">{__("folder")}</label>
            <div class="controls">
                <select name="product_file[folder_id]" id="elm_file_folder_{$id}">
                    <option value="0" {if $product_file.folder_id == "0"}selected="selected"{/if}>--</option>
                    {foreach from=$product_file_folders item=folder}
                    <option value={$folder.folder_id}{if $product_file.folder_id == $folder.folder_id} selected="selected"{/if}>{$folder.folder_name}</option>
                    {/foreach}
                </select>
            </div>
        </div>

        <div class="control-group ">
            <label for="type_{"base_file[`$id`]"|md5}" class="control-label{if !$product_file} cm-required{/if}">{__("file")}</label>
            <div class="controls">
                {if $product_file.file_path}
                    <a href="{"products.get_file?file_id=`$id`"|fn_url}">{$product_file.file_path}</a> ({$product_file.file_size|formatfilesize nofilter})
                {/if}
                {include file="common/fileuploader.tpl" var_name="base_file[`$id`]"}
            </div>
        </div>

        <div class="control-group ">
            <label class="control-label" for="type_{"file_preview[`$id`]"|md5}">{__("preview")}</label>
            <div class="controls">
                {if $product_file.preview_path}
                    <a href="{"products.get_file?file_id=`$id`&file_type=preview"|fn_url}">{$product_file.preview_path}</a> ({$product_file.preview_size|formatfilesize nofilter})
                {elseif $product_file}
                    {__("none")}
                {/if}
                {include file="common/fileuploader.tpl" var_name="file_preview[`$id`]"}
            </div>
        </div>

        <div class="control-group ">
            <label class="control-label" for="elm_file_activation_{$id}">{__("activation_mode")}</label>
            <div class="controls">
                <select name="product_file[activation_type]" id="elm_file_activation_{$id}">
                    <option value="M" {if $product_file.activation_type == "M"}selected="selected"{/if}>{__("manually")}</option>
                    <option value="I" {if $product_file.activation_type == "I"}selected="selected"{/if}>{__("immediately")}</option>
                    <option value="P" {if $product_file.activation_type == "P"}selected="selected"{/if}>{__("after_full_payment")}</option>
                </select>
            </div>
        </div>

        <div class="control-group ">
            <label class="control-label" for="elm_file_max_downloads_{$id}">{__("max_downloads")}</label>
            <div class="controls">
                <input type="text" name="product_file[max_downloads]" id="elm_file_max_downloads_{$id}" value="{$product_file.max_downloads}" size="3" class="input-text-short" />
            </div>
        </div>

        <div class="control-group ">
            <label class="control-label" for="elm_file_license_{$id}">{__("license_agreement")}</label>
            <div class="controls">
                <textarea id="elm_file_license_{$id}" name="product_file[license]" cols="55" rows="8" class="cm-wysiwyg span9">{$product_file.license}</textarea>
            </div>
        </div>

        <div class="control-group ">
            <label class="control-label">{__("agreement_required")}</label>
            <div class="controls">
                <label for="elm_file_agreement_{$id}_y" class="radio"><input type="radio" name="product_file[agreement]" id="elm_file_agreement_{$id}_y" {if $product_file.agreement == "Y" || !$product_file}checked="checked"{/if} value="Y" />
                {__("yes")}</label>
                <label for="elm_file_agreement_{$id}_n" class="radio"><input type="radio" name="product_file[agreement]" id="elm_file_agreement_{$id}_n" {if $product_file.agreement == "N"}checked="checked"{/if} value="N"  />
                {__("no")}</label>
            </div>
        </div>

        <div class="control-group ">
            <label class="control-label" for="elm_file_readme_{$id}">{__("readme")}</label>
            <div class="controls">
                <textarea id="elm_file_readme_{$id}" name="product_file[readme]" cols="55" rows="8" class="cm-wysiwyg span9">{$product_file.readme}</textarea>
            </div>
        </div>
        {hook name="product_files:properties"}
        {/hook}
    </div>
</div>

<div class="modal-footer buttons-container">
{if $product_file|fn_allow_save_object:""}
    {include file="buttons/save_cancel.tpl" but_name="dispatch[products.update_file]" cancel_action="close" save=$id}
{/if}
</div>

</form>