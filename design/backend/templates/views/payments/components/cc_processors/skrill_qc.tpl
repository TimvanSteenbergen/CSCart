{$register_url = "http://www.skrill.com/partners/cscart/"}
{$pay_to_email = $processor_params.pay_to_email|escape:url}

<p>{__("text_skrill_notice", ["[register_url]" => $register_url])}</p>
<hr>

<input type="hidden" name="payment_data[processor_params][quick_checkout]" value="Y">

<div class="control-group">
    <label class="control-label" for="pay_to_email_{$payment_id}">{__("pay_to_email")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][pay_to_email]" id="pay_to_email_{$payment_id}" value="{$processor_params.pay_to_email}"  size="60" onchange="Tygh.$('#validate_email_{$payment_id}').prop('href', '{"payment_notification.validate_email?payment=skrill&payment_id=`$payment_id`&email="|fn_url nofilter}' +  Tygh.$(this).val()); Tygh.$('#validate_secret_word_{$payment_id}').prop('href', '{"payment_notification.validate_secret_word?payment=skrill&payment_id=`$payment_id`&email="|fn_url nofilter}' +  Tygh.$(this).val() + '&cust_id=' + Tygh.$('#customer_id_{$payment_id}').val() + '&secret=' + Tygh.$('#secret_word_{$payment_id}').val()); return false;">&nbsp;<a href="{"payment_notification.validate_email?payment=skrill&payment_id=`$payment_id`&email=`$processor_params.pay_to_email`"|fn_url}" onclick="Tygh.$.ceAjax('request', Tygh.$(this).prop('href'), {ldelim}method: 'GET', callback: fn_get_validate_email_{$payment_id}{rdelim}); return false;" id="validate_email_{$payment_id}">{__("validate_email")}</a>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="customer_id_{$payment_id}">{__("skrill_customer_id")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][customer_id]" id="customer_id_{$payment_id}" value="{$processor_params.customer_id}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="merchant_firstname_{$payment_id}">{__("merchant_firstname")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][merchant_firstname]" id="merchant_firstname_{$payment_id}" value="{$processor_params.merchant_firstname}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="merchant_lastname_{$payment_id}">{__("merchant_lastname")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][merchant_lastname]" id="merchant_lastname_{$payment_id}" value="{$processor_params.merchant_lastname}"  size="60">
    </div>
</div>

<script type="text/javascript" language="javascript 1.2">
function fn_get_validate_email_{$payment_id}(data)
{ldelim}
    Tygh.$('#customer_id_{$payment_id}').val( data.customer_id_{$payment_id} );
{rdelim}
</script>


<p>
    <a onclick="Tygh.$.ceAjax('request', '{"payment_notification.activate?payment=skrill&payment_id=`$payment_id`&email="|fn_url nofilter}' + Tygh.$('#pay_to_email_{$payment_id}').val() + '&cust_id=' + Tygh.$('#customer_id_{$payment_id}').val() + '&platform=21477207' + '&merchant_firstname=' + Tygh.$('#merchant_firstname_{$payment_id}').val() + '&merchant_lastname=' + Tygh.$('#merchant_lastname_{$payment_id}').val(), {ldelim}method: 'GET'{rdelim}); return false;" href="{"payment_notification.activate?payment=skrill&payment_id=`$payment_id`&email=`$pay_to_email`&cust_id=`$processor_params.customer_id`&platform=21477207"|fn_url}">{__("activate_skrill_merchant_tools")}</a>
