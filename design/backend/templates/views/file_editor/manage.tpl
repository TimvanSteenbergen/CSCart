{script src="js/lib/ace/ace.js"}
{script src="js/tygh/file_editor.js"}

<script type="text/javascript">
    (function (_, $) {
        _.tr({
            text_restore_question : '{__("text_restore_question")|escape:"javascript"}',
            text_enter_filename : '{__("text_enter_filename"|escape:"javascript")}',
            text_are_you_sure_to_delete_file : '{__("text_are_you_sure_to_delete_file"|escape:"javascript")}'
        });

        {if $selected_path}
        _.file_editor.selected_path = '{$selected_path}';
        {/if}
        _.file_editor.rel_path = '{$rel_path}';
    }(Tygh, Tygh.$));
</script>

{capture name="mainbox"}

<div id="error_box" class="hidden">
    <div align="center" class="notification-e">
        <div id="error_status"></div>
    </div>
</div>

<div id="status_box" class="hidden">
    <div class="notification-n" align="center">
        <div id="status"></div>
    </div>
</div>

<!--Editor-->
<div class="te-content cm-te-content">
    <div id="template_text"></div>
    <div id="template_image" class="te-template-image"></div>
</div>

<div class="cm-te-messages">
    <div class="te-empty-folder empty-text">
        <h2>{__("open_file_or_create_new")}</h2>
        {include file="common/popupbox.tpl" id="add_new_file" text=__("new_file") content="" link_text=__("create_file") act="general" link_class="cm-dialog-auto-size btn-primary" icon="icon-plus icon-white"}

        {hook name="file_editor:directory_action"}{/hook}
    </div>
    <div class="te-unknown-file empty-text">
        <h2>{__("could_not_open_file")}</h2>
    </div>
</div>

<div class="hidden" id="content_upload_file" title="{__("upload_file")}">
    
    <div class="install-addon">
        <form name="upload_form" action="{""|fn_url}" method="post" enctype="multipart/form-data" class="form-horizontal">
            <input type="hidden" name="path" id="upload_path" />
            <div class="install-addon-wrapper">
                <img class="install-addon-banner" src="{$images_dir}/addon_box.png" width="151px" height="141px" />

                {include file="common/fileuploader.tpl" var_name="uploaded_data[0]"}
            </div>

            <div class="buttons-container">
                {include file="buttons/save_cancel.tpl" but_name="dispatch[file_editor.upload_file]" but_meta="cm-te-upload-file" cancel_action="close" but_text=__("upload")}

            </div>
        </form>
    </div>
</div>

<div class="hidden" id="content_add_new_folder" title="{__("new_folder")}">
    <form name="add_folder_form" class="form-horizontal form-edit">
    <div class="control-group">
        <label for="elm_new_folder" class="control-label cm-required">{__("name")}</label>
        <div class="controls">
            <input type="text" class="span4" name="new_folder" id="elm_new_folder" value="" />
        </div>
    </div>
    <div class="buttons-container">
        {include file="buttons/save_cancel.tpl" cancel_action="close" but_meta="cm-te-create-folder cm-dialog-closer"}
    </div>
    </form>
</div>

<div class="hidden" id="content_add_new_file" title="{__("new_file")}">
    <form name="add_file_form" class="form-horizontal form-edit">
    <div class="control-group">
        <label for="elm_new_file" class="control-label cm-required">{__("name")}:</label>
        <div class="controls">
            <input type="text" class="span4" name="new_file" id="elm_new_file" value="" />
        </div>
    </div>
    <div class="buttons-container">
        {include file="buttons/save_cancel.tpl" cancel_action="close" but_meta="cm-dialog-closer cm-te-create-file"}
    </div>
    </form>
</div>

{if !"IS_WINDOWS"|defined}
    <div class="hidden" title="{__("change_permissions")}" id="content_chmod">
        {include file="views/file_editor/components/chmod.tpl"}
    <!--content_{$id}--></div>
{/if}

{capture name="buttons"}
    {capture name="tools_list"}
        {$current_url = $config.current_url|escape:"url"}
        
        {hook name="file_editor:on_site_template_editing"}
        <li class="cm-te-onsite-editing">{btn type="list" text=__("on_site_template_editing") href="customization.update_mode?type=design&status=enable&return_url=`$current_url`"|fn_url target="_blank"}</li>
        <li class="divider"></li>
        {/hook}
        
        {if $active_section == "themes"}
        {hook name="file_editor:restore_from_repository"}
        <li class="cm-te-restore">{btn type="list" text=__("restore_from_repository") }</li>
        {/hook}
        {/if}
        {if !"IS_WINDOWS"|defined}
            <li>{include file="common/popupbox.tpl" id="chmod" link_text=__("change_permissions") act="link" link_text=__("change_permissions") link_class="cm-te-perms cm-dialog-auto-size"}</li>
        {/if}
        <li class="cm-te-getfile">{btn type="list" text=__("download")}</li>
        <li class="cm-te-compress">{btn type="list" text=__("make_archive")}</li>
        <li class="cm-te-decompress">{btn type="list" text=__("extract_archive")}</li>
        <li class="cm-te-rename">{btn type="list" text=__("rename") }</li>
        <li class="cm-te-delete">{btn type="list" text=__("delete") }</li>
    {/capture}
    {dropdown content=$smarty.capture.tools_list class="ce-te-actions"}

    {hook name="file_editor:save_file"}
    {include file="buttons/save_changes.tpl" but_meta="cm-te-save-file btn-primary disabled" but_role="submit"}
    {/hook}
    
{/capture}

{capture name="sidebar"}
    <div class="sidebar-row">
        <ul class="nav nav-pills">
            {foreach from=$sections item=section}
            <li {if $active_section == $section}class="active"{/if}><a href="{"file_editor.manage?active_section=$section&selected_path=/"|fn_url}" >{__($section)}</a></li>
            {/foreach}
        </ul>
    </div>
    <hr>
    {hook name="file_editor:tree"}
    <div class="sidebar-row">
        <!--file tree-->
        <div id="filelist" class="cm-te-file-tree nested-list nested-list-folders"></div>
        <!--#file tree-->
    </div>
    {/hook}
{/capture}

{capture name="adv_buttons"}
    {capture name="tools_list"}
        {hook name="file_editor:tools_list"}
        <li class="cm-te-create-file">{include file="common/popupbox.tpl" id="add_new_file" content="" link_text=__("create_file") act="edit" no_icon_link="true" link_class="cm-dialog-auto-size"}</li>
        <li class="cm-te-create-folder">{include file="common/popupbox.tpl" id="add_new_folder" content="" link_text=__("create_folder") act="edit" no_icon_link="true" link_class="cm-dialog-auto-size"}</li>
        <li>{include file="common/popupbox.tpl" id="upload_file" content="" link_text=__("upload_file") act="edit" no_icon_link="true" link_class="cm-dialog-auto-size"}</li>
        {/hook}
    {/capture}
    {include file="common/tools.tpl" prefix="main" tool_meta="cm-te-create" hide_actions=true tools_list=$smarty.capture.tools_list display="inline" title=__("create") icon="icon-plus"}
{/capture}

{capture name="mainbox_title"}
{__("file_editor")}<span class="muted f-small cm-te-path te-path"></span>
{/capture}

{/capture}
{include file="common/mainbox.tpl" content=$smarty.capture.mainbox title=$smarty.capture.mainbox_title buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons sidebar=$smarty.capture.sidebar sidebar_position="left"}
