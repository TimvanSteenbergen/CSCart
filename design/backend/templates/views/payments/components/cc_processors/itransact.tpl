<div class="control-group">
    <label class="control-label" for="vendor_id">{__("vendor_id")}:</label>
    <div class="controls">
    	<input type="text" name="payment_data[processor_params][vendor_id]" id="vendor_id" value="{$processor_params.vendor_id}" >
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="merchant_name">{__("merchant_name")}:</label>
    <div class="controls">
    	<input type="text" name="payment_data[processor_params][merchant_name]" id="merchant_name" value="{$processor_params.merchant_name}" >
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="secret_key">{__("secret_key")}:</label>
    <div class="controls">
    	<input type="text" name="payment_data[processor_params][secret_key]" id="secret_key" value="{$processor_params.secret_key}" >
    	<p><small>{__("text_secret_key_notice")}</small></p>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="order_prefix">{__("order_prefix")}:</label>
    <div class="controls">
    	<input type="text" name="payment_data[processor_params][order_prefix]" id="order_prefix" value="{$processor_params.order_prefix}" >
    </div>
</div>