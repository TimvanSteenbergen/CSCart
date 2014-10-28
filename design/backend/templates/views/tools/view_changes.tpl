{capture name="mainbox"}
    {literal}
    <style type="text/css">
        .snapshot-added {
            background-color: #dff0d8 !important;
        }
        .snapshot-changed {
            background-color: #fcf8e3 !important;
        }
        .snapshot-deleted {
            background-color: #f2dede !important;
        }
    </style>
    {/literal}

    {include file="common/subheader.tpl" title=__("change_addons_initialization")}
    <p>
        <a href="{"tools.init_addons?init_addons=none"|fn_url}">{__("tools_addons_no_addons")}</a> |
        <a href="{"tools.init_addons?init_addons=core"|fn_url}">{__("tools_addons_no_third_party_addons")}</a> |
        <a href="{"tools.init_addons"|fn_url}">{__("tools_addons_restore_defaults")}</a>

        <br><br>
        {__("tools_addons_current_state")}: {if $settings.init_addons == "none"}{__("tools_addons_no_addons")}{elseif $settings.init_addons == "core"}{__("tools_addons_no_third_party_addons")}{else}{__("default")}{/if}
        <br>
        {__("tools_addons_addons_count")}: {$addons|count}
    </p>

    {include file="common/subheader.tpl" title=__("files_changes")}
    <div class="items-container multi-level">
        {if $changes_tree}
            {include file="views/tools/components/changes_tree.tpl" parent_id=0 show_all=true expand=true}
        {else}
            <p class="no-items">{__("no_items")}</p>
        {/if}
    </div>

    {if $db_diff}
        {include file="common/subheader.tpl" title=__("database_structure_changes")}
        <pre style="height: 400px; overflow-y: scroll" class="diff-container">{$db_diff nofilter}</pre>
    {/if}

    {include file="common/subheader.tpl" title=__("database_data_changes")}

    {** include fileuploader **}
    {*include file="common/file_browser.tpl"*}
    {** /include fileuploader **}

    <form action="{""|fn_url}" method="post" name="data_compare_form" enctype="multipart/form-data" class="form-horizontal form-edit">
        <div class="control-group">
            <label class="control-label" for="name_db" >{__("db_name")}</label>
            <div class="controls">
                <input type="text" name="compare_data[db_name]" id="name_db" value="" class="span4" />
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="type_base_file">{__("file")}</label>
            <div class="controls">
                {if $compare_data.file_path}
                    <b>{$compare_data.file_path}</b> ({$compare_data.file_size|formatfilesize nofilter})
                {/if}
            {include file="common/fileuploader.tpl" var_name="base_file"}
            </div>
        </div>
        {capture name="buttons"}
            {if !$dist_filename}
                <a class="btn" href="{"tools.create_snapshot"|fn_url}">Make a fresh snapshot</a>
            {/if}
        {include file="buttons/button.tpl" but_text=__("compare") but_role="submit-link" but_target_form="data_compare_form" but_name="dispatch[tools.view_changes]"}
        {/capture}
    </form>

    <pre style="height: 300px; overflow-y: scroll" class="diff-container">{$db_d_diff nofilter}</pre>

        {if $changes_tree || $db_diff || $db_d_diff}
        <div style="margin: 30px 20px 20px 7px;">
            <table cellpadding="0" cellspacing="0" border="0" width="30%">
                <tr>
                    <td><span class="label label-success">Added</span></td>
                    <td><span class="label label-warning">Changed</span></td>
                    <td><span class="label label-important">Deleted</span></td>
                </tr>
            </table>
        </div>
        {/if}

        {capture name="sidebar"}
        <div class="sidebar-row">
            <h6>{__("snapshot_date")}</h6>
            <p>
                {if $dist_filename}
                    <span class="muted">Snapshot of the clear installation was not found. Please restore "{$dist_filename}".</span>
                {else}
                    {if $creation_time}<span class="muted">{$creation_time|date_format:"`$settings.Appearance.date_format` `$settings.Appearance.time_format`"}.</span>{/if}
                {/if}
            </p>
            <hr />
        </div>
        {/capture}

    {$changes_tree_keys=$changes_tree|array_keys}
    <script type="text/javascript">
        Tygh.$(document).ready(function(){ldelim}
          //  alert('#on_changes_{$changes_tree_keys.0}');
            Tygh.$('#on_changes_{$changes_tree_keys.0}').click();
        {rdelim}
        );
    </script>
{/capture}

{include file="common/mainbox.tpl" title=__("tools") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons sidebar=$smarty.capture.sidebar}