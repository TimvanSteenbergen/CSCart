<div class="control-group">
    <label class="control-label" for="gateway">{__("gateway")}:</label>
    <div class="controls">
    	<select name="payment_data[processor_params][gateway]" id="gateway">
    	    <option value="payment" {if $processor_params.gateway == "payment"}selected="selected"{/if}>{__("united_kingdom")}</option>
    	    <option value="nz" {if $processor_params.gateway == "nz"}selected="selected"{/if}>{__("new_zealand")}</option>
            <option value="au" {if $processor_params.gateway == "au"}selected="selected"{/if}>{__("australia")}</option>
    	</select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="customer_id">{__("customer_id")}:</label>
    <div class="controls">
    	<input type="text" name="payment_data[processor_params][customer_id]" id="customer_id" value="{$processor_params.customer_id}" >
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="username">{__("username")}:</label>
    <div class="controls">
    	<input type="text" name="payment_data[processor_params][username]" id="username" value="{$processor_params.username}" >
    </div>
</div>
