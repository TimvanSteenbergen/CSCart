<div class="control-group">
    <label class="control-label" for="merchant_id">{__("merchant_id")}:</label>
    <div class="controls">
    	<input type="text" name="payment_data[processor_params][merchant_id]" id="merchant_id" size="60" value="{$processor_params.merchant_id}" >
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="merchant_pin">{__("merchant_pin")}:</label>
    <div class="controls">
    	<input type="text" name="payment_data[processor_params][merchant_pin]" id="merchant_pin" size="60" value="{$processor_params.merchant_pin}" >
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="order_prefix">{__("order_prefix")}:</label>
    <div class="controls">
    	<input type="text" name="payment_data[processor_params][order_prefix]" id="order_prefix" size="60" value="{$processor_params.order_prefix}" >
    </div>
</div>