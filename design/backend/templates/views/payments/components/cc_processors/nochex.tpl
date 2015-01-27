<div class="control-group">
    <label class="control-label" for="">{__("merchant_id")}:</label>
    <div class="controls">
    	<input type="text" name="payment_data[processor_params][merchantid]" value="{$processor_params.merchantid}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="">{__("payment_details")}:</label>
    <div class="controls">
    	<input type="text" name="payment_data[processor_params][payment_description]" value="{$processor_params.payment_description}"   size="60">
    </div>
</div>