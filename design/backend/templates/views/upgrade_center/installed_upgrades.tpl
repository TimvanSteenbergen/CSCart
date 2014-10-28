{capture name="mainbox"}
{assign var="return_current_url" value=$config.current_url|escape:url}
{foreach from=$packages item="package" name="fep" key="p"}
{assign var="_p" value=$p|escape:url}
    <h4>{$package.details.name}</h4>

    {capture name="buttons"}
        {capture name="tools_list"}
            {if $smarty.foreach.fep.last}
                <li>{btn type="list" class="cm-confirm" text=__("revert") href="upgrade_center.revert?package=`$_p`&redirect_url=`$return_current_url`"}</li>
            {/if}
            <li>{btn type="list" class="cm-confirm" text=__("remove_backup_files") href="upgrade_center.remove?package=`$_p`"}</li>
        {/capture}
        {dropdown content=$smarty.capture.tools_list}
    {/capture}

    {capture name="sidebar"}
        <div class="sidebar-row">
            <h6>{__("upgrade")}</h6>
            <ul class="unstyled">
                <li>{__("version")}: {$package.details.to_version}</li>
                {if $package.details.timestamp}
                    <li>{__("release_date")}: {$package.details.timestamp|date_format:"`$settings.Appearance.date_format` `$settings.Appearance.time_format`"}</li>
                {/if}
                {if $package.details.size}
                    <li>{__("filesize")}: {$package.details.size|formatfilesize nofilter}</li>
                {/if}
            </ul>
        </div>
    {/capture}

    <p>{__("text_remove_backup_files")}</p>

<div class="row-fluid">
    <div class="span6">
            <h5>{__("package_contents")}</h5>
            <table class="table table-middle table-condensed">
                <thead>
                    <tr>
                        <td><h5>{__("file")}</h5></td>
                    </tr>
                </thead>
                {foreach from=$package.details.contents item="c"}
                    <tr>
                        <td title="{$c}">{$c|truncate:85:" ... ":true:true}</td>
                    </tr>
                {/foreach}
            </table>
    </div>
    <div class="span6">
        <h5>{__("text_uc_conflicts")}</h5>
        <table class="table table-middle table-condensed">
            <thead>
            <tr>
                <td><h5>{__("file")}</h5></td>
            </tr>
            </thead>
        {foreach from=$package.files key="c" item="s"}
            <tr>
                <td title="{$c}">
                    <span class="pull-left">{if $s == true}<span class="text-success">{__("resolved")}</span>{/if} {$c|truncate:60:" ... ":true:true}</span>
                    {assign var="_c" value=$c|escape:url}
                    {capture name="tools_list"}
                        <li>
                            <a href="{"upgrade_center.diff?file=`$_c`&package=`$_p`"|fn_url}">{__("changes")}</a>
                        </li>
                        {if $s == true}
                            <li>
                                <a href="{"upgrade_center.conflicts.unmark?file=`$_c`&package=`$_p`"|fn_url}">{__("unmark")}</a>
                            </li>
                        {else}
                            <li><a href="{"upgrade_center.conflicts.mark?file=$_c&package=`$_p`"|fn_url}">{__("mark")}</a></li>
                        {/if}
                    {/capture}
                    <div class="hidden-tools pull-right">
                        {dropdown content=$smarty.capture.tools_list}
                    </div>
                </td>
            </tr>
        {foreachelse}
            <tr>
                <td class="no-items">
                    {__("text_no_conflicts")}
                </td>
            </tr>
        {/foreach}
          </table>
    </div>
</div>

{capture name="sidebar"}
    <div class="sidebar-row">
        <h6>{__("description")}</h6>
        <p>{$package.details.description nofilter}</p>
    </div>
{/capture}

{foreachelse}
    <p class="no-items">{__("no_data")}</p>
{/foreach}

{/capture}
{include file="common/mainbox.tpl" title=__("installed_upgrades") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons sidebar=$smarty.capture.sidebar}