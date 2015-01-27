{hook name="order_management:payment_method"}
    {if $settings.General.min_order_amount <= $cart.total}
        {if $cart.total != 0}
        <div class="control-group">
            <div class="control-label">
                <h4 class="subheader">{__("payment_information")}</h4>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="payment_methods">{__("method")}</label>
            <div class="controls">
            <select name="payment_id" id="payment_methods" onchange="Tygh.$.selectPaymentMethod(Tygh.$(this).val())">
                {foreach from=$payment_methods item="pm" name="pay"}
                <option value="{$pm.payment_id}" {if $cart.payment_id == $pm.payment_id || (!$cart.payment_id && $smarty.foreach.pay.first)}{assign var="selected_payment_id" value=$pm.payment_id}selected="selected"{/if}>{$pm.payment}</option>
                {/foreach}
            </select>
            </div>
        </div>
        {if $payment_method.template}
            {capture name="payment_details"}
                {include file=$payment_method.template payment_id=$payment_method.payment_id}
            {/capture}
            {if $smarty.capture.payment_details|trim ne ''}
                {$smarty.capture.payment_details nofilter}
            {/if}
        {/if}
        {/if}
    {elseif $settings.General.min_order_amount > $cart.total}
        <label class="error-text">
            {__("text_min_order_amount_required")}&nbsp;<span>{include file="common/price.tpl" value=$settings.General.min_order_amount}</span>
        </label>
    {/if}
{/hook}