{capture name="mainbox"}
{include file="views/upgrade_center/components/stage.tpl"}

<div class="alert cm-notification-content alert-error">
    {__("text_uc_emergency_restore", ["[href]" => "`$config.http_location`/var/upgrade/`$smarty.session.uc_package`/restore.php?uak=`$restore_key`"])}
</div>

<table width="100%">
<tr>
    {if $backup_details.files}
        <td valign="top" width="50%">
            <table class="table table-middle table-condensed">
                <thead>
                    <tr>
                        <td><h5>{__("text_uc_backup_files")}</h5></td>
                    </tr>
                </thead>
            {foreach from=$backup_details.files item="c"}
                <tr>
                    <td title="{$c}">
                        <span class="pull-left">{$c|truncate:60:" ... ":true:true}</span>
                        <small class="text-success pull-right">{__("uc_ok")}</small>
                    </td>
                </tr>
            {/foreach}
            </table>
        </td>
    {/if}

    {if $backup_details.tables}
        <td valign="top" width="50%">
            <table class="table table-middle table-condensed">
                <thead>
                <tr>
                    <td><h5>{__("text_uc_backup_database")}</h5></td>
                </tr>
                </thead>
            {foreach from=$backup_details.tables item="c"}
                <tr>
                    <td title="{$c}">
                        <span class="pull-left">{$c|truncate:60:" ... ":true:true}</span>
                        <small class="text-success pull-right">{__("uc_ok")}</small>
                    </td>
                </tr>
            {/foreach}
            </table>
        </td>
    {/if}
</tr>
</table>

<form action="{""|fn_url}" method="get" name="uc_check_form">
    {capture name="buttons"}
        {include file="buttons/button.tpl" but_text=__("continue") but_name="dispatch[upgrade_center.upgrade]" but_target_form="uc_check_form" but_role="submit-link"}
    {/capture}
</form>

{/capture}
{include file="common/mainbox.tpl" title=__("backup") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons}