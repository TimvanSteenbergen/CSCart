{notes title=__("notice")}
    <p>{__("export_cron_hint")}:<br />
        <span>php /path/to/cart/{""|fn_url:"A":"rel"} --dispatch=exim.cron_export --cron_password={$addons.data_feeds.cron_password}</span>
    </p>
{/notes}

{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="manage_datafeeds_form">

{if $datafeeds}
<table class="table sortable table-middle">
<thead>
    <tr>
        <th width="5%" class="left">{include file="common/check_items.tpl"}</th>
        <th width="30%" nowrap="nowrap">{__("name")}</th>
        <th width="30%" nowrap="nowrap">{__("export_by_cron")}</th>
        <th width="20%" nowrap="nowrap">&nbsp;</th>
        <th width="15%" nowrap="nowrap" class="right">{__("status")}</th>
    </tr>
</thead>
{foreach from=$datafeeds item=datafeed}
<tr class="cm-row-status-{$datafeed.status|lower}">
    <td class="left">
        <input type="checkbox" name="datafeed_ids[]" value="{$datafeed.datafeed_id}" class="checkbox cm-item" />
    </td>
    
    <td>
        <a href="{"data_feeds.update?datafeed_id=`$datafeed.datafeed_id`"|fn_url}">{$datafeed.datafeed_name}</a>
    </td>

    <td class="nowrap">
        <select name="datafeed_data[{$datafeed.datafeed_id}][export_location]">
            <option value=""> -- </option>
            <option value="S" {if $datafeed.export_location == "S"}selected="selected"{/if}>{__("server")}</option>
            <option value="F" {if $datafeed.export_location == "F"}selected="selected"{/if}>{__("ftp")}</option>
        </select>
    </td>
    
    <td class="nowrap">
        {capture name="tools_list"}
            <li>{btn type="list" class="cm-confirm cm-ajax cm-comet" text=__("local_export") href="exim.export_datafeed?datafeed_ids[]=`$datafeed.datafeed_id`&location=L"}</li>
            <li>{btn type="list" class="cm-confirm cm-ajax cm-comet" text=__("export_to_server") href="exim.export_datafeed?datafeed_ids[]=`$datafeed.datafeed_id`&location=S"}</li>
            <li>{btn type="list" class="cm-confirm cm-ajax cm-comet" text=__("upload_to_ftp") href="exim.export_datafeed?datafeed_ids[]=`$datafeed.datafeed_id`&location=F"}</li>
            <li class="divider"></li>
            <li>{btn type="list" text=__("edit") href="data_feeds.update?datafeed_id=`$datafeed.datafeed_id`"}</li>
        {/capture}
        <div class="hidden-tools">
            {dropdown content=$smarty.capture.tools_list}
        </div>
    </td>
    
    <td class="right">
        {include file="common/select_popup.tpl" id=$datafeed.datafeed_id status=$datafeed.status hidden=false object_id_name="datafeed_id" table="data_feeds"}
    </td>
    
</tr>
{/foreach}
</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}

{capture name="buttons"}
    {if $datafeeds}
        {capture name="tools_list"}
            <li>{btn type="delete_selected" dispatch="dispatch[data_feeds.m_delete]" form="manage_datafeeds_form"}</li>
            <li>{include file="common/popupbox.tpl" id="select_fields_to_edit" text=__("select_fields_to_edit") content=$smarty.capture.select_fields_to_edit}</li>
        {/capture}
        {dropdown content=$smarty.capture.tools_list}

        {include file="buttons/save.tpl" but_name="dispatch[data_feeds.m_update]" but_role="submit-link" but_target_form="manage_datafeeds_form"}
    {/if}
{/capture}

{capture name="adv_buttons"}
    {include file="common/tools.tpl" tool_href="data_feeds.add" prefix="bottom" title="{__("add_datafeed")}" hide_tools=true icon="icon-plus"}
{/capture}
        
</form>

{/capture}
{include file="common/mainbox.tpl" title=__("data_feeds") content=$smarty.capture.mainbox tools=$smarty.capture.tools select_languages=true buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons}
