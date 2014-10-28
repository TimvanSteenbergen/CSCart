{assign var="r_url" value="payment_notification?payment=ideal_basic"|fn_url:'C':'http'}
{assign var="e_url" value="payment_notification.result?payment=ideal_basic"|fn_url:'C':'http'}
<p>{__("text_ideal_basic_notice", ["[return_url]" => $r_url, "[error_url]" => $e_url])}</p>
<hr>

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
    <label class="control-label" for="language">{__("language")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][language]" id="language">
            <option value="nl"{if $processor_params.language == "nl"} selected="selected"{/if}>{__("dutch")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="test">{__("test_live_mode")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][test]" id="test">
            <option value="FALSE" {if $processor_params.test == "FALSE"}selected="selected"{/if}>{__("live")}</option>
            <option value="TRUE" {if $processor_params.test == "TRUE"}selected="selected"{/if}>{__("test")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="currency">{__("currency")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][currency]" id="currency">
            <option value="EUR" {if $processor_params.currency == "EUR"}selected="selected"{/if}>{__("currency_code_eur")}</option>
        </select>
    </div>
</div>