<div class="control-group">
    <label class="control-label" for="merchant_id">{__("merchant_id")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][merchant_id]" id="merchant_id" value="{$processor_params.merchant_id}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="merchant_key">{__("secret_key")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][merchant_key]" id="merchant_key" value="{$processor_params.merchant_key}"   size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="description">{__("description")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][description]" id="description" value="{$processor_params.description}"   size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="test">{__("test_live_mode")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][test]" id="test">
            <option value="0" {if $processor_params.test == "0"}selected="selected"{/if}>{__("live")}</option>
            <option value="1" {if $processor_params.test == "1"}selected="selected"{/if}>{__("test")}</option>
        </select>
    </div>
</div>