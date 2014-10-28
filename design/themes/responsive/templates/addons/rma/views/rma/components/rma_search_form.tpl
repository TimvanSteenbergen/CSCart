<form action="{""|fn_url}" name="rma_search_form" method="get" class="ty-rma-search">

<div class="ty-control-group">
    <label class="ty-control-group__title" for="qty">{__("quantity")}</label>
    <input id="qty" type="text" name="rma_amount_from" value="{$smarty.request.rma_amount_from}" size="3" class="ty-input-text-short" />&nbsp;-&nbsp;<input type="text" name="rma_amount_to" value="{$smarty.request.rma_amount_to}" size="3" class="ty-input-text-short" />
</div>

<div class="ty-control-group">
    <label class="ty-control-group__title" for="r_id">{__("rma_return")}</label>
    <input id="r_id" type="text" name="return_id" value="{$smarty.request.return_id}" size="30" class="ty-input-text" />
</div>

<div class="ty-control-group">
    <label class="ty-control-group__title">{__("return_status")}</label>
    <div class="ty-rma-search__status">
        {include file="common/status.tpl" status=$smarty.request.request_status display="checkboxes" name="request_status" status_type=$smarty.const.STATUSES_RETURN}
    </div>
</div>

{include file="common/period_selector.tpl" period=$smarty.request.period form_name="rma_search_form"}

<div class="ty-rma-search__toggle cm-combination cm-save-state cm-ss-reverse" id="sw_s_show_options">
    <span class="ty-rma-search__toggle-title">{__("search_by_order")}<i id="on_s_show_options" class="ty-rma-search__toggle-icon ty-icon-down-open"></i><i id="off_s_show_options" class="ty-rma-search__toggle-icon ty-icon-up-open hidden"></i></span>
</div>

<div id="s_show_options"{if !($smarty.request.order_status || $smarty.request.order_id)} class="rma-options hidden"{/if}>
    <div class="ty-control-group">
        <label class="ty-control-group__title" for="r_id">{__("order")}&nbsp;{__("id")}</label>
        <input type="text" name="order_id" value="{$smarty.request.order_id}" size="30" class="ty-input-text" />
    </div>
    
    <div class="ty-control-group">
        <label class="ty-control-group__title" for="r_id">{__("order_status")}</label>
        <div class="ty-rma-search__status">
            {include file="common/status.tpl" status=$smarty.request.order_status display="checkboxes" name="order_status"}
        </div>
    </div>
</div>

<div class="ty-rma-search__buttons buttons-container">
    {if $action}
        {assign var="_action" value="$action"}
    {/if}
    {include file="buttons/button.tpl" but_text=__("search") but_name="dispatch[`$runtime.controller`.`$runtime.mode`.`$runtime.action`]" but_meta="ty-btn__secondary"}
</div>
</form>