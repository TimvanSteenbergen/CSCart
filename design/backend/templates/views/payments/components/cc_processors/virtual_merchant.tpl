<div class="control-group">
    <label class="control-label" for="merchant_id">{__("merchant_id")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][merchant_id]" id="merchant_id" value="{$processor_params.merchant_id}" >
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="user_id">{__("user_id")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][user_id]" id="user_id" value="{$processor_params.user_id}" >
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="user_pin">{__("user_pin")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][user_pin]" id="user_pin" value="{$processor_params.user_pin}" >
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="mode">{__("test_live_mode")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][mode]" id="mode">
            <option value="test" {if $processor_params.mode == "test"}selected="selected"{/if}>{__("test")}</option>
            <option value="live" {if $processor_params.mode == "live"}selected="selected"{/if}>{__("live")}</option>
            <option value="demo" {if $processor_params.mode == "demo"}selected="selected"{/if}>{__("demo")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="order_prefix">{__("order_prefix")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][order_prefix]" id="order_prefix" value="{$processor_params.order_prefix}" >
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="cvv2">{__("cvv2")}:</label>
    <div class="controls">
        <input type="hidden" name="payment_data[processor_params][cvv2]" value="N">
        <input type="checkbox" name="payment_data[processor_params][cvv2]" id="cvv2" value="Y" {if $processor_params.cvv2 == "Y"}checked="checked"{/if}>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="avs">{__("avs")}:</label>
    <div class="controls">
        <input type="hidden" name="payment_data[processor_params][avs]" value="N">
        <input type="checkbox" name="payment_data[processor_params][avs]" id="avs" value="Y" {if $processor_params.avs == "Y"}checked="checked"{/if}>
    </div>
</div>