<div class="control-group">
    <label class="control-label" for="merchant_id">{__("merchant_id")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][merchant_id]" id="merchant_id" value="{$processor_params.merchant_id}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="terminal">{__("terminal")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][terminal]" id="terminal" value="{$processor_params.terminal}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="clave">{__("secret_key")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][clave]" id="clave" value="{$processor_params.clave}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="test">{__("test_live_mode")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][test]" id="test">
            <option value="N" {if $processor_params.test == "N"}selected="selected"{/if}>{__("live")}</option>
            <option value="Y" {if $processor_params.test == "Y"}selected="selected"{/if}>{__("test")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="currency">{__("currency")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][currency]" id="currency">
            <option value="978"{if $processor_params.currency eq "978"} selected="selected"{/if}>{__("currency_code_eur")}
            <option value="840"{if $processor_params.currency eq "840"} selected="selected"{/if}>{__("currency_code_usd")}
        </select>
    </div>
</div>