</p>
<p>
{__("text_skrill_activate_quick_checkout_short_explanation")}
</p>
<p>
{__("text_skrill_activate_quick_checkout_short_explanation_2")}
</p>
<br>
<div class="control-group">
    <label class="control-label" for="secret_word_{$payment_id}">{__("secret_word")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][secret_word]" id="secret_word_{$payment_id}" value="{$processor_params.secret_word}"  size="60" onchange="Tygh.$('#validate_secret_word_{$payment_id}').prop( 'href', '{"payment_notification.validate_secret_word?payment=skrill&payment_id=`$payment_id`&email="|fn_url nofilter}' + Tygh.$('#pay_to_email_{$payment_id}').val() + '&cust_id=' + Tygh.$('#customer_id_{$payment_id}').val() + '&secret=' + Tygh.$(this).val()); return false;">&nbsp;<a href="{"payment_notification.validate_secret_word?payment=skrill&payment_id=`$payment_id`&email=`$pay_to_email`&cust_id=`$processor_params.customer_id`&secret=`$processor_params.secret_word`"|fn_url}" onclick="Tygh.$.ceAjax('request', Tygh.$(this).prop('href'), {ldelim}method: 'GET', callback: fn_get_validate_secret_word_{$payment_id}{rdelim}); return false;" id="validate_secret_word_{$payment_id}">{__("validate_secret_word")}</a>
        
        <script type="text/javascript" language="javascript 1.2">
        function fn_get_validate_secret_word_{$payment_id}(data)
        {ldelim}
            Tygh.$('#secret_word_{$payment_id}').val(data.secret_word_{$payment_id});
        {rdelim}
        </script>
        <p><small>{__("text_skrill_secred_word_notice")}</small></p>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="recipient_description_{$payment_id}">{__("recipient_description")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][recipient_description]" id="recipient_description_{$payment_id}" value="{$processor_params.recipient_description|default:$settings.Company.company_name}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="language_{$payment_id}">{__("language")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][language]" id="language_{$payment_id}">
            <option value="EN" {if $processor_params.language == 'EN'}selected="selected"{/if}>{__("english")}</option>
            <option value="DE" {if $processor_params.language == 'DE'}selected="selected"{/if}>{__("german")}</option>
            <option value="ES" {if $processor_params.language == 'ES'}selected="selected"{/if}>{__("spanish")}</option>
            <option value="FR" {if $processor_params.language == 'FR'}selected="selected"{/if}>{__("french")}</option>
            <option value="IT" {if $processor_params.language == 'IT'}selected="selected"{/if}>{__("italian")}</option>
        
            <option value="PL" {if $processor_params.language == 'PL'}selected="selected"{/if}>{__("polish")}</option>
            <option value="GR" {if $processor_params.language == 'GR'}selected="selected"{/if}>{__("greek")}</option>
            <option value="RO" {if $processor_params.language == 'RO'}selected="selected"{/if}>{__("romanian")}</option>
            <option value="RU" {if $processor_params.language == 'RU'}selected="selected"{/if}>{__("russian")}</option>
            <option value="TR" {if $processor_params.language == 'TR'}selected="selected"{/if}>{__("turkish")}</option>
            <option value="CN" {if $processor_params.language == 'CN'}selected="selected"{/if}>{__("chinese")}</option>
            <option value="CZ" {if $processor_params.language == 'CZ'}selected="selected"{/if}>{__("czech")}</option>
            <option value="NL" {if $processor_params.language == 'NL'}selected="selected"{/if}>{__("dutch")}</option>
            <option value="DA" {if $processor_params.language == 'DA'}selected="selected"{/if}>{__("danish")}</option>
            <option value="SV" {if $processor_params.language == 'SV'}selected="selected"{/if}>{__("swedish")}</option>
            <option value="FI" {if $processor_params.language == 'FI'}selected="selected"{/if}>{__("finnish")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="currency_{$payment_id}">{__("currency")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][currency]" id="currency_{$payment_id}">
            <option value="EUR"{if $processor_params.currency eq "EUR"} selected="selected"{/if}>{__("currency_code_eur")}</option>
            <option value="USD"{if $processor_params.currency eq "USD"} selected="selected"{/if}>{__("currency_code_usd")}</option>
            <option value="GBP"{if $processor_params.currency eq "GBP"} selected="selected"{/if}>{__("currency_code_gbp")}</option>
            <option value="HKD"{if $processor_params.currency eq "HKD"} selected="selected"{/if}>{__("currency_code_hkd")}</option>
            <option value="SGD"{if $processor_params.currency eq "SGD"} selected="selected"{/if}>{__("currency_code_sgd")}</option>
            <option value="JPY"{if $processor_params.currency eq "JPY"} selected="selected"{/if}>{__("currency_code_jpy")}</option>
            <option value="CAD"{if $processor_params.currency eq "CAD"} selected="selected"{/if}>{__("currency_code_cad")}</option>
            <option value="AUD"{if $processor_params.currency eq "AUD"} selected="selected"{/if}>{__("currency_code_aud")}</option>
            <option value="CHF"{if $processor_params.currency eq "CHF"} selected="selected"{/if}>{__("currency_code_chf")}</option>
            <option value="DKK"{if $processor_params.currency eq "DKK"} selected="selected"{/if}>{__("currency_code_dkk")}</option>
            <option value="SEK"{if $processor_params.currency eq "SEK"} selected="selected"{/if}>{__("currency_code_sek")}</option>
            <option value="NOK"{if $processor_params.currency eq "NOK"} selected="selected"{/if}>{__("currency_code_nok")}</option>
            <option value="ILS"{if $processor_params.currency eq "ILS"} selected="selected"{/if}>{__("currency_code_ils")}</option>
            <option value="MYR"{if $processor_params.currency eq "MYR"} selected="selected"{/if}>Malaysian Ringgit</option>
            <option value="NZD"{if $processor_params.currency eq "NZD"} selected="selected"{/if}>{__("currency_code_nzd")}</option>
            <option value="TRY"{if $processor_params.currency eq "TRY"} selected="selected"{/if}>{__("currency_code_try")}</option>
            <option value="TWD"{if $processor_params.currency eq "TWD"} selected="selected"{/if}>Taiwan Dollar</option>
            <option value="THB"{if $processor_params.currency eq "THB"} selected="selected"{/if}>{__("currency_code_thb")}</option>
            <option value="CZK"{if $processor_params.currency eq "CZK"} selected="selected"{/if}>{__("currency_code_czk")}</option>
            <option value="HUF"{if $processor_params.currency eq "HUF"} selected="selected"{/if}>{__("currency_code_huf")}</option>
            <option value="SKK"{if $processor_params.currency eq "SKK"} selected="selected"{/if}>{__("currency_code_skk")}</option>
            <option value="EEK"{if $processor_params.currency eq "EEK"} selected="selected"{/if}>Estonian Kroon</option>
            <option value="BGN"{if $processor_params.currency eq "BGN"} selected="selected"{/if}>Bulgarian Leva</option>
            <option value="PLN"{if $processor_params.currency eq "PLN"} selected="selected"{/if}>{__("currency_code_pln")}</option>
            <option value="ISK"{if $processor_params.currency eq "ISK"} selected="selected"{/if}>Iceland Krona</option>
            <option value="INR"{if $processor_params.currency eq "INR"} selected="selected"{/if}>Indian Rupee</option>
            <option value="LVL"{if $processor_params.currency eq "LVL"} selected="selected"{/if}>{__("currency_code_lvl")}</option>
            <option value="KRW"{if $processor_params.currency eq "KRW"} selected="selected"{/if}>{__("currency_code_krw")}</option>
            <option value="ZAR"{if $processor_params.currency eq "ZAR"} selected="selected"{/if}>{__("currency_code_zar")}</option>
            <option value="RON"{if $processor_params.currency eq "RON"} selected="selected"{/if}>Romanian Leu New</option>
            <option value="HRK"{if $processor_params.currency eq "HRK"} selected="selected"{/if}>Croatian Kuna</option>
            <option value="LTL"{if $processor_params.currency eq "LTL"} selected="selected"{/if}>{__("currency_code_ltl")}</option>
        </select>
        {assign var="cur_man" value="currencies.manage"|fn_url}
        <p><small>{__("text_skrill_currs_notice", ["[link]" => $cur_man])}</small></p>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="order_prefix_{$payment_id}">{__("order_prefix")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][order_prefix]" id="order_prefix_{$payment_id}" value="{$processor_params.order_prefix}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="payment_methods_{$payment_id}">{__("payment_methods")}:</label>
    <div class="controls"><input type="hidden" name="payment_data[processor_params][payment_methods]" value="{$processor_params.payment_methods}" id="txtpm_{$payment_id}">
        <select name="payment_data[processor_params][_payment_methods]" size="10" multiple="multiple" id="pm_{$payment_id}" onchange="fn_get_selected_values('pm_{$payment_id}', 'txtpm_{$payment_id}');">
            <option value="IDL">Ideal</option>
            <option value="PWY">Przelewy24</option>
            <option value="PWY5">ING Bank Śląski</option>
            <option value="PWY6">PKO BP (PKO Inteligo)</option>
            <option value="PWY7">Multibank (Multitransfer)</option>
            <option value="PWY14">Lukas Bank</option>
            <option value="PWY15">Bank BPH</option>
            <option value="PWY37">Kredyt Bank</option>
            <option value="PWY17">InvestBank</option>
            <option value="PWY18">PeKaO S.A.</option>
            <option value="PWY19">Citibank handlowy</option>
            <option value="PWY20">Bank Zachodni WBK (Przelew24)</option>
            <option value="PWY21">BGŻ</option>
            <option value="PWY22">Millenium</option>
            <option value="PWY26">Płacę z Inteligo</option>
            <option value="PWY25">mBank (mTransfer)</option>
            <option value="PWY28">Bank Ochrony Środowiska</option>
            <option value="PWY32">Nordea</option>
            <option value="PWY33">Fortis Bank</option>
            <option value="PWY36">Deutsche Bank PBC S.A,.</option>
            <option value="VSA">VISA</option>
            <option value="MSC">MASTERCARD</option>
            <option value="VSD">DELTA / VISA DEBIT</option>
            <option value="VSE">VISA ELECTRON</option>
            <option value="AMX">AMERICAN EXPRESS</option>
            <option value="DIN">DINERS</option>
            <option value="JCB">JCB</option>
            <option value="MAE">MAESTRO</option>
            <option value="LSR">LASER</option>
            <option value="SLO">SOLO</option>
            <option value="GCB">Carte Bleue</option>
            <option value="SFT">Sofortueberweisung</option>
            <option value="DID">direct debit</option>
            <option value="GIR">Giropay</option>
            <option value="ENT">Enets</option>
            <option value="EBT">Solo sweden</option>
            <option value="SO2">Solo finland</option>
            <option value="NPY">eps (NetPay)</option>
            <option value="PLI">POLi</option>
            <option value="DNK">Dankort</option>
            <option value="CSI">CartaSi</option>
            <option value="PSP">Postepay</option>
            <option value="EPY">ePay Bulgaria</option>
            <option value="BWI">BWI</option>
            <option value="OBT">Online Bank Transfer</option>
        </select>
        <p><small>{__("multiple_selectbox_notice")}</small></p></div>
</div>

<script type="text/javascript" language="javascript 1.2">

{literal}
function fn_get_selected_values(id, txtid)
{
    var txtSelectedValuesObj = document.getElementById(txtid);
    var selectedArray = new Array();
    var selObj = document.getElementById(id);
    var i;
    var count = 0;
    for (i = 0; i < selObj.options.length; i++) {
        if (selObj.options[i].selected) {
            selectedArray[count] = selObj.options[i].value;
            count++;
        }
    }
    txtSelectedValuesObj.value = selectedArray;
}

function fn_set_selected_values(id, txtid)
{
    var txtSelectedValuesObj = document.getElementById(txtid);
    var pm_str = txtSelectedValuesObj.value;
    pm_array = pm_str.split(',');
    var selectedArray = new Array();
    var selObj = document.getElementById(id);
    var i;
    var count = 0;
    for (i = 0; i < selObj.options.length; i++) {
        if (in_array(selObj.options[i].value, pm_array)) {
            selObj.options[i].selected = true;
        }
    }
}

function fn_set_all_values (id, txtid)
{
    var txtSelectedValuesObj = document.getElementById(txtid);
    var pm_str = txtSelectedValuesObj.value;
    pm_array = ['VSA', 'MSC', 'VSD', 'VSE', 'MAE', 'SLO', 'AMX', 'DIN', 'JCB', 'LSR', 'GCB', 'DNK', 'PSP', 'CSI'];
    var selectedArray = new Array();
    var selObj = document.getElementById(id);
    var i;
    var count = 0;
    for (i = 0; i < selObj.options.length; i++) {
        if (in_array(selObj.options[i].value, pm_array)) {
            selObj.options[i].selected = true;
        }
    }
}

function in_array(what, where) {
    var a = false;
    for (var i = 0; i < where.length; i++) {
        if (what == where[i]) {
            a = true;
            break;
        }
    }
    return a;
}
{/literal}

Tygh.$(document).ready(function() {$ldelim}
    {if $processor_params && $processor_params|is_array}
    fn_set_selected_values ('pm_{$payment_id}', 'txtpm_{$payment_id}');
    {else}
    fn_set_all_values ('pm_{$payment_id}', 'txtpm_{$payment_id}');
    {/if}
    fn_get_selected_values ('pm_{$payment_id}', 'txtpm_{$payment_id}')
{$rdelim});
</script>

<div class="control-group">
    <label class="control-label" for="do_not_pass_logo">{__("do_not_pass_logo")}:</label>
    <div class="controls">
        <input type="hidden" name="payment_data[processor_params][do_not_pass_logo]" value="N">
        <input type="checkbox" name="payment_data[processor_params][do_not_pass_logo]" value="Y" id="do_not_pass_logo" class="checkbox" {if $processor_params.do_not_pass_logo == "Y"}checked="checked"{/if}>
        <p><small>{__("text_skrill_logo_notice")}</small></p>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="iframe_mode_{$payment_id}">{__("iframe_mode")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][iframe_mode]" id="iframe_mode_{$payment_id}">
            <option value="N" {if $processor_params.iframe_mode == 'N'}selected="selected"{/if}>{__("disabled")}</option>
            <option value="Y" {if $processor_params.iframe_mode == 'Y'}selected="selected"{/if}>{__("enabled")}</option>
        </select>
    </div>
</div>

<p>{__("text_skrill_support")}</p>