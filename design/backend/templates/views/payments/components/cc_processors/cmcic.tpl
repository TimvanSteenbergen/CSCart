{assign var="r_url" value="http"|fn_payment_url:"cmcic.php"}
<p>{__("text_cmcic_notice", ["[postback_url]" => "<span>`$r_url`</span>"])}</p>
<hr>

<div class="control-group">
    <label class="control-label" for="merchant_id">{__("merchant_id")} ({__("tpe")}):</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][merchant_id]" id="merchant_id" value="{$processor_params.merchant_id}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="key">{__("key")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][key]" id="key" value="{$processor_params.key}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="societe">{__("cmcic_societe")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][societe]" id="societe" value="{$processor_params.societe}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="bank">{__("bank")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][bank]" id="bank">
            <option value="CM" {if $processor_params.bank == "CM"}selected="selected"{/if}>{__("bank_cm")}</option>
            <option value="CIC" {if $processor_params.bank == "CIC"}selected="selected"{/if}>{__("bank_cic")}</option>
            <option value="OBS" {if $processor_params.bank == "OBS"}selected="selected"{/if}>{__("bank_obc")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="payment_desc">{__("payment_details")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][payment_desc]" id="payment_desc" value="{$processor_params.payment_desc}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="language">{__("language")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][language]" id="language">
            <option value="FR" {if $processor_params.language == "FR"}selected="selected"{/if}>{__("french")}</option>
            <option value="en" {if $processor_params.language == "en"}selected="selected"{/if}>{__("english")}</option>
            <option value="IT" {if $processor_params.language == "IS"}selected="selected"{/if}>{__("italian")}</option>
            <option value="ES" {if $processor_params.language == "ES"}selected="selected"{/if}>{__("spanish")}</option>
            <option value="DE" {if $processor_params.language == "DE"}selected="selected"{/if}>{__("german")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="currency">{__("currency")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][currency]" id="currency">
            <option value="EUR" {if $processor_params.currency == "EUR"}selected="selected"{/if}>{__("currency_code_eur")}</option>
            <option value="USD" {if $processor_params.currency == "USD"}selected="selected"{/if}>{__("currency_code_usd")}</option>
            <option value="GBP" {if $processor_params.currency == "GBP"}selected="selected"{/if}>{__("currency_code_gbp")}</option>
            <option value="CHF" {if $processor_params.currency == "CHF"}selected="selected"{/if}>{__("currency_code_chf")}</option>
            <option value="NOK" {if $processor_params.currency == "NOK"}selected="selected"{/if}>{__("currency_code_nok")}</option>
            <option value="SEK" {if $processor_params.currency == "SEK"}selected="selected"{/if}>{__("currency_code_sek")}</option>
            <option value="DKK" {if $processor_params.currency == "DKK"}selected="selected"{/if}>{__("currency_code_dkk")}</option>
            <option value="AUD" {if $processor_params.currency == "AUD"}selected="selected"{/if}>{__("currency_code_aud")}</option>
            <option value="CAD" {if $processor_params.currency == "CAD"}selected="selected"{/if}>{__("currency_code_cad")}</option>
        </select>
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