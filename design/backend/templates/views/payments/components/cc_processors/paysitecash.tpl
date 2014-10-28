{assign var="url" value="payment_notification.index_redirect?payment=paysitecash"|fn_url:"C":"http"}
{assign var="ref_url" value="payment_notification.index_redirect?payment=paysitecash"|fn_url:"C":"http"}
{assign var="sucess_url" value="payment_notification.process?payment=paysitecash"|fn_url:"C":"http"}
{assign var="cancel_url" value="payment_notification.cancel?payment=paysitecash"|fn_url:"C":"http"}
{assign var="confirm_url" value="http"|fn_payment_url:"paysitecash.php"}
<p>{__("text_paysitecash_notice", ["[url]" => $url, "[ref_url]" => $ref_url, "[sucess_url]" => $sucess_url, "[cancel_url]" => $cancel_url, "[confirm_url]" => $confirm_url])}</p>
<hr>
<div class="control-group">
    <label class="control-label" for="psc_processor">{__("text_paysitecash_processor")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][processor]" id="psc_processor">
            <option value="psc" {if $processor_params.processor == "psc"}selected="selected"{/if}>Paysite Cash</option>
            <option value="ep" {if $processor_params.processor == "ep"}selected="selected"{/if}>Easy Pay</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="psc_site_id">{__("text_paysitecash_site_id")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][site_id]" id="psc_site_id" value="{$processor_params.site_id}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="psc_currency">{__("currency")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][currency]" id="psc_currency">
            <option value="EUR" {if $processor_params.currency == "EUR"}selected="selected"{/if}>EUR</option>
            <option value="USD" {if $processor_params.currency == "USD"}selected="selected"{/if}>USD</option>
            <option value="CHF" {if $processor_params.currency == "CHF"}selected="selected"{/if}>CHF</option>
            <option value="CAD" {if $processor_params.currency == "CAD"}selected="selected"{/if}>CAD</option>
            <option value="GBP" {if $processor_params.currency == "GBP"}selected="selected"{/if}>GBP</option>
            <option value="LVL" {if $processor_params.currency == "LVL"}selected="selected"{/if}>LVL</option>
            <option value="LTL" {if $processor_params.currency == "LTL"}selected="selected"{/if}>LTL</option>
            <option value="RON" {if $processor_params.currency == "RON"}selected="selected"{/if}>RON</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="psc_mode">{__("text_paysitecash_mode")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][mode]" id="psc_mode">
            <option value="1"{if $processor_params.mode == 1} selected="selected"{/if}>{__("text_paysitecash_mode_test")}</option>
            <option value="0"{if $processor_params.mode == 0} selected="selected"{/if}>{__("text_paysitecash_mode_live")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="psc_debug">{__("text_paysitecash_debug")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][debug]" id="psc_debug">
            <option value="0"{if $processor_params.debug == 0} selected="selected"{/if}>{__("text_paysitecash_mode_debug_off")}</option>
            <option value="1"{if $processor_params.debug == 1} selected="selected"{/if}>{__("text_paysitecash_mode_debug_on")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="psc_nocurrencies">{__("text_paysitecash_nocurrencies")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][nocurrencies]" id="psc_nocurrencies">
            <option value="no"{if $processor_params.nocurrencies == "no"} selected="selected"{/if}>{__("text_paysitecash_nocurrencies_no")}</option>
            <option value="yes"{if $processor_params.nocurrencies == "yes"} selected="selected"{/if}>{__("text_paysitecash_nocurrencies_yes")}</option>
        </select>
    </div>
</div>
