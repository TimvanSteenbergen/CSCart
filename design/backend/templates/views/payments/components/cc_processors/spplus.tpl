<div class="control-group">
    <label class="control-label" for="elm_clent">CLENT:</label>
    <div class="controls">
       <input type="text" name="payment_data[processor_params][clent]" id="elm_clent" value="{$processor_params.clent}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="merchant_id">{__("merchant_id")}:</label>
    <div class="controls">
       <input type="text" name="payment_data[processor_params][merchant_id]" id="merchant_id" value="{$processor_params.merchant_id}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="post_url">{__("post_url")}:</label>
    <div class="controls">
       <input type="text" name="payment_data[processor_params][post_url]" id="post_url" value="{$processor_params.post_url}"  size="60">
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
            <option value="978" {if $processor_params.currency == "978"}selected="selected"{/if}>{__("currency_code_eur")}</option>
            <option value="840" {if $processor_params.currency == "840"}selected="selected"{/if}>{__("currency_code_usd")}</option>
            <option value="826" {if $processor_params.currency == "826"}selected="selected"{/if}>{__("currency_code_gbp")}</option>
            <option value="124" {if $processor_params.currency == "124"}selected="selected"{/if}>{__("currency_code_cad")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="order_prefix">{__("order_prefix")}:</label>
    <div class="controls"><input type="text" name="payment_data[processor_params][order_prefix]" id="order_prefix" value="{$processor_params.order_prefix}"   size="60"></div>
</div>