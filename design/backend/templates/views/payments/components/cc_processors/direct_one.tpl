<div class="control-group">
    <label class="control-label" for="merchant_id">{__("vendor_name")}:</label>
    <div class="controls">
    	<input type="text" name="payment_data[processor_params][merchant_id]" id="merchant_id" value="{$processor_params.merchant_id}"   size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="mode">{__("test_live_mode")}:</label>
    <div class="controls">
    	<select name="payment_data[processor_params][mode]" id="mode">
    	    <option value="test"{if $processor_params.mode == 'test'} selected="selected"{/if}>{__("test")}</option>
    	    <option value="live"{if $processor_params.mode == 'live'} selected="selected"{/if}>{__("live")}</option>
    	</select>
    </div>
</div>