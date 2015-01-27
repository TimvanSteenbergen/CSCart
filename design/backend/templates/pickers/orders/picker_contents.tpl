{if !$smarty.request.extra}
<script type="text/javascript">
(function(_, $) {
    _.tr('text_items_added', '{__("text_items_added")|escape:"javascript"}');

    $.ceEvent('on', 'ce.formpost_add_orders', function(frm, elm) {
        var max_displayed_qty = {$smarty.request.max_displayed_qty|default:"0"};
        var details_url = '{"orders.manage?order_id="|fn_url}';
        var orders = {};

        if ($('input.cm-item:checked', frm).length > 0) {
            $('input.cm-item:checked', frm).each( function() {
                var id = $(this).val();
                var item = $(this).parent().parent();
                orders[id] = {
                    status: item.find('td.cm-order-status').text(), 
                    customer: item.find('td.cm-order-customer').text(), 
                    timestamp: item.find('td.cm-order-timestamp').text(),
                    total: item.find('td.cm-order-total').text()
                };
            });
            
            {literal}
            $.cePicker('add_js_item', frm.data('caResultId'), orders, 'o', {
                '{order_id}': '%id',
                '{status}': '%item.status',
                '{customer}': '%item.customer',
                '{timestamp}': '%item.timestamp',
                '{total}': '%item.total'
            });
            {/literal}

            $.cePicker('check_items_qty', frm.data('caResultId'), details_url, max_displayed_qty);
            
            $.ceNotification('show', {
                type: 'N', 
                title: _.tr('notice'), 
                message: _.tr('text_items_added'), 
                message_state: 'I'
            });            
        }

        return false;   
    });
}(Tygh, Tygh.$));
</script>
{/if}

{include file="views/orders/components/orders_search_form.tpl" dispatch="orders.picker" extra="<input type=\"hidden\" name=\"result_ids\" value=\"pagination_`$smarty.request.data_id`\"><input type=\"hidden\" name=\"data_id\" value=\"`$smarty.request.data_id`\"><input type=\"hidden\" name=\"extra\" value=\"`$smarty.request.extra`\" />" form_meta="cm-ajax" in_popup=true}

<form action="{$smarty.request.extra|fn_url}" data-ca-result-id="{$smarty.request.data_id}" method="post" name="add_orders">

{include file="common/pagination.tpl" save_current_page=true div_id="pagination_`$smarty.request.data_id`"}

{if $orders}
<table width="100%" class="table">
<tr>
    <th class="center" width="1%">
        {include file="common/check_items.tpl" class="mrg-check"}</th>
    <th width="10%">{__("id")}</th>
    <th width="15%">{__("status")}</th>
    <th width="25%">{__("customer")}</th>
    <th width="25%">{__("date")}</th>
    <th width="24%" class="right">{__("total")}</th>
</tr>
{foreach from=$orders item="o"}
<tr>
    <td class="center" width="1%">
        <input type="checkbox" name="add_parameter[]" value="{$o.order_id}" class="checkbox mrg-check cm-item" /></td>
    <td>
        <span>#{$o.order_id}</span></td>
    <td class="cm-order-status"><input type="hidden" name="origin_statuses[{$o.order_id}]" value="{$o.status}" />{include file="common/status.tpl" status=$o.status display="view" name="order_statuses[`$o.order_id`]"}</td>
    <td class="cm-order-customer">{$o.firstname} {$o.lastname}</td>
    <td class="cm-order-timestamp">
        {$o.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}</td>
    <td class="right cm-order-total">
        {include file="common/price.tpl" value=$o.total}</td>
</tr>
{/foreach}
</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}

{include file="common/pagination.tpl" div_id="pagination_`$smarty.request.data_id`"}

<div class="buttons-container">
    {include file="buttons/add_close.tpl" but_text=__("add_orders") but_close_text=__("add_orders_and_close") is_js=$smarty.request.extra|fn_is_empty}
</div>

</form>
