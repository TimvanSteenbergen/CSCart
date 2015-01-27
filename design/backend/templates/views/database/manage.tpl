{capture name="mainbox"}

{capture name="tabsbox"}

{** CREATE BACKUP **}
<div id="content_backup"> 
<form action="{""|fn_url}" method="post" name="backup_form" class="form-horizontal form-edit cm-ajax">
<input type="hidden" name="selected_section" value="backup" />
<input type="hidden" name="result_ids" value="database_management,tools_restore,tools_restore_delete" />

{notes}
    {__("multiple_selectbox_notice")}
{/notes}

<div class="control-group">
    <label for="dbdump_tables" class="control-label">{__("select_tables")}:</label>
    <div class="controls">
        <select name="dbdump_tables[]" id="dbdump_tables" multiple="multiple" size="10">
            {foreach from=$all_tables item=tbl}
                <option value="{$tbl}"{if $config.table_prefix == '' || $tbl|strpos:$config.table_prefix === 0} selected="selected"{/if}>{$tbl}</option>
            {/foreach}
        </select>
        <p><a onclick="Tygh.$('#dbdump_tables').selectOptions(true); return false;" class="underlined">{__("select_all")}</a> / <a onclick="Tygh.$('#dbdump_tables').selectOptions(false); return false;" class="underlined">{__("unselect_all")}</a></p>
    </div>
</div>

<div class="control-group">
    <label for="dbdump_filename" class="control-label">
        {__("backup_options")}:
    </label>
    <div class="controls">
        <label for="dbdump_schema" class="checkbox">
            <input type="checkbox" name="dbdump_schema" id="dbdump_schema" value="Y" checked="checked" >
            {__("backup_schema")}
        </label>
        <label for="dbdump_data" class="checkbox">
            <input type="checkbox" name="dbdump_data" id="dbdump_data" value="Y" checked="checked">
            {__("backup_data")}
        </label>
        <input type="hidden" name="dbdump_compress" value="N" />
        <label for="dbdump_compress" class="checkbox">
            <input type="checkbox" name="dbdump_compress" id="dbdump_compress" value="Y" checked="checked">
            {__("compress_dump")}
        </label>
    </div>
</div>

<div class="control-group">
    <label for="dbdump_filename" class="control-label">{__("backup_filename")}:</label>
    <div class="controls">
        <input type="text" name="dbdump_filename" id="dbdump_filename" size="30" value="dump_{$smarty.now|date_format:"%m%d%Y"}.sql" class="input-text">
        <p class="muted">{__("text_backup_filename")}</p>
    </div>
</div>
</form>
</div>
{** /CREATE BACKUP **}

{** RESTORE DATABASE **}
<div id="content_restore">
    <form action="{""|fn_url}" method="post" name="upload_data" class="cm-ajax" enctype="multipart/form-data">
    <input type="hidden" name="selected_section" value="restore" />
    <input type="hidden" name="result_ids" value="content_restore,tools_restore,tools_restore_delete" />

    <fieldset>
    
    <p>{__("text_backup_management_notice")}</p>
    
    {include file="common/fileuploader.tpl" var_name="sql_dump[0]" allowed_ext="tgz,sql"}
    
    <div class="buttons-container">
        {include file="buttons/button.tpl" but_text=__("upload") but_name="dispatch[database.upload]"}
    </div>
    </fieldset>
    </form>
    <form action="{""|fn_url}" method="post" name="restore_form" class="cm-ajax" enctype="multipart/form-data">
    <input type="hidden" name="fake" value="1" />
    <input type="hidden" name="selected_section" value="restore" />
    <input type="hidden" name="result_ids" value="database_management,tools_restore,tools_restore_delete" />
    <fieldset>
    {if $backup_files}
    <table class="table">
    <thead>
        <tr>
            <th>
                {include file="common/check_items.tpl"}</th>
            <th class="center">{__("type")}</th>
            <th>{__("filename")}</th>
            <th>{__("date")}</th>
            <th>{__("filesize")}</th>
            <th>&nbsp;</th>
        </tr>
    </thead>
    {foreach from=$backup_files item=file key=name}
    <tr>
        <td>
            <input type="checkbox" name="backup_files[]" value="{$name}" class="checkbox cm-item" /></td>
        <td class="center">[{$file.type}]</td>
        <td>
            <a href="{"database.getfile?file=`$name`"|fn_url}"><span>{$name}</span></a></td>
        <td>{$file.create}</td>
        <td>
            {$file.size|number_format}&nbsp;{__("bytes")}</td>
        <td class="nowrap">
            <div class="hidden-tools">
                {capture name="tools_list"}
                    <li>{btn type="list" text=__("download") href="database.getfile?file=`$name`"}</li>
                    <li>{btn type="list" class="cm-confirm" text=$link_text|default:__("delete") href="database.delete?backup_file=`$name`"}</li>
                {/capture}
                {dropdown content=$smarty.capture.tools_list}
            </div>
        </td>
    </tr>
    {/foreach}
    </table>
    {else}
        <p class="no-items">{__("no_data")}</p>
    {/if}

    </fieldset>
    </form>

<!--content_restore--></div>
{** /RESTORE DATABASE **}

{** MAINTENANCE **}
<div id="content_maintenance">

    <p>{__("current_database_size")}: <span>{$database_size|number_format}</span> {__("bytes")}</p>

<form action="{""|fn_url}" method="post" class="cm-ajax cm-comet" name="mainainance_form">
    <input type="hidden" name="selected_section" value="maintenance" />
    <input type="hidden" name="result_ids" value="database_management" />
</form>

</div>
{** /MAINTENANCE **}

{/capture}
{include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox}
{/capture}

{capture name="adv_buttons"}

{/capture}

{capture name="buttons"}
    {capture name="tools_list"}
        <li>{btn type="list" text=__("logs") href="logs.manage"}</li>
        <li class="cm-tab-tools" id="tools_restore_delete">
            {if $backup_files}
                {btn type="delete_selected" dispatch="dispatch[database.m_delete]" form="restore_form"}
            {/if}
        <!--tools_restore_delete--></li>
    {/capture}
    {dropdown content=$smarty.capture.tools_list}

    <div class="cm-tab-tools pull-right shift-left" id="tools_backup">
        {include file="buttons/button.tpl" but_text=__("backup") but_name="dispatch[database.backup]" but_target_form="backup_form" but_meta="cm-comet" but_role="submit-link"}
    </div>

    <div class="cm-tab-tools pull-right shift-left" id="tools_restore">
        {if $backup_files}
            {include file="buttons/button.tpl" but_text=__("restore") but_name="dispatch[database.restore]" but_meta="cm-process-items cm-confirm cm-comet" but_target_form="restore_form" but_role="submit-link"}
        {/if}
    <!--tools_restore--></div>

    <div class="cm-tab-tools pull-right shift-left" id="tools_maintenance">
        {include file="buttons/button.tpl" but_text=__("optimize_database") but_name="dispatch[database.optimize]" but_target_form="mainainance_form" but_role="submit-link"}
    </div>
{/capture}

{include file="common/mainbox.tpl" title=__("database") content=$smarty.capture.mainbox box_id="database_management" adv_buttons=$smarty.capture.adv_buttons buttons=$smarty.capture.buttons}
