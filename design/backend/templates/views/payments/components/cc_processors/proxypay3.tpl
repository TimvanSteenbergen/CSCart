<p>{__("text_proxypay_notice", ["[validation_url]" => "current"|fn_payment_url:"proxypay3_validation.php", "[confirmation_url]" => "current"|fn_payment_url:"proxypay3_confirmation.php", "[ok_url]" => "current"|fn_payment_url:"proxypay3_ok.php", "[nok_url]" => "current"|fn_payment_url:"proxypay3_nok.php"])}</p>
<hr>

<div class="control-group">
    <label class="control-label" for="merchantid">{__("merchant_id")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][merchantid]" id="merchantid" value="{$processor_params.merchantid}" >
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="url">{__("payment")} {__("url")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][url]" id="url" value="{$processor_params.url|default:"eptest.eurocommerce.gr/proxypay/apacs"}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="details">{__("payment_details")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][details]" id="details" value="{$processor_params.details}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="currency">{__("currency")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][currency]" id="currency">
            <option value="0840" {if $processor_params.currency == "0840"}selected="selected"{/if}>{__("currency_code_usd")}</option>
            <option value="0978" {if $processor_params.currency == "0978"}selected="selected"{/if}>{__("currency_code_eur")}</option>
            <option value="0300" {if $processor_params.currency == "0300"}selected="selected"{/if}>{__("currency_9")}</option>
        </select>
    </div>
</div>