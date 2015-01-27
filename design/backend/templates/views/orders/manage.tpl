{capture name="mainbox"}

{if $runtime.mode == "new"}
    <p>{__("text_admin_new_orders")}</p>
{/if}

{capture name="sidebar"}
    {include file="common/saved_search.tpl" dispatch="orders.manage" view_type="orders"}
    {include file="views/orders/components/orders_search_form.tpl" dispatch="orders.manage"}
{/capture}

<form action="{""|fn_url}" method="post" target="_self" name="orders_list_form">

{include file="common/pagination.tpl" save_current_page=true save_current_url=true div_id=$smarty.request.content_id}

{assign var="c_url" value=$config.current_url|fn_query_remove:"sort_by":"sort_order"}
{assign var="c_icon" value="<i class=\"exicon-`$search.sort_order_rev`\"></i>"}
{assign var="c_dummy" value="<i class=\"exicon-dummy\"></i>"}

{assign var="rev" value=$smarty.request.content_id|default:"pagination_contents"}

{if $incompleted_view}
    {assign var="page_title" value=__("incompleted_orders")}
    {assign var="get_additional_statuses" value=true}
{else}
    {assign var="page_title" value=__("orders")}
    {assign var="get_additional_statuses" value=false}
{/if}
{assign var="order_status_descr" value=$smarty.const.STATUSES_ORDER|fn_get_simple_statuses:$get_additional_statuses:true}
{assign var="extra_status" value=$config.current_url|escape:"url"}
{$statuses = []}
{assign var="order_statuses" value=$smarty.const.STATUSES_ORDER|fn_get_statuses:$statuses:$get_additional_statuses:true}

{if $orders}
<table width="100%" class="table table-middle">
<thead>
<tr>
    <th  class="left">
    {include file="common/check_items.tpl" check_statuses=$order_status_descr}
    </th>
    <th width="17%"><a class="cm-ajax" href="{"`$c_url`&sort_by=order_id&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("id")}{if $search.sort_by == "order_id"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
    <th width="17%"><a class="cm-ajax" href="{"`$c_url`&sort_by=status&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("status")}{if $search.sort_by == "status"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
    <th width="15%"><a class="cm-ajax" href="{"`$c_url`&sort_by=date&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("date")}{if $search.sort_by == "date"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
    <th width="20%"><a class="cm-ajax" href="{"`$c_url`&sort_by=customer&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("customer")}{if $search.sort_by == "customer"}{$c_icon nofilter}{/if}</a></th>
    <th width="15%"><a class="cm-ajax" href="{"`$c_url`&sort_by=phone&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("phone")}{if $search.sort_by == "phone"}{$c_icon nofilter}{/if}</a></th>

    {hook name="orders:manage_header"}{/hook}

    <th>&nbsp;</th>
    <th width="14%" class="right"><a class="cm-ajax{if $search.sort_by == "total"} sort-link-{$search.sort_order_rev}{/if}" href="{"`$c_url`&sort_by=total&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id={$rev}>{__("total")}</a></th>

</tr>
</thead>
{foreach from=$orders item="o"}
{hook name="orders:order_row"}
<tr>
    <td class="left">
        <input type="checkbox" name="order_ids[]" value="{$o.order_id}" class="cm-item cm-item-status-{$o.status|lower}" /></td>
    <td>
        <a href="{"orders.details?order_id=`$o.order_id`"|fn_url}" class="underlined">{__("order")} #{$o.order_id}</a>
        {if $order_statuses_data[$o.status].params.appearance_type == "I" && $o.invoice_id}
            <p class="small-note">{__("invoice")} #{$o.invoice_id}</p>
        {elseif $order_statuses_data[$o.status].params.appearance_type == "C" && $o.credit_memo_id}
            <p class="small-note">{__("credit_memo")} #{$o.credit_memo_id}</p>
        {/if}
        {include file="views/companies/components/company_name.tpl" object=$o}
    </td>
    <td>
        {if "MULTIVENDOR"|fn_allowed_for}
            {assign var="notify_vendor" value=true}
        {else}
            {assign var="notify_vendor" value=false}
        {/if}

        {include file="common/select_popup.tpl" suffix="o" order_info=$o id=$o.order_id status=$o.status items_status=$order_status_descr update_controller="orders" notify=true notify_department=true notify_vendor=$notify_vendor status_target_id="orders_total,`$rev`" extra="&return_url=`$extra_status`" statuses=$order_statuses btn_meta="btn btn-info o-status-`$o.status` btn-small"|lower}
    </td>
    <td>{$o.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}</td>
    <td>
        {if $o.email}<a href="mailto:{$o.email|escape:url}">@</a> {/if}
        {if $o.user_id}<a href="{"profiles.update?user_id=`$o.user_id`"|fn_url}">{/if}{$o.lastname} {$o.firstname}{if $o.user_id}</a>{/if}
    </td>
    <td>{$o.phone}</td>

    {hook name="orders:manage_data"}{/hook}

    <td width="5%" class="center">
        {capture name="tools_items"}
            <li>{btn type="list" href="orders.details?order_id=`$o.order_id`" text={__("view")}}</li>
            {hook name="orders:list_extra_links"}
                <li>{btn type="list" href="order_management.edit?order_id=`$o.order_id`" text={__("edit")}}</li>
                {assign var="current_redirect_url" value=$config.current_url|escape:url}
                <li>{btn type="list" href="orders.delete?order_id=`$o.order_id`&redirect_url=`$current_redirect_url`" class="cm-confirm" text={__("delete")}}</li>
            {/hook}
        {/capture}
        <div class="hidden-tools">
            {dropdown content=$smarty.capture.tools_items}
        </div>
    </td>
    <td class="right">
        {include file="common/price.tpl" value=$o.total}
    </td>
