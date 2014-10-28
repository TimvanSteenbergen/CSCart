<p>{__("processor_description_cardsave")}</p>
<hr/>

<div class="control-group">
    <label class="control-label" for="merchant_id">{__("merchant_id")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][merchant_id]" id="merchant_id" value="{$processor_params.merchant_id}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="access_code">{__("preshared_key")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][access_code]" id="access_code" value="{$processor_params.access_code}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="password">{__("password")}:</label>
    <div class="controls">
        <input type="password" name="payment_data[processor_params][password]" id="password" value="{$processor_params.password}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="transaction_type">{__("transaction_type")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][transaction_type]" id="transaction_type">
            <option value="SALE" {if $processor_params.transaction_type == "SALE"}selected="selected"{/if}>{__("sale")}</option>
            <option value="PREAUTH" {if $processor_params.transaction_type == "PREAUTH"}selected="selected"{/if}>{__("preauth")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="currency">{__("currency")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][currency]" id="currency">
            <option value="978"{if $processor_params.currency == '978'} selected="selected"{/if}>{__("currency_code_eur")}</option>
            <option value="840"{if $processor_params.currency == '840'} selected="selected"{/if}>{__("currency_code_usd")}</option>
            <option value="826"{if $processor_params.currency == '826'} selected="selected"{/if}>{__("currency_code_gbp")}</option>
        </select>
    </div>
</div>

<hr/>

<div class="control-group">
    <label class="control-label" for="transaction_type">{__("cvv2")}&nbsp;{__("mandatory")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][cv2_mandatory]" id="cv2_mandatory">
            <option value="true" {if $processor_params.cv2_mandatory == "true"}selected="selected"{/if}>{__("yes")}</option>
            <option value="false" {if $processor_params.cv2_mandatory == "false"}selected="selected"{/if}>{__("no")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="transaction_type">{__("country")}&nbsp;{__("mandatory")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][country_mandatory]" id="country_mandatory">
            <option value="true" {if $processor_params.country_mandatory == "true"}selected="selected"{/if}>{__("yes")}</option>
            <option value="false" {if $processor_params.country_mandatory == "false"}selected="selected"{/if}>{__("no")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="transaction_type">{__("state")}&nbsp;{__("mandatory")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][state_mandatory]" id="state_mandatory">
            <option value="true" {if $processor_params.state_mandatory == "true"}selected="selected"{/if}>{__("yes")}</option>
            <option value="false" {if $processor_params.state_mandatory == "false"}selected="selected"{/if}>{__("no")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="transaction_type">{__("city")}&nbsp;{__("mandatory")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][city_mandatory]" id="city_mandatory">
            <option value="true" {if $processor_params.city_mandatory == "true"}selected="selected"{/if}>{__("yes")}</option>
            <option value="false" {if $processor_params.city_mandatory == "false"}selected="selected"{/if}>{__("no")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="transaction_type">{__("address")}&nbsp;{__("mandatory")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][address_mandatory]" id="address_mandatory">
            <option value="true" {if $processor_params.address_mandatory == "true"}selected="selected"{/if}>{__("yes")}</option>
            <option value="false" {if $processor_params.address_mandatory == "false"}selected="selected"{/if}>{__("no")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="transaction_type">{__("zip_postal_code")}&nbsp;{__("mandatory")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][postcode_mandatory]" id="postcode_mandatory">
            <option value="true" {if $processor_params.postcode_mandatory == "true"}selected="selected"{/if}>{__("yes")}</option>
            <option value="false" {if $processor_params.postcode_mandatory == "false"}selected="selected"{/if}>{__("no")}</option>
        </select>
    </div>
</div>