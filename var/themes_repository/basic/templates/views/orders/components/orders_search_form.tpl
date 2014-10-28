<form action="{""|fn_url}" name="orders_search_form" method="get">

<div class="control-group">
    <label>{__("total")}&nbsp;({$currencies.$primary_currency.symbol nofilter})</label>
    <input type="text" name="total_from" value="{$search.total_from}" size="3" class="input-text input-text-short" />&nbsp;&#8211;&nbsp;<input type="text" name="total_to" value="{$search.total_to}" size="3" class="input-text input-text-short" />
</div>

<div class="control-group">
    <label>{__("order_status")}</label>
    {include file="common/status.tpl" status=$search.status display="checkboxes" name="status"}
</div>

{include file="common/period_selector.tpl" period=$search.period form_name="orders_search_form"}

{if $auth.user_id}
<div class="control-group">
    <label>{__("order_id")}</label>
    <input type="text" name="order_id" value="{$search.order_id}" size="10" class="input-text" />
</div>
{/if}

<div class="buttons-container">
    {include file="buttons/button.tpl" but_text=__("search") but_name="dispatch[orders.search]"}
</div>
</form>