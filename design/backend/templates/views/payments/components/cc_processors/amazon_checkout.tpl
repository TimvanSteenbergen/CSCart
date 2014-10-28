{* Amazon general settings *}
{assign var="callback_url" value="https"|fn_payment_url:"amazon_checkout.php"}
<p>{__("text_amazon_callback_url", ["[callback_url]" => $callback_url])}</p>
<p>{__("text_amazon_link_message")}</p>
<p>{__("text_amazon_surcharge")}</p>
<hr>

<fieldset>
    <div class="control-group">
        <label class="control-label" for="merchant_id">{__("merchant_id")}:</label>
        <div class="controls">
            <input type="text" name="payment_data[processor_params][merchant_id]" id="merchant_id" value="{$processor_params.merchant_id}"  size="60">
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="aws_access_public_key">{__("lbl_amazon_aws_access_public_key")}:</label>
        <div class="controls">
            <input type="text" name="payment_data[processor_params][aws_access_public_key]" id="aws_access_public_key" value="{$processor_params.aws_access_public_key}"  size="60">
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="aws_secret_access_key">{__("lbl_amazon_aws_access_secret_key")}:</label>
        <div class="controls">
            <input type="text" name="payment_data[processor_params][aws_secret_access_key]" id="aws_secret_access_key" value="{$processor_params.aws_secret_access_key}"  size="60">
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="test">{__("currency")}:</label>
        <div class="controls">
            <select name="payment_data[processor_params][currency]" id="currency">
                <option value="EUR" {if $processor_params.currency == "EUR"}selected="selected"{/if}>{__("currency_code_eur")}</option>
                <option value="GBP" {if $processor_params.currency == "GBP"}selected="selected"{/if}>{__("currency_code_gbp")}</option>
                <option value="USD" {if $processor_params.currency == "USD"}selected="selected"{/if}>{__("currency_code_usd")}</option>
            </select>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="test">{__("lbl_amazon_process_order_on_failure")}:</label>
        <div class="controls">
            <select name="payment_data[processor_params][process_on_failure]" id="test">
                <option value="N" {if $processor_params.process_on_failure == "N"}selected="selected"{/if}>{__("no")}</option>
                <option value="Y" {if $processor_params.process_on_failure == "Y"}selected="selected"{/if}>{__("yes")}</option>
            </select>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="test">{__("test_live_mode")}:</label>
        <div class="controls">
            <select name="payment_data[processor_params][test]" id="test">
                <option value="N" {if $processor_params.test == "N"}selected="selected"{/if}>{__("live")}</option>
                <option value="Y" {if $processor_params.test == "Y"}selected="selected"{/if}>{__("test")}</option>
            </select>
        </div>
    </div>
</fieldset>

{* Amazon button style *}

{include file="common/subheader.tpl" title=__("lbl_amazon_button_style") target="#amazon_button_style"}
<div id="amazon_button_style" class="in collapse">
    <fieldset>
        <div class="control-group">
            <label class="control-label" for="background_color">{__("lbl_amazon_background_color")}:</label>
            <div class="controls">
                <select name="payment_data[processor_params][button_background]" id="background_color">
                    <option value="white" {if $processor_params.button_background == "white"}selected="selected"{/if}>{__("lbl_amazon_color_white")}</option>
                    <option value="light" {if $processor_params.button_background == "light"}selected="selected"{/if}>{__("lbl_amazon_color_light")}</option>
                    <option value="dark" {if $processor_params.button_background == "dark"}selected="selected"{/if}>{__("lbl_amazon_color_dark")}</option>
                </select>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="button_color">{__("lbl_amazon_button_color")}:</label>
            <div class="controls">
                <select name="payment_data[processor_params][button_color]" id="button_color">
                    <option value="orange" {if $processor_params.button_color == "orange"}selected="selected"{/if}>{__("lbl_amazon_color_orange")}</option>
                    <option value="tan" {if $processor_params.button_color == "tan"}selected="selected"{/if}>{__("lbl_amazon_color_tan")}</option>
                </select>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="button_size">{__("lbl_amazon_button_size")}:</label>
            <div class="controls">
                <select name="payment_data[processor_params][button_size]" id="button_size">
                    <option value="x-large" {if $processor_params.button_size == "x-large"}selected="selected"{/if}>{__("lbl_amazon_size_xlarge")}</option>
                    <option value="large" {if $processor_params.button_size == "large"}selected="selected"{/if}>{__("lbl_amazon_size_large")}</option>
                    <option value="medium" {if $processor_params.button_size == "medium"}selected="selected"{/if}>{__("lbl_amazon_size_medium")}</option>
                </select>
            </div>
        </div>
    </fieldset>
</div>
