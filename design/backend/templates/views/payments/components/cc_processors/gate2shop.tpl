{assign var="r_url" value="current"|fn_payment_url:"gate2shop.php"}
{assign var="b_url" value="payment_notification.index_redirect?payment=gate2shop"|fn_url:"C":"http"}

<div> 
    {__("text_gate2shop_notice", ["[result_url]" => $r_url, "[back_url]" => $b_url])}
</div> 
<hr>

<div class="control-group">
    <label class="control-label" for="merchant_id">{__("merchant_id")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][merchant_id]" id="merchant_id" value="{$processor_params.merchant_id}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="merchant_site_id">{__("merchant_site_id")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][merchant_site_id]" id="merchant_site_id" value="{$processor_params.merchant_site_id}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="secret_string">{__("secret_string")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][secret_string]" id="secret_string" value="{$processor_params.secret_string}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="currency">{__("currency")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][currency]" id="currency">
            <option value="EUR" {if $processor_params.currency == "EUR" || $processor_params.currency == ""}selected="selected"{/if}>{__("currency_code_eur")}</option>
            <option value="USD" {if $processor_params.currency == "USD"}selected="selected"{/if}>{__("currency_code_usd")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="order_prefix">{__("order_prefix")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][order_prefix]" id="order_prefix" value="{$processor_params.order_prefix}" >
    </div>
</div>
