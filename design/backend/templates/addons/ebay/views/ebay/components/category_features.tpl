<div id="box_ebay_cf_{$data_id}">
{if $category_features}
    {if $category_features.PaymentMethod}
    <div class="control-group" >
        <label class="control-label cm-required" for="elm_ebay_payment_methods">{__("ebay_payment_methods")}{include file="common/tooltip.tpl" tooltip={__("ebay_payment_method_tooltip")}}:</label>
        <div class="controls">
        <select size="5" id="elm_ebay_payment_methods" name="template_data[payment_methods][]" multiple="multiple">
            {foreach from=$category_features.PaymentMethod item="payment"}
                <option {if (is_array($template_data.payment_methods) && in_array($payment, $template_data.payment_methods) && $category_features.PayPalRequired == 'false') || ($category_features.PayPalRequired == 'true' && $payment == 'PayPal')}selected="selected"{/if} value="{$payment}">{$payment}</option>
            {/foreach}
        </select>
        {if $category_features.PayPalRequired == 'true'}<p class="ebay_paypal_notice muted">{__('paypal_required_and_selected')}</p>{/if}
        </div>
    </div>
    {/if}

    {if $category_features.PayPalRequired == 'true' || (is_array($category_features.PaymentMethod) && in_array('PayPal', $category_features.PaymentMethod))}
    <div class="control-group">
        <label for="elm_paypal_email" class="control-label cm-email {if $category_features.PayPalRequired == 'true'}cm-required{/if}">{__("paypal_email")}{include file="common/tooltip.tpl" tooltip={__("paypal_email_tooltip")}}:</label>
        <div class="controls">
            <input type="text" id="elm_paypal_email" name="template_data[paypal_email]" class="input-large" size="32" maxlength="128" value="{$template_data.paypal_email}" />
        </div>
    </div>
    {/if}
    
    {if $category_features.ConditionEnabled == 'Required' && $category_features.ConditionValues}
    <div class="control-group" >
        <label class="control-label" for="elm_ebay_condition">{__("ebay_category_condition")}:</label>
        <div class="controls">
        <select id="elm_ebay_condition" name="template_data[condition_id]">
            {foreach from=$category_features.ConditionValues->Condition item="condition"}
                <option {if $template_data.condition_id == $condition->ID}selected="selected"{/if} value="{$condition->ID}">{$condition->DisplayName}</option>
            {/foreach}
        </select>
        </div>
    </div>
    {/if}

    {if $category_features.listing_duration}
    <div class="control-group" id="ebay_duration">
        <label class="control-label cm-required" for="elm_ebay_duration">{__("ebay_duration")}:</label>
        <div class="controls">
        <select id="elm_ebay_duration" name="template_data[ebay_duration]">
            {foreach from=$category_features.listing_duration item="item"}
            <option {if $template_data.ebay_duration == $item}selected="selected"{/if} value="{$item}">{$item}</option>
            {/foreach}
        </select>
        </div>
    </div>
    {/if}
{/if}
<!--box_ebay_cf_{$data_id}--></div>