</tr>
{/hook}
{/foreach}
</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}

{if $orders}
    <div class="statistic clearfix" id="orders_total">
        {hook name="orders:statistic_list"}
        <table class="pull-right ">
            {if $total_pages > 1 && $search.page != "full_list"}
                <tr>
                    <td>&nbsp;</td>
                    <td width="100px">{__("for_this_page_orders")}:</td>
                </tr>
                <tr>
                    <td>{__("gross_total")}:</td>
                    <td>{include file="common/price.tpl" value=$display_totals.gross_total}</td>
                </tr>
                {if !$incompleted_view}
                    <tr>
                        <td>{__("totally_paid")}:</td>
                        <td>{include file="common/price.tpl" value=$display_totals.totally_paid}</td>
                    </tr>
                {/if}
                <hr />
                <tr>
                    <td>{__("for_all_found_orders")}:</td>
                </tr>
            {/if}
            <tr>
                <td>{__("gross_total")}:</td>
                <td>{include file="common/price.tpl" value=$totals.gross_total}</td>
            </tr>
            {hook name="orders:totals_stats"}
                {if !$incompleted_view}
                    <tr>
                        <td><h4>{__("totally_paid")}:</h4></td>
                        <td class="price">{include file="common/price.tpl" value=$totals.totally_paid}</td>
                    </tr>
                {/if}
            {/hook}
        </table>
        {/hook}
    <!--orders_total--></div>
{/if}

{include file="common/pagination.tpl" div_id=$smarty.request.content_id}


{capture name="adv_buttons"}
    {hook name="orders:manage_tools"}
        {include file="common/tools.tpl" tool_href="order_management.new" prefix="bottom" hide_tools="true" title=__("add_order") icon="icon-plus"}
    {/hook}
{/capture}

</form>
{/capture}

{capture name="buttons"}
    {capture name="tools_list"}
        {if $orders}
            <li>{btn type="list" text={__("bulk_print_invoice")} dispatch="dispatch[orders.bulk_print]" form="orders_list_form" class="cm-new-window"}</li>
            <li>{btn type="list" text="{__("bulk_print_pdf")}" dispatch="dispatch[orders.bulk_print..pdf]" form="orders_list_form"}</li>            
            <li>{btn type="list" text="{__("bulk_print_packing_slip")}" dispatch="dispatch[orders.packing_slip]" form="orders_list_form" class="cm-new-window"}</li>
            <li>{btn type="list" text={__("view_purchased_products")} dispatch="dispatch[orders.products_range]" form="orders_list_form"}</li>
            
            <li class="divider"></li>
            <li>{btn type="list" text={__("export_selected")} dispatch="dispatch[orders.export_range]" form="orders_list_form"}</li>
            {if $incompleted_view}
                <li>{btn type="list" href="orders.manage" text={__("view_all_orders")}}</li>
            {else}
                <li>{btn type="list" href="orders.manage?skip_view=Y&status=`$smarty.const.STATUS_INCOMPLETED_ORDER`" text={__("incompleted_orders")} form="orders_list_form"}</li>
            {/if}
            {if $orders && !$runtime.company_id}
                <li class="divider"></li>
                <li>{btn type="delete_selected" dispatch="dispatch[orders.m_delete]" form="orders_list_form"}</li>
            {/if}
        {/if}
        {hook name="orders:list_tools"}
        {/hook}
    {/capture}
    {dropdown content=$smarty.capture.tools_list}
{/capture}

{include file="common/mainbox.tpl" title=$page_title sidebar=$smarty.capture.sidebar content=$smarty.capture.mainbox buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons content_id="manage_orders"}
