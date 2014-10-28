<form action="{""|fn_url}" method="post" name="usergroup_requests_form" class="form-table">
{capture name="mainbox"}
{assign var="c_icon" value="<i class=\"exicon-`$search.sort_order_rev`\"></i>"}
{assign var="c_dummy" value="<i class=\"exicon-dummy\"></i>"}

{include file="common/pagination.tpl" save_current_page=true save_current_url=true}
{assign var="c_url" value=$config.current_url|fn_query_remove:"sort_by":"sort_order"}

{if $usergroup_requests}
<table width="100%" class="table table-middle">
    <thead>
    <tr>
        <th width="1%" class="left">
            {include file="common/check_items.tpl"}</th>
        <th width="60%"><a class="cm-ajax" href="{"`$c_url`&sort_by=customer&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("user")}{if $search.sort_by == "customer"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
        <th width="20%"><a class="cm-ajax" href="{"`$c_url`&sort_by=usergroup&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("usergroup")}{if $search.sort_by == "usergroup"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
        <th width="19%" class="right">{__("status")}</th>
    </tr>
    </thead>
    {foreach from=$usergroup_requests item=ug_request}
    <tr>
        <td class="center">
            <input type="checkbox" name="link_ids[]" value="{$ug_request.link_id}" class="cm-item" /></td>
        <td><a href="{"profiles.update?user_id=`$ug_request.user_id`"|fn_url}">{$ug_request.lastname} {$ug_request.firstname}</a></td>
        <td><a href="{"usergroups.manage#group`$ug_request.usergroup_id`"|fn_url}">{$ug_request.usergroup}</a></td>
        <td class="right">
            {include file="common/select_popup.tpl" id="`$ug_request.usergroup_id`_`$ug_request.user_id`" status=$ug_request.status hidden="" items_status="usergroups"|fn_get_predefined_statuses extra="&user_id=`$ug_request.user_id`" update_controller="usergroups" notify=true}
        </td>
    </tr>
    {/foreach}
</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}

{include file="common/pagination.tpl"}

{if $usergroup_requests}
{capture name="buttons"}
    {capture name="list_list"}
        <li>{btn type="list" text=__("approve_selected") dispatch="dispatch[usergroups.bulk_update_status.approve]" class="cm-confirm" form="usergroup_requests_form"}</li>
        <li>
            <a>
                <label for="notify_user">
                <input type="checkbox" name="notify_user" id="notify_user" value="Y"/>
                {__("notify_customer")}</label>
            </a>
        </li>
        <li class="divider"></li>
        <li>{btn type="list" text=__("decline_selected") dispatch="dispatch[usergroups.bulk_update_status.decline]" form="usergroup_requests_form"}</li>
    {/capture}
    {dropdown content=$smarty.capture.list_list}
{/capture}

{/if}

{/capture}
{include file="common/mainbox.tpl" buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons title=__("user_group_requests") content=$smarty.capture.mainbox}
</form>
