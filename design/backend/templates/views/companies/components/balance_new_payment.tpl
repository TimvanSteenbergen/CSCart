<form action="{""|fn_url}" method="post" class="form-horizontal form-edit" name="shipments_form">
<input type="hidden" name="redirect_url" value="{$c_url}" />

{include file="views/companies/components/company_field.tpl"
    name="payment[vendor]"
    id="p_vendor"
    selected=$smarty.request.vendor|default:""
}

{if $settings.Appearance.calendar_date_format == "month_first"}
    {assign var="date_format" value="%m/%d/%Y"}
{else}
    {assign var="date_format" value="%d/%m/%Y"}
{/if}

<div class="control-group">
    <label class="control-label">{__("sales_period")}</label>
    <div class="controls">
    {include file="common/calendar.tpl" date_name="payment[start_date]" date_val=$total.new_period_date date_id="start_date"} - 
    {include file="common/calendar.tpl" date_name="payment[end_date]" date_val=$smarty.const.TIME date_id="end_date"}
    </div>
</div>

<div class="control-group">
    <label class="cm-required control-label" for="payment_amount">{__("payment_amount")}</label>
    <div class="controls">
        <input type="text" name="payment[amount]" id="payment_amount" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="payment_method">{__("payment_method")}</label>
    <div class="controls">
        <input type="text" name="payment[payment_method]" id="payment_method" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="payment_comments">{__("comments")}</label>
    <div class="controls">
    <textarea class="span9" rows="8" cols="55" name="payment[comments]" id="payment_comments"
    ></textarea></div>
</div>

<div class="control-group">
<label for="" class="control-label">&nbsp;</label>
<div class="controls cm-toggle-button">
    <div class="select-field notify-customer">
        <label class="checkbox" for="notify_user"><input type="checkbox" name="payment[notify_user]" id="notify_user" value="Y"  />
        {__("notify_vendor")}</label>
    </div>
</div>
</div>

{include file="views/companies/components/balance_info.tpl"}

<div class="buttons-container">
    {include file="buttons/save_cancel.tpl" but_name="dispatch[companies.payouts_add]" cancel_action="close"}
</div>

</form>