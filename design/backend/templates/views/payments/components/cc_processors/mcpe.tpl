{assign var="url" value="current"|fn_payment_url:"mcpe_result.php"}
<p>{__("text_mcpe_notice", ["[return_url]" => "<span>`$url`</span>"])}</p>
<hr>

<div class="control-group">
    <label class="control-label" for="merchant_id">{__("merchant_id")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][merchant_id]" id="merchant_id" value="{$processor_params.merchant_id}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="mode">{__("test_live_mode")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][mode]" id="mode">
            <option value="0" {if $processor_params.mode == "0"}selected="selected"{/if}>{__("live")}</option>
            <option value="1" {if $processor_params.mode == "1"}selected="selected"{/if}>{__("test")}: {__("approved")}</option>
            <option value="2" {if $processor_params.mode == "2"}selected="selected"{/if}>{__("test")}: {__("declined")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="currency">{__("currency")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][currency]" id="currency">
            <option value="GBP" {if $processor_params.currency eq "GBP"}selected="selected"{/if}>{__("currency_code_gbp")}
            <option value="USD" {if $processor_params.currency eq "USD"}selected="selected"{/if}>{__("currency_code_usd")}
            <option value="EUR" {if $processor_params.currency eq "EUR"}selected="selected"{/if}>{__("currency_code_eur")}
        </select>
    </div>
</div>