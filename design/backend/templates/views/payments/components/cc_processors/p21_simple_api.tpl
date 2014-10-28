{__("processor_description_p21")}
<hr/>

<div class="control-group">
    <label class="control-label" for="merchant_id">{__("merchant_id")}:</label>
    <div class="controls">
    	<input type="text" name="payment_data[processor_params][merchant_id]" id="merchant_id" value="{$processor_params.merchant_id}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="password">{__("password")}:</label>
    <div class="controls">
    	<input type="password" name="payment_data[processor_params][password]" id="password" value="{$processor_params.password}"   size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ip_address">{__("ip_address")}:</label>
    <div class="controls">
    	<input type="text" name="payment_data[processor_params][ip_address]" id="ip_address" value="{$processor_params.ip_address}"   size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="company">{__("company")}:</label>
    <div class="controls">
    	<input type="text" name="payment_data[processor_params][company]" id="company" value="{$processor_params.company}"   size="60">
    </div>
</div>