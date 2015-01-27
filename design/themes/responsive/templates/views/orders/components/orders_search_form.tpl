<form action="{""|fn_url}" name="orders_search_form" method="get">

<div class="ty-control-group">
    <label class="ty-control-group__title">{__("total")}&nbsp;({$currencies.$primary_currency.symbol nofilter})</label>
    <input type="text" name="total_from" value="{$search.total_from}" size="3" class="ty-input-text-short" />&nbsp;&#8211;&nbsp;<input type="text" name="total_to" value="{$search.total_to}" size="3" class="ty-input-text-short" />
</div>

<div class="ty-control-group">
    <label class="ty-control-group__title">{__("order_status")}</label>
    {include file="common/status.tpl" status=$search.status display="checkboxes" name="status"}
</div>

{include file="common/period_selector.tpl" period=$search.period form_name="orders_search_form"}

{if $auth.user_id}
<div class="ty-control-group">
    <label class="ty-control-group__title">{__("order_id")}</label>
    <input type="text" name="order_id" value="{$search.order_id}" size="10" class="ty-search-form__input" />
</div>
{/if}

<div class="buttons-container ty-search-form__buttons-container">
    {include file="buttons/button.tpl" but_meta="ty-btn__secondary" but_text=__("search") but_name="dispatch[orders.search]"}
</div>
</form>