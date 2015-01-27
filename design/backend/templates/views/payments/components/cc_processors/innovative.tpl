<p>{__("text_innovative_notice")}</p>
<hr>

<div class="control-group">
    <label class="control-label" for="username">{__("username")}:</label>
    <div class="controls">
    	<input type="text" name="payment_data[processor_params][username]" id="username" value="{$processor_params.username}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="password">{__("password")}:</label>
    <div class="controls">
    	<input type="text" name="payment_data[processor_params][password]" id="password" value="{$processor_params.password}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="mode">{__("test_live_mode")}:</label>
    <div class="controls">
    	<select name="payment_data[processor_params][mode]" id="mode">
    	    <option value="test" {if $processor_params.mode == "test"}selected="selected"{/if}>{__("test")}: {__("approved")}</option>
    	    <option value="live" {if $processor_params.mode == "live"}selected="selected"{/if}>{__("live")}</option>
    	</select>
    </div>
</div>