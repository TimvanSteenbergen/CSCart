<div class="control-group">
    <label class="control-label" for="account">{__("account")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][account]" id="account" value="{$processor_params.account}" >
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="item_name">{__("paypal_item_name")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][item_name]" id="item_name" value="{$processor_params.item_name}" >
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="currency">{__("currency")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][currency]" id="currency">
            <option value="CAD" {if $processor_params.currency == "CAD"}selected="selected"{/if}>{__("currency_code_cad")}</option>
            <option value="EUR" {if $processor_params.currency == "EUR"}selected="selected"{/if}>{__("currency_code_eur")}</option>
            <option value="GBP" {if $processor_params.currency == "GBP"}selected="selected"{/if}>{__("currency_code_gbp")}</option>
            <option value="USD" {if $processor_params.currency == "USD"}selected="selected"{/if}>{__("currency_code_usd")}</option>
            <option value="JPY" {if $processor_params.currency == "JPY"}selected="selected"{/if}>{__("currency_code_jpy")}</option>
            <option value="RUB" {if $processor_params.currency == "RUB"}selected="selected"{/if}>{__("currency_code_rur")}</option>
            <option value="AUD" {if $processor_params.currency == "AUD"}selected="selected"{/if}>{__("currency_code_aud")}</option>
            <option value="NZD" {if $processor_params.currency == "NZD"}selected="selected"{/if}>{__("currency_code_nzd")}</option>
            <option value="CHF" {if $processor_params.currency == "CHF"}selected="selected"{/if}>{__("currency_code_chf")}</option>
            <option value="HKD" {if $processor_params.currency == "HKD"}selected="selected"{/if}>{__("currency_code_hkd")}</option>
            <option value="SGD" {if $processor_params.currency == "SGD"}selected="selected"{/if}>{__("currency_code_sgd")}</option>
            <option value="SEK" {if $processor_params.currency == "SEK"}selected="selected"{/if}>{__("currency_code_sek")}</option>
            <option value="DKK" {if $processor_params.currency == "DKK"}selected="selected"{/if}>{__("currency_code_dkk")}</option>
            <option value="PLN" {if $processor_params.currency == "PLN"}selected="selected"{/if}>{__("currency_code_pln")}</option>
            <option value="NOK" {if $processor_params.currency == "NOK"}selected="selected"{/if}>{__("currency_code_nok")}</option>
            <option value="HUF" {if $processor_params.currency == "HUF"}selected="selected"{/if}>{__("currency_code_huf")}</option>
            <option value="CZK" {if $processor_params.currency == "CZK"}selected="selected"{/if}>{__("currency_code_czk")}</option>
            <option value="ILS" {if $processor_params.currency == "ILS"}selected="selected"{/if}>{__("currency_code_ils")}</option>
            <option value="MXN" {if $processor_params.currency == "MXN"}selected="selected"{/if}>{__("currency_code_mxn")}</option>
            <option value="BRL" {if $processor_params.currency == "BRL"}selected="selected"{/if}>{__("currency_code_brl")}</option>
            <option value="MYR" {if $processor_params.currency == "MYR"}selected="selected"{/if}>{__("currency_code_myr")}</option>
            <option value="PHP" {if $processor_params.currency == "PHP"}selected="selected"{/if}>{__("currency_code_php")}</option>
            <option value="TWD" {if $processor_params.currency == "TWD"}selected="selected"{/if}>{__("currency_code_twd")}</option>
            <option value="THB" {if $processor_params.currency == "THB"}selected="selected"{/if}>{__("currency_code_thb")}</option>
            <option value="TRY" {if $processor_params.currency == "TRY"}selected="selected"{/if}>{__("currency_code_try")}</option>
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

<div class="control-group">
    <label class="control-label" for="order_prefix">{__("order_prefix")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][order_prefix]" id="order_prefix" value="{$processor_params.order_prefix}" >
    </div>
</div>

<p>
{__("see_demo")}: <a href="http://www.paypal-marketing.com/html/partner/portal/standard.html">http://www.paypal-marketing.com/html/partner/portal/standard.html</a>
</p>

{include file="common/subheader.tpl" title=__("text_paypal_status_map") target="#text_paypal_status_map"}

