<div class="control-group">
    <label class="control-label" for="merchant_id">{__("merchant_id")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][merchant_id]" id="merchant_id" value="{$processor_params.merchant_id}"   size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="password">{__("password")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][password]" id="password" value="{$processor_params.password}"   size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="order_prefix">{__("order_prefix")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][order_prefix]" id="order_prefix" value="{$processor_params.order_prefix}"   size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="mode">{__("test_live_mode")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][mode]" id="mode">
            <option value="true" {if $processor_params.mode == "true"}selected="selected"{/if}>{__("test")}: {__("approved")}</option>
            <option value="false" {if $processor_params.mode == "false"}selected="selected"{/if}>{__("test")}: {__("declined")}</option>
            <option value="live" {if $processor_params.mode == "live"}selected="selected"{/if}>{__("live")}</option>
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
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="dups">{__("duplicate")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][dups]" id="dups">
            <option value="" {if $processor_params.deferred == ""}selected="selected"{/if}>{__("disabled")}</option>
            <option value="false" {if $processor_params.deferred == "false"}selected="selected"{/if}>{__("enabled")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="deferred">{__("deferred")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][deferred]" id="deferred">
            <option value="full" {if $processor_params.deferred == "full"}selected="selected"{/if}>{__("full")}</option>
            <option value="true" {if $processor_params.deferred == "true"}selected="selected"{/if}>{__("true")}</option>
            <option value="reuse" {if $processor_params.deferred == "reuse"}selected="selected"{/if}>{__("reuse")}</option>
            <option value="" {if !$processor_params.deferred}selected="selected"{/if}>{__("do_not_use")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="mail_subject">{__("mail_subject")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][mail_subject]" id="mail_subject" value="{$processor_params.mail_subject}"   size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="mail_message">{__("mail_message")}:</label>
    <div class="controls">
        <textarea name="payment_data[processor_params][mail_message]" id="mail_message" class="input-textarea-long" cols="80" rows="7">{$processor_params.mail_message}</textarea>
    </div>
</div>