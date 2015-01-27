{capture name="mainbox"}

<p>{__("text_confirmation_page_header")}</p>

{if $change_return_status}
<form action="{""|fn_url}" method="post" name="change_return_status">
<input type="hidden" name="confirmed" value="Y" />
{foreach from=$change_return_status item="value" key="field"}
<input type="hidden" name="change_return_status[{$field}]" value="{$value}" />
{/foreach}

<div>
    {assign var="status_to" value=$change_return_status.status_to}
    {assign var="status_from" value=$change_return_status.status_from}
    {__("text_return_change_warning", ["[return_id]" => $change_return_status.return_id])}&nbsp;<span>{$status_descr.$status_from}&nbsp;&#8212;&#8250;&nbsp;{$status_descr.$status_to}</span>.
</div>
{if $change_return_status.recalculate_order == "M"}
<div class="control-group">
    <label for="total" class="cm-required control-label">{__("order_total_will_changed")}:</label>
    <div class="controls">
        <input id="total" type="text" name="change_return_status[total]" value="{$change_return_status.total}" size="5" class="input-text" />
    </div>
</div>
{elseif $change_return_status.recalculate_order == "R"}

{if $shipping_info}
<div>
    {__("shipping_costs_will_changed")}:
</div>
{foreach from=$shipping_info item="shipping" key="shipping_id"}
<div class="control-group">
    <label for="sh_{$shipping_id}" class="control-label cm-required">{$shipping.shipping}:</label>
    <div class="controls">
        <input id="sh_{$shipping_id}" type="text" name="change_return_status[shipping_costs][{$shipping_id}]" value="{$shipping.cost}" size="5" class="input-text" />
    </div>
</div>
{/foreach}
{/if}

{/if}
<p>{__("text_are_you_sure_to_proceed")}</p>

<div class="buttons-container">    
    {include file="buttons/button.tpl" but_text=__("yes") but_name="dispatch[rma.update_details]"}
    {include file="buttons/button.tpl" but_text=__("no") but_meta="cm-back-link"}
</div>

</form>
{/if}
{/capture}
{include file="common/mainbox.tpl" title=__("confirmation_dialog") content=$smarty.capture.mainbox}