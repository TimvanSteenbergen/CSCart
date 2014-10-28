<form action="{""|fn_url}" name="rma_search_form" method="get" class="rma-search">

<div class="control-group">
    <label for="qty">{__("quantity")}</label>
    <input id="qty" type="text" name="rma_amount_from" value="{$smarty.request.rma_amount_from}" size="3" class="input-text-short" />&nbsp;-&nbsp;<input type="text" name="rma_amount_to" value="{$smarty.request.rma_amount_to}" size="3" class="input-text-short" />
</div>

<div class="control-group">
    <label for="r_id">{__("rma_return")}</label>
    <input id="r_id" type="text" name="return_id" value="{$smarty.request.return_id}" size="30" class="input-text" />
</div>

<div class="control-group">
    <label>{__("return_status")}</label>
    {include file="common/status.tpl" status=$smarty.request.request_status display="checkboxes" name="request_status" status_type=$smarty.const.STATUSES_RETURN}
</div>

{include file="common/period_selector.tpl" period=$smarty.request.period form_name="rma_search_form"}

<div class="rma-toggle cm-combination cm-save-state cm-ss-reverse" id="sw_s_show_options">
    <span>{__("search_by_order")}<i id="on_s_show_options" class="icon-down-open"></i><i id="off_s_show_options" class="icon-up-open hidden"></i></span>
</div>

<div id="s_show_options"{if !($smarty.request.order_status || $smarty.request.order_id)} class="rma-options hidden"{/if}>
    <div class="control-group">
        <label for="r_id">{__("order")}&nbsp;{__("id")}</label>
        <input type="text" name="order_id" value="{$smarty.request.order_id}" size="30" class="input-text" />
    </div>
    
    <div class="control-group">
        <label for="r_id">{__("order_status")}</label>
        {include file="common/status.tpl" status=$smarty.request.order_status display="checkboxes" name="order_status"}
    </div>
</div>

<div class="buttons-container">
    {if $action}
        {assign var="_action" value="$action"}
    {/if}
    {include file="buttons/button.tpl" but_text=__("search") but_name="dispatch[`$runtime.controller`.`$runtime.mode`.`$runtime.action`]"}
</div>
</form>