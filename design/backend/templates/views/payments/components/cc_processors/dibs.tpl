<div class="control-group">
    <label class="control-label" for="merchant_id">{__("merchant_id")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][merchant]" id="merchant_id" value="{$processor_params.merchant}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="key1">{__("key1_for_md5")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][key1]" id="key1" value="{$processor_params.key1}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="key2">{__("key2_for_md5")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][key2]" id="key2" value="{$processor_params.key2}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="test">{__("test_live_mode")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][test]" id="test">
            <option value="test"{if $processor_params.test == 'test'} selected="selected"{/if}>{__("test")}</option>
            <option value="live"{if $processor_params.test == 'live'} selected="selected"{/if}>{__("live")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="currency">{__("currency")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][currency]" id="currency">
            <option value="208"{if $processor_params.currency == '208'} selected="selected"{/if}>{__("currency_code_dkk")}</option>
            <option value="978"{if $processor_params.currency == '978'} selected="selected"{/if}>{__("currency_code_eur")}</option>
            <option value="840"{if $processor_params.currency == '840'} selected="selected"{/if}>{__("currency_code_usd")}</option>
            <option value="826"{if $processor_params.currency == '826'} selected="selected"{/if}>{__("currency_code_gbp")}</option>
            <option value="752"{if $processor_params.currency == '752'} selected="selected"{/if}>{__("currency_code_sek")}</option>
            <option value="036"{if $processor_params.currency == '036'} selected="selected"{/if}>{__("currency_code_aud")}</option>
            <option value="124"{if $processor_params.currency == '124'} selected="selected"{/if}>{__("currency_code_cad")}</option>
            <option value="352"{if $processor_params.currency == '352'} selected="selected"{/if}>{__("currency_code_isk")}</option>
            <option value="392"{if $processor_params.currency == '392'} selected="selected"{/if}>{__("currency_code_jpy")}</option>
            <option value="554"{if $processor_params.currency == '554'} selected="selected"{/if}>{__("currency_code_nzd")}</option>
            <option value="578"{if $processor_params.currency == '578'} selected="selected"{/if}>{__("currency_code_nok")}</option>
            <option value="756"{if $processor_params.currency == '756'} selected="selected"{/if}>{__("currency_code_chf")}</option>
            <option value="949"{if $processor_params.currency == '949'} selected="selected"{/if}>{__("currency_code_try")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="lang">{__("default_language")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][lang]" id="lang">
            <option value="da"{if $processor_params.lang == 'da'} selected="selected"{/if}>{__("danish")}</option>
            <option value="sv"{if $processor_params.lang == 'sv'} selected="selected"{/if}>{__("swedish")}</option>
            <option value="no"{if $processor_params.lang == 'no'} selected="selected"{/if}>{__("norway")}</option>
            <option value="en"{if $processor_params.lang == 'en'} selected="selected"{/if}>{__("english")}</option>
            <option value="nl"{if $processor_params.lang == 'nl'} selected="selected"{/if}>{__("dutch")}</option>
            <option value="de"{if $processor_params.lang == 'de'} selected="selected"{/if}>{__("german")}</option>
            <option value="fr"{if $processor_params.lang == 'fr'} selected="selected"{/if}>{__("french")}</option>
            <option value="fi"{if $processor_params.lang == 'fi'} selected="selected"{/if}>{__("finnish")}</option>
            <option value="es"{if $processor_params.lang == 'es'} selected="selected"{/if}>{__("spanish")}</option>
            <option value="it"{if $processor_params.lang == 'it'} selected="selected"{/if}>{__("italian")}</option>
            <option value="fo"{if $processor_params.lang == 'fo'} selected="selected"{/if}>{__("faroese")}</option>
            <option value="pl"{if $processor_params.lang == 'pl'} selected="selected"{/if}>{__("polish")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="color">{__("color")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][color]" id="color">
            <option value="blue"{if $processor_params.color == 'blue'} selected="selected"{/if}>Blue</option>
            <option value="sand"{if $processor_params.color == 'sand'} selected="selected"{/if}>Sand</option>
            <option value="grey"{if $processor_params.color == 'grey'} selected="selected"{/if}>Grey</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="decorator">{__("decorator")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][decorator]" id="decorator">
            <option value="default"{if $processor_params.decorator == 'default'} selected="selected"{/if}>Default</option>
            <option value="basal"{if $processor_params.decorator == 'basal'} selected="selected"{/if}>Basal</option>
            <option value="rich"{if $processor_params.decorator == 'rich'} selected="selected"{/if}>Rich</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="skiplastpage">{__("skiplastpage")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][skiplastpage]" id="skiplastpage">
            <option value="yes"{if $processor_params.skiplastpage == 'yes'} selected="selected"{/if}>Yes</option>
            <option value="no"{if $processor_params.skiplastpage == 'no'} selected="selected"{/if}>No</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="order_prefix">{__("order_prefix")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][order_prefix]" id="order_prefix" value="{$processor_params.order_prefix}"  size="60">
    </div>
</div>