{assign var="verified_url" value="payment_notification.notify.verified?payment=pay4later"|fn_url:'C':'http'}
{assign var="refer_url" value="payment_notification.refer?payment=pay4later"|fn_url:'C':'http'}
{assign var="decline_url" value="payment_notification.decline?payment=pay4later"|fn_url:'C':'http'}
{assign var="cancel_url" value="payment_notification.cancel?payment=pay4later"|fn_url:'C':'http'}
{assign var="process_url" value="payment_notification.process?payment=pay4later"|fn_url:'C':'http'}
<p>{__("text_pay4later_notice", ["[verified_url]" => $verified_url, "[decline_url]" => $decline_url, "[cancel_url]" => $cancel_url, "[refer_url]" => $refer_url, "[process_url]" => $process_url])}</p>
<hr>

<div class="control-group">
    <label class="control-label" for="p4l_merchant_key">{__("merchant_key")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][merchant_key]" id="p4l_merchant_key" value="{$processor_params.merchant_key}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="p4l_installation_id">{__("installation_id")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][installation_id]" id="p4l_installation_id" value="{$processor_params.installation_id}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="p4l_finance_product_code">{__("finance_product_code")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][finance_product_code]" id="p4l_finance_product_code" value="{$processor_params.finance_product_code|default:'ONIF6'}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="p4l_deposit_amount">{__("deposit_amount")}:</label>
   <div class="controls">
        <input type="text" name="payment_data[processor_params][deposit_amount]" id="p4l_deposit_amount" value="{$processor_params.deposit_amount|default:'0.00'}"  size="60">
   </div>
</div>

<div class="control-group">
    <label class="control-label" for="p4l_mode">{__("test_live_mode")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][mode]" id="p4l_mode">
            <option value="test"{if $processor_params.mode eq "test"} selected="selected"{/if}>{__("test")}
            <option value="live"{if $processor_params.mode eq "live"} selected="selected"{/if}>{__("live")}
        </select>
    </div>
</div>