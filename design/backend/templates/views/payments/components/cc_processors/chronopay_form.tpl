<div class="control-group">
    <label class="control-label" for="product_id">{__("product_id")}:</label>
    <div class="controls">
    	<input type="text" name="payment_data[processor_params][product_id]" id="product_id" value="{$processor_params.product_id}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="encrypt">{__("sharedsec")}:</label>
    <div class="controls">
    	<input type="text" name="payment_data[processor_params][sharedsec]" id="encrypt" value="{$processor_params.sharedsec}"  size="60">
    </div>
</div>