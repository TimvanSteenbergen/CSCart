<div class="control-group">
    <label class="control-label" for="vendor">{__("vendor_name")}:</label>
    <div class="controls">
        <input type="text" id="vendor" name="payment_data[processor_params][vendor]" value="{$processor_params.vendor}"  size="60">
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="password">{__("encryption")} {__("password")}:</label>
    <div class="controls">
        <input type="password" id="password" name="payment_data[processor_params][password]" value="{$processor_params.password}"  size="60">
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="order_prefix">{__("order_prefix")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][order_prefix]" value="{$processor_params.order_prefix}"  size="60">
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="testmode">{__("test_live_mode")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][testmode]" id="testmode">
            <option value="Y" {if $processor_params.testmode == "Y"}selected="selected"{/if}>{__("test")}</option>
            <option value="N" {if $processor_params.testmode == "N"}selected="selected"{/if}>{__("live")}</option>
        </select>
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="currency">{__("currency")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][currency]" id="currency">
            <option value="GBP" {if $processor_params.currency == "826"}selected="selected"{/if}>{__("currency_code_gbp")}</option>
            <option value="EUR" {if $processor_params.currency == "978"}selected="selected"{/if}>{__("currency_code_eur")}</option>
            <option value="USD" {if $processor_params.currency == "840"}selected="selected"{/if}>{__("currency_code_usd")}</option>
            <option value="AUD" {if $processor_params.currency == "036"}selected="selected"{/if}>{__("currency_code_aud")}</option>
        </select>
    </div>
</div>