<div class="control-group">
    <label class="control-label" for="client_id">{__("client_id")}:</label>
    <div class="controls">
    	<input type="text" name="payment_data[processor_params][client_id]" id="client_id" value="{$processor_params.client_id}"   size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="order_prefix">{__("order_prefix")}:</label>
    <div class="controls">
    	<input type="text" name="payment_data[processor_params][order_prefix]" id="order_prefix" value="{$processor_params.order_prefix}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="test">{__("test_live_mode")}:</label>
    <div class="controls">
    	<select name="payment_data[processor_params][test]" id="test">
    	    <option value="Y" {if $processor_params.test == "Y"}selected="selected"{/if}>{__("test")}</option>
    	    <option value="N" {if $processor_params.test == "N"}selected="selected"{/if}>{__("live")}</option>
    	</select>
    </div>
</div>