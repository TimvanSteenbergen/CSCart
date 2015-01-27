<div class="control-group">
    <label class="control-label" for="terminal_id">{__("terminal_id")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][terminal_id]" id="terminal_id" value="{$processor_params.terminal_id}"   size="10">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="shared_secret">{__("shared_secret")}:</label>
    <div class="controls">
        <input type="password" name="payment_data[processor_params][shared_secret]" id="shared_secret" value="{$processor_params.shared_secret}"   size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="test">{__("test_live_mode")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][test]" id="test">
            <option value="1" {if $processor_params.test == "1"}selected="selected"{/if}>{__("test")}</option>
            <option value="0" {if $processor_params.test == "0"}selected="selected"{/if}>{__("live")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="avs">{__("avs")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][avs]" id="avs">
            <option value="1" {if $processor_params.avs == "1"}selected="selected"{/if}>{__("enabled")}</option>
            <option value="0" {if $processor_params.avs == "0"}selected="selected"{/if}>{__("disabled")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="currency">{__("currency")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][currency]" id="currency">
            <option value="EUR" {if $processor_params.currency == "EUR"}selected="selected"{/if}>{__("currency_code_eur")}</option>
            <option value="GBP" {if $processor_params.currency == "GBP"}selected="selected"{/if}>{__("currency_code_gbp")}</option>
            <option value="USD" {if $processor_params.currency == "USD"}selected="selected"{/if}>{__("currency_code_usd")}</option>
        </select>
    </div>
</div>