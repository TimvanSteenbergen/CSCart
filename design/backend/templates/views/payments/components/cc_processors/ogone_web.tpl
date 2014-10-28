{assign var="r_url" value="payment_notification.process?payment=ogone_web"|fn_url:'C':'http'}
<p>{__("text_ogoneweb_notice", ["[r_url]" => $r_url])}</p>
<hr>

<div class="control-group">
    <label class="control-label" for="pspid">{__("pspid")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][pspid]" id="pspid" size="60" value="{$processor_params.pspid}" >
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="sha_sign">{__("sha_sign")}:</label>
    <div class="controls">
        <input type="password" name="payment_data[processor_params][sha_sign]" id="sha_sign" size="60" value="{$processor_params.sha_sign}" >
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="use_new_sha_method">{__("use_new_sha_method")}:</label>
    <div class="controls"><input type="hidden" name="payment_data[processor_params][use_new_sha_method]" value="N">
        <input type="checkbox" name="payment_data[processor_params][use_new_sha_method]" id="use_new_sha_method" value="Y"{if $processor_params.use_new_sha_method eq Y} checked="checked"{/if}></div>
</div>

<div class="control-group">
    <label class="control-label" for="currency">{__("currency")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][currency]" id="currency">
            <option value="AUD"{if $processor_params.currency eq "AUD"} selected="selected"{/if}>{__("currency_code_aud")}</option>
            <option value="CAD"{if $processor_params.currency eq "CAD"} selected="selected"{/if}>{__("currency_code_cad")}</option>
            <option value="CHF"{if $processor_params.currency eq "CHF"} selected="selected"{/if}>{__("currency_code_chf")}</option>
            <option value="CZK"{if $processor_params.currency eq "CZK"} selected="selected"{/if}>{__("currency_code_czk")}</option>
            <option value="DKK"{if $processor_params.currency eq "DKK"} selected="selected"{/if}>{__("currency_code_dkk")}</option>
            <option value="EUR"{if $processor_params.currency eq "EUR"} selected="selected"{/if}>{__("currency_code_eur")}</option>
            <option value="FRF"{if $processor_params.currency eq "FRF"} selected="selected"{/if}>{__("currency_code_frf")}</option>
            <option value="GBP"{if $processor_params.currency eq "GBP"} selected="selected"{/if}>{__("currency_code_gbp")}</option>
            <option value="HKD"{if $processor_params.currency eq "HKD"} selected="selected"{/if}>{__("currency_code_hkd")}</option>
            <option value="HUF"{if $processor_params.currency eq "HUF"} selected="selected"{/if}>{__("currency_code_huf")}</option>
            <option value="ILS"{if $processor_params.currency eq "ILS"} selected="selected"{/if}>{__("currency_code_ils")}</option>
            <option value="JPY"{if $processor_params.currency eq "JPY"} selected="selected"{/if}>{__("currency_code_jpy")}</option>
            <option value="LTL"{if $processor_params.currency eq "LTL"} selected="selected"{/if}>{__("currency_code_ltl")}</option>
            <option value="LVL"{if $processor_params.currency eq "LVL"} selected="selected"{/if}>{__("currency_code_lvl")}</option>
            <option value="MXN"{if $processor_params.currency eq "MXN"} selected="selected"{/if}>{__("currency_code_mxn")}</option>
            <option value="NOK"{if $processor_params.currency eq "NOK"} selected="selected"{/if}>{__("currency_code_nok")}</option>
            <option value="NZD"{if $processor_params.currency eq "NZD"} selected="selected"{/if}>{__("currency_code_nzd")}</option>
            <option value="PLN"{if $processor_params.currency eq "PLN"} selected="selected"{/if}>{__("currency_code_pln")}</option>
            <option value="RUR"{if $processor_params.currency eq "RUR"} selected="selected"{/if}>{__("currency_code_rur")}</option>
            <option value="SEK"{if $processor_params.currency eq "SEK"} selected="selected"{/if}>{__("currency_code_sek")}</option>
            <option value="SGD"{if $processor_params.currency eq "SGD"} selected="selected"{/if}>{__("currency_code_sgd")}</option>
            <option value="SKK"{if $processor_params.currency eq "SKK"} selected="selected"{/if}>{__("currency_code_skk")}</option>
            <option value="THB"{if $processor_params.currency eq "THB"} selected="selected"{/if}>{__("currency_code_thb")}</option>
            <option value="TRY"{if $processor_params.currency eq "TRY"} selected="selected"{/if}>{__("currency_code_try")}</option>
            <option value="USD"{if $processor_params.currency eq "USD"} selected="selected"{/if}>{__("currency_code_usd")}</option>
            <option value="ZAR"{if $processor_params.currency eq "ZAR"} selected="selected"{/if}>{__("currency_code_zar")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="mode">{__("test_live_mode")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][mode]" id="mode">
            <option value="test"{if $processor_params.mode eq "test"} selected="selected"{/if}>{__("test")}
            <option value="live"{if $processor_params.mode eq "live"} selected="selected"{/if}>{__("live")}
        </select>
    </div>
</div>