{capture name="mainbox"}
    {assign var="c_url" value=""|fn_url}
    {__("browser_upgrade_notice", ["[url]" => $c_url])}
{/capture}
{include file="common/mainbox.tpl" title=__("browser_upgrade_notice_title") content=$smarty.capture.mainbox}