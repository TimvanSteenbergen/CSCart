{assign var="prepayment" value=""|fn_url:"C"}
{assign var="return" value="http"|fn_payment_url:"westpac.php"}
{assign var="notify" value="https"|fn_payment_url:"westpac.php"}
<p>{__("text_payway_notice", ["[prepayment]" => $prepayment, "[return]" => $return, "[notify]" => $notify])}</p>
<hr>

<div class="control-group">
    <label class="control-label" for="elm_merchant_id">{__("merchant_id")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][merchant_id]" id="elm_merchant_id" value="{$processor_params.merchant_id}"   size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_biller_code">{__("biller_code")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][biller_code]" id="elm_biller_code" value="{$processor_params.biller_code}"   size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="elm_encryption_key">{__("encryption_key")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][encryption_key]" id="elm_encryption_key" value="{$processor_params.encryption_key}"   size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="mode">{__("test_live_mode")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][mode]" id="mode">
            <option value="test" {if $processor_params.mode == "test"}selected="selected"{/if}>{__("test")}</option>
            <option value="live" {if $processor_params.mode == "live"}selected="selected"{/if}>{__("live")}</option>
        </select>
    </div>
</div>