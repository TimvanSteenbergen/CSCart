<div class="control-group">
    <label class="control-label" for="username">{__("username")}:</label>
    <div class="controls">
    	<input type="text" name="payment_data[processor_params][username]" id="username" value="{$processor_params.username}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="password">{__("password")}:</label>
    <div class="controls">
    	<input type="password" name="payment_data[processor_params][password]" id="password" value="{$processor_params.password}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="type">{__("type")}:</label>
    <div class="controls">
    	<select name="payment_data[processor_params][type]" id="type">
    	    <option value="AUTORIZATION" {if $processor_params.type eq "AUTORIZATION"} selected="selected"{/if}>{__("authorization")}</option>
    	    <option value="AUTORIZATION_CAPTURE" {if $processor_params.type eq "AUTORIZATION_CAPTURE"} selected="selected"{/if}>{__("authorization")} + {__("capture")}</option>
    	</select>
    </div>
</div>