<div id="text_paypal_status_map" class="in collapse">
    {assign var="statuses" value=$smarty.const.STATUSES_ORDER|fn_get_simple_statuses}
    
    <div class="control-group">
        <label class="control-label" for="elm_paypal_refunded">{__("refunded")}:</label>
        <div class="controls">
            <select name="payment_data[processor_params][statuses][refunded]" id="elm_paypal_refunded">
                {foreach from=$statuses item="s" key="k"}
                <option value="{$k}" {if (isset($processor_params.statuses.refunded) && $processor_params.statuses.refunded == $k) || (!isset($processor_params.statuses.refunded) && $k == 'I')}selected="selected"{/if}>{$s}</option>
                {/foreach}
            </select>
        </div>
    </div>
    
    <div class="control-group">
        <label class="control-label" for="elm_paypal_completed">{__("completed")}:</label>
        <div class="controls">
            <select name="payment_data[processor_params][statuses][completed]" id="elm_paypal_completed">
                {foreach from=$statuses item="s" key="k"}
                <option value="{$k}" {if (isset($processor_params.statuses.completed) && $processor_params.statuses.completed == $k) || (!isset($processor_params.statuses.completed) && $k == 'P')}selected="selected"{/if}>{$s}</option>
                {/foreach}
            </select>
        </div>
    </div>
    
    <div class="control-group">
        <label class="control-label" for="elm_paypal_pending">{__("pending")}:</label>
        <div class="controls">
            <select name="payment_data[processor_params][statuses][pending]" id="elm_paypal_pending">
                {foreach from=$statuses item="s" key="k"}
                <option value="{$k}" {if (isset($processor_params.statuses.pending) && $processor_params.statuses.pending == $k) || (!isset($processor_params.statuses.pending) && $k == 'O')}selected="selected"{/if}>{$s}</option>
                {/foreach}
            </select>
        </div>
    </div>
    
    <div class="control-group">
        <label class="control-label" for="elm_paypal_canceled_reversal">{__("canceled_reversal")}:</label>
        <div class="controls">
            <select name="payment_data[processor_params][statuses][canceled_reversal]" id="elm_paypal_canceled_reversal">
                {foreach from=$statuses item="s" key="k"}
                <option value="{$k}" {if (isset($processor_params.statuses.canceled_reversal) && $processor_params.statuses.canceled_reversal == $k) || (!isset($processor_params.statuses.canceled_reversal) && $k == 'I')}selected="selected"{/if}>{$s}</option>
                {/foreach}
            </select>
        </div>
    </div>
    
    <div class="control-group">
        <label class="control-label" for="elm_paypal_created">{__("created")}:</label>
        <div class="controls">
            <select name="payment_data[processor_params][statuses][created]" id="elm_paypal_created">
                {foreach from=$statuses item="s" key="k"}
                <option value="{$k}" {if (isset($processor_params.statuses.created) && $processor_params.statuses.created == $k) || (!isset($processor_params.statuses.created) && $k == 'O')}selected="selected"{/if}>{$s}</option>
                {/foreach}
            </select>
        </div>
    </div>
    
    <div class="control-group">
        <label class="control-label" for="elm_paypal_denied">{__("denied")}:</label>
        <div class="controls">
            <select name="payment_data[processor_params][statuses][denied]" id="elm_paypal_denied">
                {foreach from=$statuses item="s" key="k"}
                <option value="{$k}" {if (isset($processor_params.statuses.denied) && $processor_params.statuses.denied == $k) || (!isset($processor_params.statuses.denied) && $k == 'I')}selected="selected"{/if}>{$s}</option>
                {/foreach}
            </select>
        </div>
    </div>
    
    <div class="control-group">
        <label class="control-label" for="elm_paypal_expired">{__("expired")}:</label>
        <div class="controls">
            <select name="payment_data[processor_params][statuses][expired]" id="elm_paypal_expired">
                {foreach from=$statuses item="s" key="k"}
                <option value="{$k}" {if (isset($processor_params.statuses.expired) && $processor_params.statuses.expired == $k) || (!isset($processor_params.statuses.expired) && $k == 'F')}selected="selected"{/if}>{$s}</option>
                {/foreach}
            </select>
        </div>
    </div>
    
    <div class="control-group">
        <label class="control-label" for="elm_paypal_reversed">{__("reversed")}:</label>
        <div class="controls">
            <select name="payment_data[processor_params][statuses][reversed]" id="elm_paypal_reversed">
                {foreach from=$statuses item="s" key="k"}
                <option value="{$k}" {if (isset($processor_params.statuses.reversed) && $processor_params.statuses.reversed == $k) || (!isset($processor_params.statuses.reversed) && $k == 'I')}selected="selected"{/if}>{$s}</option>
                {/foreach}
            </select>
        </div>
    </div>
    
    <div class="control-group">
        <label class="control-label" for="elm_paypal_processed">{__("processed")}:</label>
        <div class="controls">
            <select name="payment_data[processor_params][statuses][processed]" id="elm_paypal_processed">
                {foreach from=$statuses item="s" key="k"}
                <option value="{$k}" {if (isset($processor_params.statuses.processed) && $processor_params.statuses.processed == $k) || (!isset($processor_params.statuses.processed) && $k == 'P')}selected="selected"{/if}>{$s}</option>
                {/foreach}
            </select>
        </div>
    </div>
    
    <div class="control-group">
        <label class="control-label" for="elm_paypal_voided">{__("voided")}:</label>
        <div class="controls">
            <select name="payment_data[processor_params][statuses][voided]" id="elm_paypal_voided">
                {foreach from=$statuses item="s" key="k"}
                <option value="{$k}" {if (isset($processor_params.statuses.voided) && $processor_params.statuses.voided == $k) || (!isset($processor_params.statuses.voided) && $k == 'O')}selected="selected"{/if}>{$s}</option>
                {/foreach}
            </select>
        </div>
    </div>
</div>
