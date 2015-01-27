{capture name="mainbox"}

<div class="items-container" id="mailing_lists">
{if $mailing_lists}
<table width="100%" class="table table-middle">
    <thead>
        <tr>
            <th>{__("name")}</th>
            <th>{__("subscribers_num")}</th>
            <th width="5%">&nbsp;</th>
            <th width="15%" class="right">{__("status")}</th>
        </tr>
    </thead>
<tbody>
{foreach from=$mailing_lists item="mailing_list"}

    {capture name="tool_items"}
        <li>{btn type="list" text=__("add_subscribers") href="subscribers.manage?list_id=`$mailing_list.list_id`"}</li>
        <li class="divider"></li>
    {/capture}

    {include file="common/object_group.tpl" no_table=true id=$mailing_list.list_id text=$mailing_list.object status=$mailing_list.status hidden=true href="mailing_lists.update?list_id=`$mailing_list.list_id`" details="{__("subscribers_num")}: `$mailing_list.subscribers_num`" object_id_name="list_id" table="mailing_lists" href_delete="mailing_lists.delete?list_id=`$mailing_list.list_id`" delete_target_id="mailing_lists" header_text="{__("editing_mailing_list")}: `$mailing_list.object`" tool_items=$smarty.capture.tool_items}

{/foreach}
</tbody>
</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}
<!--mailing_lists--></div>

    {capture name="adv_buttons"}
        {capture name="add_new_picker"}
            {include file="addons/news_and_emails/views/mailing_lists/update.tpl" mailing_list=[]}
        {/capture}
        {include file="common/popupbox.tpl" id="add_new_mailing_lists" text=__("new_mailing_lists") content=$smarty.capture.add_new_picker title=__("add_mailing_lists") act="general" icon="icon-plus"}
    {/capture}
{/capture}

{include file="common/mainbox.tpl" title=__("mailing_lists") content=$smarty.capture.mainbox adv_buttons=$smarty.capture.adv_buttons select_languages=true}