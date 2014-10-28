<div class="control-group">
    <label class="control-label" for="username">{__("username")}:</label>
    <div class="controls">
    	<input type="text" name="payment_data[processor_params][username]" id="username" value="{$processor_params.username}"   size="40">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="password">{__("password")}:</label>
    <div class="controls">
    	<input type="text" name="payment_data[processor_params][password]" id="password" value="{$processor_params.password}"  size="40">
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