{capture name="mainbox"}

{capture name="open_store_link"}
<a class="cm-ajax cm-confirm text-button" data-ca-target-id="store_mode" href="{"tools.store_mode?state=opened"|fn_url}">{__("open_store")}</a>
{/capture}

{assign var="package" value=$smarty.request.package|escape:url}

{if $uc_upgrade_errors}
    {assign var="restore_link" value="upgrade_center.revert?package=`$package`"|fn_url}
    {__("text_uc_upgrade_completed_with_errors", ["[href]" => $config.resources.helpdesk_url, "[restore_link]" => $restore_link])}
{else}
    {__("text_uc_upgrade_completed")}
{/if}
{__("text_uc_upgrade_completed_check_and_open")}

<a href="{"upgrade_center.manage"|fn_url}">{__("upgrade_center")}</a>

{if $smarty.request.package}
    {capture name="buttons"}
        <a href="{"upgrade_center.revert?package=`$package`"|fn_url}" class="btn">{__("revert")}</a>
    {/capture}
{/if}

{/capture}
{include file="common/mainbox.tpl" title=__("summary") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons}
