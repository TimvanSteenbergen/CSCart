{capture name="mainbox"}
    {include file="views/upgrade_center/components/stage.tpl"}

    {if $check_results.non_writable || $check_results.changed}
        <div class="row-fluid">
            {if $check_results.non_writable}
                <div class="span6">
                    <table class="table table-middle table-condensed">
                        <thead>
                        <th><h5>{__("text_uc_non_writable_files")}</h5></th>
                        </thead>
                        {foreach from=$check_results.non_writable item="c"}
                            <tr>
                                <td title="{$c}">
                                    <span class="pull-left">{$c|truncate:60:" ... ":true:true}</span>
                                </td>
                            </tr>
                            {foreachelse}
                            <tr>
                                <td class="no-items">{__("no_data")}</td>
                            </tr>
                        {/foreach}
                    </table>
                </div>
            {/if}
            {if $check_results.changed}
                <div class="span6">
                    <table class="table table-middle table-condensed">
                        <thead>
                        <th><h5>{__("text_uc_changed_files")}</h5></th>
                        </thead>
                        {foreach from=$check_results.changed item="c"}
                            <tr>
                                <td title="{$c}">
                                    <span class="pull-left">{$c|truncate:60:" ... ":true:true}</span>
                                </td>
                            </tr>
                            {foreachelse}
                            <tr>
                                <td class="no-items">{__("no_data")}s</td>
                            </tr>
                        {/foreach}
                    </table>
                </div>
            {/if}
        </div>
    {/if}

    {capture name="ftp_needed"}
        <p>{__("text_uc_ftp_needed")}</p>
        <form action="{""|fn_url}" method="post" class="form-horizontal form-edit"  name="uc_ftp_access">
            <input type="hidden" name="redirect_url" value="{$config.current_url}" />
             <div class="control-group">
                <label class="control-label" for="ftp_host">{__("host")}:</label>
                <div class="controls">
                    <input type="text" name="settings_data[ftp_hostname]" id="ftp_host" size="10" value="{$uc_settings.ftp_hostname}" class="input-medium" />
                </div>
            </div>
             <div class="control-group">
                <label class="control-label" for="ftp_user">{__("username")}:</label>
                <div class="controls">
                    <input type="text" name="settings_data[ftp_username]" id="ftp_user" size="10" value="{$uc_settings.ftp_username}" class="input-medium" />
                </div>
            </div>
             <div class="control-group">
                <label class="control-label" for="ftp_password">{__("password")}:</label>
                <div class="controls">
                    <input type="password" name="settings_data[ftp_password]" id="ftp_password" size="10" value="{$uc_settings.ftp_password}" class="input-medium" />
                </div>
            </div>
             <div class="control-group">
                <label class="control-label" for="ftp_directory">{__("directory")}:</label>
                <div class="controls">
                    <input type="text" name="settings_data[ftp_directory]" id="ftp_directory" size="10" value="{$uc_settings.ftp_directory}" class="input-medium" />
                </div>
            </div>
             <div class="buttons-container buttons-bg">
                {include file="buttons/button.tpl" but_name="dispatch[upgrade_center.update_settings]" but_text=__("change") but_role="button_main"}
            </div>
         </form>
    {/capture}

    {if $check_results.no_db_rights}

        {assign var="priv_list" value=", "|implode:$check_results.no_db_rights}
        <p>{__("text_uc_db_right_needed", ["[priviliges]" => $priv_list, "[db_user]" => $config.db_user])}</p>

    {else}

        {if !$check_results.changed && !$check_results.non_writable && !$check_results.no_db_rights}
            <p>{__("text_uc_check_ok", ["[product]" => $smarty.const.PRODUCT_NAME])}<p>
        {/if}

        <form action="{""|fn_url}" method="get" name="uc_check_form">

            {capture name="buttons"}
                {capture name="tools_list"}
                        <li>{include file="common/popupbox.tpl" id="auto_set_permissions_via_ftp" text=__("auto_set_permissions_via_ftp") link_text=__("auto_set_permissions_via_ftp") content=$smarty.capture.ftp_needed act="link"}</li>
                {/capture}
                {dropdown content=$smarty.capture.tools_list}
                {if !$check_results.non_writable}
                    {include file="buttons/button.tpl" but_role="submit-link" but_name="dispatch[upgrade_center.run_backup]" but_target_form="uc_check_form" but_text=__("continue")}
                {else}
                    {include file="buttons/button.tpl" but_role="submit-link" but_name="dispatch[upgrade_center.check]" but_target_form="uc_check_form" but_text=__("check_again")}
                {/if}
            {/capture}

        </form>
    {/if}

{/capture}
{include file="common/mainbox.tpl" title=__("check") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons}
