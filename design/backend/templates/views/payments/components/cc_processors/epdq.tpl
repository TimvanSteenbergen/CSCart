{$url = fn_url("payment_notification.process&payment=epdq", "C")}
{__("payments.epdq.instructions", ["[url]" => $url])}
<hr />
<div class="control-group">
    <label class="control-label" for="epdq_mode">{__("test_live_mode")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][epdq_mode]" id="epdq_mode">
            <option value="test" {if $processor_params.epdq_mode == "test"}selected="selected"{/if}>{__("test")}</option>
            <option value="live" {if $processor_params.epdq_mode == "live"}selected="selected"{/if}>{__("live")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="epdq_pspid">{__("payments.epdq.pspid")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][epdq_pspid]" id="epdq_pspid" value="{$processor_params.epdq_pspid}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="epdq_passphrase">{__("passphrase")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][epdq_passphrase]" id="epdq_passphrase" value="{$processor_params.epdq_passphrase}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="epdq_operation">{__("transaction_type")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][epdq_operation]" id="epdq_operation">
            <option value="SAL" {if $processor_params.epdq_3dsecure == "SAl"}selected="selected"{/if}>{__("sale")}</option>
            <option value="RES" {if $processor_params.epdq_3dsecure == "REs"}selected="selected"{/if}>{__("authorize_only")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="epdq_3dsecure">{__("3d_secure")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][epdq_3dsecure]" id="epdq_3dsecure">
            <option value="none" {if $processor_params.epdq_3dsecure == "none"}selected="selected"{/if}>{__("none")}</option>
            <option value="MAINW" {if $processor_params.epdq_3dsecure == "MAINW"}selected="selected"{/if}>{__("epdq_3ds_main")}</option>
            <option value="POPUP" {if $processor_params.epdq_3dsecure == "POPUP"}selected="selected"{/if}>{__("epdq_3ds_popup")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="epdq_logo">{__("logo_link")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][epdq_logo]" id="epdq_logo" value="{$processor_params.epdq_logo}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="epdq_language">{__("language")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][epdq_language]" id="epdq_language">
            <option value="en_US" {if $processor_params.epdq_language == "en_US"}selected="selected"{/if}>{__("english")}</option>
            <option value="ar_AR" {if $processor_params.epdq_language == "ar_AR"}selected="selected"{/if}>{__("arabic")}</option>
            <option value="cs_CZ" {if $processor_params.epdq_language == "cs_CZ"}selected="selected"{/if}>{__("czech")}</option>
            <option value="dk_DK" {if $processor_params.epdq_language == "dk_DK"}selected="selected"{/if}>{__("danish")}</option>
            <option value="de_DE" {if $processor_params.epdq_language == "de_DE"}selected="selected"{/if}>{__("german")}</option>
            <option value="el_GR" {if $processor_params.epdq_language == "el_GR"}selected="selected"{/if}>{__("greek")}</option>
            <option value="es_ES" {if $processor_params.epdq_language == "es_ES"}selected="selected"{/if}>{__("spanish")}</option>
            <option value="fi_FI" {if $processor_params.epdq_language == "fi_FI"}selected="selected"{/if}>{__("finnish")}</option>
            <option value="fr_FR" {if $processor_params.epdq_language == "fr_FR"}selected="selected"{/if}>{__("french")}</option>
            <option value="he_IL" {if $processor_params.epdq_language == "he_IL"}selected="selected"{/if}>{__("hebrew")}</option>
            <option value="hu_HU" {if $processor_params.epdq_language == "hu_HU"}selected="selected"{/if}>{__("hungarian")}</option>
            <option value="it_IT" {if $processor_params.epdq_language == "it_IT"}selected="selected"{/if}>{__("italian")}</option>
            <option value="ja_JP" {if $processor_params.epdq_language == "ja_JP"}selected="selected"{/if}>{__("japanese")}</option>
            <option value="ko_KR" {if $processor_params.epdq_language == "ko_KR"}selected="selected"{/if}>{__("korean")}</option>
            <option value="nl_BE" {if $processor_params.epdq_language == "nl_BE"}selected="selected"{/if}>{__("flemish")}</option>
            <option value="nl_NL" {if $processor_params.epdq_language == "nl_NL"}selected="selected"{/if}>{__("dutch")}</option>
            <option value="no_NO" {if $processor_params.epdq_language == "no_NO"}selected="selected"{/if}>{__("norwegian")}</option>
            <option value="pl_PL" {if $processor_params.epdq_language == "pl_PL"}selected="selected"{/if}>{__("polish")}</option>
            <option value="pt_PT" {if $processor_params.epdq_language == "pt_PT"}selected="selected"{/if}>{__("portugese")}</option>
            <option value="ru_RU" {if $processor_params.epdq_language == "ru_RU"}selected="selected"{/if}>{__("russian")}</option>
            <option value="se_SE" {if $processor_params.epdq_language == "se_SE"}selected="selected"{/if}>{__("swedish")}</option>
            <option value="sk_SK" {if $processor_params.epdq_language == "sk_SK"}selected="selected"{/if}>{__("slovak")}</option>
            <option value="tr_TR" {if $processor_params.epdq_language == "tr_TR"}selected="selected"{/if}>{__("turkish")}</option>
            <option value="zh_CN" {if $processor_params.epdq_language == "zh_CN"}selected="selected"{/if}>{__("chinese")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="epdq_currency">{__("currency")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][epdq_currency]" id="epdq_currency">
            <option value="AED" {if $processor_params.epdq_currency == "AED"}selected="selected"{/if}>{__("currency_code_aed")}</option>
            <option value="ANG" {if $processor_params.epdq_currency == "ANG"}selected="selected"{/if}>{__("currency_code_ang")}</option>
            <option value="ARS" {if $processor_params.epdq_currency == "ARS"}selected="selected"{/if}>{__("currency_code_ars")}</option>
            <option value="AUD" {if $processor_params.epdq_currency == "AUD"}selected="selected"{/if}>{__("currency_code_aud")}</option>
            <option value="AWG" {if $processor_params.epdq_currency == "AWG"}selected="selected"{/if}>{__("currency_code_awg")}</option>
            <option value="BGN" {if $processor_params.epdq_currency == "BGN"}selected="selected"{/if}>{__("currency_code_bgn")}</option>
            <option value="BRL" {if $processor_params.epdq_currency == "BRL"}selected="selected"{/if}>{__("currency_code_brl")}</option>
            <option value="BYR" {if $processor_params.epdq_currency == "BYR"}selected="selected"{/if}>{__("currency_code_byr")}</option>
            <option value="CAD" {if $processor_params.epdq_currency == "CAD"}selected="selected"{/if}>{__("currency_code_cad")}</option>
            <option value="CHF" {if $processor_params.epdq_currency == "CHF"}selected="selected"{/if}>{__("currency_code_chf")}</option>
            <option value="CNY" {if $processor_params.epdq_currency == "CNY"}selected="selected"{/if}>{__("currency_code_cny")}</option>
            <option value="CZK" {if $processor_params.epdq_currency == "CZK"}selected="selected"{/if}>{__("currency_code_czk")}</option>
            <option value="DKK" {if $processor_params.epdq_currency == "DKK"}selected="selected"{/if}>{__("currency_code_dkk")}</option>
            <option value="EEK" {if $processor_params.epdq_currency == "EEK"}selected="selected"{/if}>{__("currency_code_eek")}</option>
            <option value="EGP" {if $processor_params.epdq_currency == "EGP"}selected="selected"{/if}>{__("currency_code_egp")}</option>
            <option value="EUR" {if $processor_params.epdq_currency == "EUR"}selected="selected"{/if}>{__("currency_code_eur")}</option>
            <option value="GBP" {if $processor_params.epdq_currency == "GBP"}selected="selected"{/if}>{__("currency_code_gbp")}</option>
            <option value="GEL" {if $processor_params.epdq_currency == "GEL"}selected="selected"{/if}>{__("currency_code_gel")}</option>
            <option value="HKD" {if $processor_params.epdq_currency == "HKD"}selected="selected"{/if}>{__("currency_code_hkd")}</option>
            <option value="HRK" {if $processor_params.epdq_currency == "HRK"}selected="selected"{/if}>{__("currency_code_hrk")}</option>
            <option value="HUF" {if $processor_params.epdq_currency == "HUF"}selected="selected"{/if}>{__("currency_code_huf")}</option>
            <option value="ILS" {if $processor_params.epdq_currency == "ILS"}selected="selected"{/if}>{__("currency_code_ils")}</option>
            <option value="ISK" {if $processor_params.epdq_currency == "ISK"}selected="selected"{/if}>{__("currency_code_isk")}</option>
            <option value="JPY" {if $processor_params.epdq_currency == "JPY"}selected="selected"{/if}>{__("currency_code_jpy")}</option>
            <option value="KRW" {if $processor_params.epdq_currency == "KRW"}selected="selected"{/if}>{__("currency_code_krw")}</option>
            <option value="LTL" {if $processor_params.epdq_currency == "LTL"}selected="selected"{/if}>{__("currency_code_ltl")}</option>
            <option value="LVL" {if $processor_params.epdq_currency == "LVL"}selected="selected"{/if}>{__("currency_code_lvl")}</option>
            <option value="MAD" {if $processor_params.epdq_currency == "MAD"}selected="selected"{/if}>{__("currency_code_mad")}</option>
            <option value="MXN" {if $processor_params.epdq_currency == "MXN"}selected="selected"{/if}>{__("currency_code_mxn")}</option>
            <option value="NOK" {if $processor_params.epdq_currency == "NOK"}selected="selected"{/if}>{__("currency_code_nok")}</option>
            <option value="NZD" {if $processor_params.epdq_currency == "NZD"}selected="selected"{/if}>{__("currency_code_nzd")}</option>
            <option value="PLN" {if $processor_params.epdq_currency == "PLN"}selected="selected"{/if}>{__("currency_code_pln")}</option>
            <option value="RON" {if $processor_params.epdq_currency == "RON"}selected="selected"{/if}>{__("currency_code_ron")}</option>
            <option value="RUB" {if $processor_params.epdq_currency == "RUB"}selected="selected"{/if}>{__("currency_code_rub")}</option>
            <option value="SEK" {if $processor_params.epdq_currency == "SEK"}selected="selected"{/if}>{__("currency_code_sek")}</option>
            <option value="SGD" {if $processor_params.epdq_currency == "SGD"}selected="selected"{/if}>{__("currency_code_sgd")}</option>
            <option value="SKK" {if $processor_params.epdq_currency == "SKK"}selected="selected"{/if}>{__("currency_code_skk")}</option>
            <option value="THB" {if $processor_params.epdq_currency == "THB"}selected="selected"{/if}>{__("currency_code_thb")}</option>
            <option value="TRY" {if $processor_params.epdq_currency == "TRY"}selected="selected"{/if}>{__("currency_code_try")}</option>
            <option value="UAH" {if $processor_params.epdq_currency == "UAH"}selected="selected"{/if}>{__("currency_code_uah")}</option>
            <option value="USD" {if $processor_params.epdq_currency == "USD"}selected="selected"{/if}>{__("currency_code_usd")}</option>
            <option value="XAF" {if $processor_params.epdq_currency == "XAF"}selected="selected"{/if}>{__("currency_code_xaf")}</option>
            <option value="XOF" {if $processor_params.epdq_currency == "XOF"}selected="selected"{/if}>{__("currency_code_xof")}</option>
            <option value="XPF" {if $processor_params.epdq_currency == "XPF"}selected="selected"{/if}>{__("currency_code_xpf")}</option>
            <option value="ZAR" {if $processor_params.epdq_currency == "ZAR"}selected="selected"{/if}>{__("currency_code_zar")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label cm-color" for="epdq_form_bgcolor">{__("payments.epdq.bgcolor")}:</label>
    <div class="controls">
        {include file="common/colorpicker.tpl" cp_name="payment_data[processor_params][epdq_form_bgcolor]" cp_id="epdq_form_bgcolor" cp_value=$processor_params.epdq_form_bgcolor}
    </div>
</div>

<div class="control-group">
    <label class="control-label cm-color" for="epdq_form_textcolor">{__("payments.epdq.textcolor")}:</label>
    <div class="controls">
        {include file="common/colorpicker.tpl" cp_name="payment_data[processor_params][epdq_form_textcolor]" cp_id="epdq_form_textcolor" cp_value=$processor_params.epdq_form_textcolor}
    </div>
</div>

<div class="control-group">
    <label class="control-label cm-color" for="epdq_form_tbl_bgcolor">{__("payments.epdq.tbl_bgcolor")}:</label>
    <div class="controls">
        {include file="common/colorpicker.tpl" cp_name="payment_data[processor_params][epdq_form_tbl_bgcolor]" cp_id="epdq_form_tbl_bgcolor" cp_value=$processor_params.epdq_form_tbl_bgcolor}
    </div>
</div>

<div class="control-group">
    <label class="control-label cm-color" for="epdq_form_tbl_textcolor">{__("payments.epdq.tbl_textcolor")}:</label>
    <div class="controls">
        {include file="common/colorpicker.tpl" cp_name="payment_data[processor_params][epdq_form_tbl_textcolor]" cp_id="epdq_form_tbl_textcolor" cp_value=$processor_params.epdq_form_tbl_textcolor}
    </div>
</div>

<div class="control-group">
    <label class="control-label cm-color" for="epdq_form_btn_bgcolor">{__("payments.epdq.btn_bgcolor")}:</label>
    <div class="controls">
        {include file="common/colorpicker.tpl" cp_name="payment_data[processor_params][epdq_form_btn_bgcolor]" cp_id="epdq_form_btn_bgcolor" cp_value=$processor_params.epdq_form_btn_bgcolor}
    </div>
</div>

<div class="control-group">
    <label class="control-label cm-color" for="epdq_form_btn_textcolor">{__("payments.epdq.btn_textcolor")}:</label>
    <div class="controls">
        {include file="common/colorpicker.tpl" cp_name="payment_data[processor_params][epdq_form_btn_textcolor]" cp_id="epdq_form_btn_textcolor" cp_value=$processor_params.epdq_form_btn_textcolor}
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="epdq_form_font_type">{__("payments.epdq.font_type")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][epdq_form_font_type]" id="epdq_form_font_type" value="{$processor_params.epdq_form_font_type}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="epdq_form_title">{__("payments.epdq.title")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][epdq_form_title]" id="epdq_form_title" value="{$processor_params.epdq_form_title}"  size="60">
    </div>
</div>
