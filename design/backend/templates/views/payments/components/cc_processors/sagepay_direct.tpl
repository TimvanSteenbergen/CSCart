<p>{__("text_sagepay_dir_notice")}</p>
<hr>

<div class="control-group">
    <label class="control-label" for="vendor">{__("vendor_name")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][vendor]" id="vendor" value="{$processor_params.vendor}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="order_prefix">{__("order_prefix")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][order_prefix]" id="order_prefix" value="{$processor_params.order_prefix}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="transaction_type">{__("transaction_type")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][transaction_type]" id="transaction_type">
            <option value="AUTHENTICATE" {if $processor_params.transaction_type == "AUTHENTICATE"}selected="selected"{/if}>AUTHENTICATE</option>
            <option value="DEFERRED" {if $processor_params.transaction_type == "DEFERRED"}selected="selected"{/if}>DEFERRED</option>
            <option value="PAYMENT" {if $processor_params.transaction_type == "PAYMENT"}selected="selected"{/if}>PAYMENT</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="currency">{__("currency")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][currency]" id="currency">
            <option value="GBP" {if $processor_params.currency == "GBP"}selected="selected"{/if}>{__("currency_code_gbp")}</option>
            <option value="EUR" {if $processor_params.currency == "EUR"}selected="selected"{/if}>{__("currency_code_eur")}</option>
            <option value="USD" {if $processor_params.currency == "USD"}selected="selected"{/if}>{__("currency_code_usd")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="testmode">{__("test_live_mode")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][testmode]" id="testmode">
            <option value="Y" {if $processor_params.testmode == "Y"}selected="selected"{/if}>{__("test")}</option>
            <option value="N" {if $processor_params.testmode == "N"}selected="selected"{/if}>{__("live")}</option>
            <option value="S" {if $processor_params.testmode == "S"}selected="selected"{/if}>DEV</option>
        </select>
    </div>
</div>