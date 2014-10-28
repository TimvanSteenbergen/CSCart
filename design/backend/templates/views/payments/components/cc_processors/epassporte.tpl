<div class="control-group">
    <label class="control-label" for="acct_num">{__("merchant_id")}:</label>
    <div class="controls">
    	<input type="text" name="payment_data[processor_params][acct_num]" id="acct_num" value="{$processor_params.acct_num}"   size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="pi_code">{__("product_code")}:</label>
    <div class="controls">
    	<input type="text" name="payment_data[processor_params][pi_code]" id="pi_code" value="{$processor_params.pi_code}"   size="60">
    </div>
</div>