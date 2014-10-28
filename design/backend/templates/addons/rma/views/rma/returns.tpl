{capture name="mainbox"}

<form action="{""|fn_url}" method="post" target="" enctype="multipart/form-data" name="rma_list_form">

{include file="common/pagination.tpl"}

{assign var="c_url" value=$config.current_url|fn_query_remove:"sort_by":"sort_order"}
{assign var="c_icon" value="<i class=\"exicon-`$search.sort_order_rev`\"></i>"}
{assign var="c_dummy" value="<i class=\"exicon-dummy\"></i>"}

{if $return_requests}
<table width="100%" class="table table-middle">
<thead>
<tr>
    <th class="left">
        {include file="common/check_items.tpl"}</th>
    <th width="5%" class="nowrap"><a class="cm-ajax" href="{"`$c_url`&sort_by=return_id&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("id")}{if $search.sort_by == "return_id"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
    <th width="15%"><a class="cm-ajax" href="{"`$c_url`&sort_by=status&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("status")}{if $search.sort_by == "status"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
    <th width="25%"><a class="cm-ajax" href="{"`$c_url`&sort_by=customer&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("customer")}{if $search.sort_by == "customer"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
    <th width="20%"><a class="cm-ajax" href="{"`$c_url`&sort_by=timestamp&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("date")}{if $search.sort_by == "timestamp"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
    <th width="10%"><a class="cm-ajax" href="{"`$c_url`&sort_by=action&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("action")}{if $search.sort_by == "action"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
    <th width="10%" class="center"><a class="cm-ajax" href="{"`$c_url`&sort_by=order_id&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("order")}&nbsp;{__("id")}{if $search.sort_by == "order_id"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
    <th width="10%" class="center"><a class="cm-ajax" href="{"`$c_url`&sort_by=amount&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("quantity")}{if $search.sort_by == "amount"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
    <th>&nbsp;</th>
</tr>
</thead>
{foreach from=$return_requests item="request"}
<tr>
    <td class="left">
        <input type="checkbox" name="return_ids[]" value="{$request.return_id}" class="cm-item" /></td>
    <td><a href="{"rma.details?return_id=`$request.return_id`"|fn_url}" class="underlined">#{$request.return_id}</a></td>
    <td>
        {include file="common/status.tpl" status=$request.status display="view" name="return_statuses[`$request.return_id`]" status_type=$smarty.const.STATUSES_RETURN}
    </td>
    <td>{$request.firstname} {$request.lastname}</td>
    <td><a href="{"rma.details?return_id=`$request.return_id`"|fn_url}" class="underlined">{$request.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}</a></td>
    <td>{$request.action}</td>
    <td class="center"><a href="{"orders.details?order_id=`$request.order_id`"|fn_url}" class="underlined">{$request.order_id}</a></td>
    <td class="center">{$request.total_amount}</td>
    <td class="nowrap">
        {capture name="tools_list"}
            <li>{btn type="list" text=__("edit") href="rma.details?return_id=`$request.return_id`"}</li>
            <li>{btn type="list" class="cm-confirm" text=__("delete") href="rma.delete?return_id=`$request.return_id`"}</li>
        {/capture}
        <div class="hidden-tools">
            {dropdown content=$smarty.capture.tools_list}
        </div>
    </td>
</tr>
{/foreach}
</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}

{include file="common/pagination.tpl"}

{capture name="buttons"}
    {capture name="tools_list"}
        {if $return_requests}
            <li>{btn type="delete_selected" dispatch="dispatch[rma.m_delete_returns]" form="rma_list_form"}</li>
            <li>{btn type="list" text=__("bulk_print_packing_slip") dispatch="dispatch[rma.bulk_slip_print]" form="rma_list_form" class="cm-process-items cm-new-window"}</li>
        {/if}
    {/capture}
    {dropdown content=$smarty.capture.tools_list}
{/capture}
</form>

{capture name="sidebar"}
    {include file="common/saved_search.tpl" dispatch="rma.returns" view_type="rma"}
    {include file="addons/rma/views/rma/components/rma_search_form.tpl" dispatch="rma.returns"}
{/capture}

{/capture}
{include file="common/mainbox.tpl" title=__("return_requests") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons sidebar=$smarty.capture.sidebar}
