{assign var="return_url" value="http"|fn_payment_url:"worldpay.php"}
<p>{__("text_worldpay_notice", ["[return_url]" => $return_url])}</p>
<hr>

<div class="control-group">
    <label class="control-label" for="account_id">{__("installation_id")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][account_id]" id="account_id" value="{$processor_params.account_id}"   size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="callback_password">{__("payment_response_password")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][callback_password]" id="callback_password" value="{$processor_params.callback_password}"   size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="md5_secret">{__("worldpay_secret")}:</label>
   <div class="controls">
        <input type="text" name="payment_data[processor_params][md5_secret]" id="md5_secret" value="{$processor_params.md5_secret}"   size="60">
   </div>
</div>

<div class="control-group">
    <label class="control-label" for="test">{__("test_live_mode")}:</label>
   <div class="controls">
        <select name="payment_data[processor_params][test]" id="test">
           <option value="101" {if $processor_params.test == "101"}selected="selected"{/if}>{__("test")}: {__("declined")}</option>
           <option value="100" {if $processor_params.test == "100"}selected="selected"{/if}>{__("test")}: {__("approved")}</option>
           <option value="0" {if $processor_params.test == "0"}selected="selected"{/if}>{__("live")}</option>
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
            <option value="AUD" {if $processor_params.currency == "AUD"}selected="selected"{/if}>{__("currency_code_aud")}</option>
            <option value="CAD" {if $processor_params.currency == "CAD"}selected="selected"{/if}>{__("currency_code_cad")}</option>
            <option value="CHF" {if $processor_params.currency == "CHF"}selected="selected"{/if}>{__("currency_code_chf")}</option>
            <option value="CZK" {if $processor_params.currency == "CZK"}selected="selected"{/if}>{__("currency_code_czk")}</option>
            <option value="DKK" {if $processor_params.currency == "DKK"}selected="selected"{/if}>{__("currency_code_dkk")}</option>
            <option value="HKD" {if $processor_params.currency == "HKD"}selected="selected"{/if}>{__("currency_code_hkd")}</option>
            <option value="HUF" {if $processor_params.currency == "HUF"}selected="selected"{/if}>{__("currency_code_huf")}</option>
            <option value="JPY" {if $processor_params.currency == "JPY"}selected="selected"{/if}>{__("currency_code_jpy")}</option>
            <option value="KRW" {if $processor_params.currency == "KRW"}selected="selected"{/if}>{__("currency_code_krw")}</option>
            <option value="MYR" {if $processor_params.currency == "MYR"}selected="selected"{/if}>{__("currency_code_myr")}</option>
            <option value="NOK" {if $processor_params.currency == "NOK"}selected="selected"{/if}>{__("currency_code_nok")}</option>
            <option value="NZD" {if $processor_params.currency == "NZD"}selected="selected"{/if}>{__("currency_code_nzd")}</option>
            <option value="PLN" {if $processor_params.currency == "PLN"}selected="selected"{/if}>{__("currency_code_pln")}</option>
            <option value="SEK" {if $processor_params.currency == "SEK"}selected="selected"{/if}>{__("currency_code_sek")}</option>
            <option value="SGD" {if $processor_params.currency == "SGD"}selected="selected"{/if}>{__("currency_code_sgd")}</option>
            <option value="SKK" {if $processor_params.currency == "SKK"}selected="selected"{/if}>{__("currency_code_skk")}</option>
            <option value="THB" {if $processor_params.currency == "THB"}selected="selected"{/if}>{__("currency_code_thb")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="type">{__("type")}:</label>
     <div class="controls">
         <select name="payment_data[processor_params][authmode]" id="type">
            <option value="A" {if $processor_params.authmode == "A"}selected="selected"{/if}>{__("fullauth")}</option>
            <option value="E" {if $processor_params.authmode == "E"}selected="selected"{/if}>{__("preauth")}</option>
             </select>
     </div>
</div>