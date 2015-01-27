<div class="control-group">
    <label class="control-label" for="paypal_adv_merchant_login">{__("merchant_login")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][merchant_login]" id="paypal_adv_merchant_login" value="{$processor_params.merchant_login}" class="input-text" size="60" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="paypal_adv_api_user">{__("api_user")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][api_user]" id="paypal_adv_api_user" value="{$processor_params.api_user}" class="input-text" size="60" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="paypal_adv_api_partner">{__("api_partner")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][api_partner]" id="paypal_adv_api_partner" value="{$processor_params.api_partner}" class="input-text" size="60" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="paypal_adv_api_password">{__("api_password")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][api_password]" id="paypal_adv_api_password" value="{$processor_params.api_password}" class="input-text" size="60" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="paypal_adv_testmode">{__("test_live_mode")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][testmode]" id="paypal_adv_testmode">
            <option value="Y" {if $processor_params.testmode == "Y"}selected="selected"{/if}>{__("test")}</option>
            <option value="N" {if $processor_params.testmode == "N"}selected="selected"{/if}>{__("live")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="paypal_adv_layout">{__("payments.paypal_adv_layout")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][layout]" id="paypal_adv_layout">
            <option value="templateA" {if $processor_params.layout == "templateA"}selected="selected"{/if}>{__("payments.layout_a")}</option>
            <option value="templateB" {if $processor_params.layout == "templateB"}selected="selected"{/if}>{__("payments.layout_b")}</option>
            <option value="minLayout" {if $processor_params.layout == "minLayout"}selected="selected"{/if}>{__("payments.layout_c")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label cm-color" for="paypal_adv_payflowcolor">{__("payflowcolor")}:</label>
    <div class="controls">
        {include file="common/colorpicker.tpl" cp_name="payment_data[processor_params][payflowcolor]" cp_id="paypal_adv_payflowcolor" cp_value=$processor_params.payflowcolor}
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="paypal_adv_header_image">{__("header_image")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][header_image]" id="paypal_adv_header_image" value="{$processor_params.header_image}" class="input-text"  size="60" maxlength="127" />
    </div>
</div>

<div class="control-group">
    <label class="control-label cm-color" for="paypal_adv_button_bgcolor">{__("button_bgcolor")}:</label>
    <div class="controls">
        {include file="common/colorpicker.tpl" cp_name="payment_data[processor_params][button_bgcolor]" cp_id="paypal_adv_button_bgcolor" cp_value=$processor_params.button_bgcolor}
    </div>
</div>

<div class="control-group">
    <label class="control-label cm-color" for="paypal_adv_button_text_color">{__("button_text_color")}:</label>
    <div class="controls">
        {include file="common/colorpicker.tpl" cp_name="payment_data[processor_params][button_text_color]" cp_id="paypal_adv_button_text_color" cp_value=$processor_params.button_text_color}
    </div>
</div>

<div class="control-group">
    <label class="control-label cm-color" for="paypal_adv_collapse_bg_color">{__("collapse_bg_color")}:</label>
    <div class="controls">
        {include file="common/colorpicker.tpl" cp_name="payment_data[processor_params][collapse_bg_color]" cp_id="paypal_adv_collapse_bg_color" cp_value=$processor_params.collapse_bg_color}
    </div>
</div>

<div class="control-group">
    <label class="control-label cm-color" for="paypal_adv_collapse_text_color">{__("collapse_text_color")}:</label>
    <div class="controls">
        {include file="common/colorpicker.tpl" cp_name="payment_data[processor_params][collapse_text_color]" cp_id="paypal_adv_collapse_text_color" cp_value=$processor_params.collapse_text_color}
    </div>
</div>

<div class="control-group">
    <label class="control-label cm-color" for="paypal_adv_label_text_color">{__("label_text_color")}:</label>
    <div class="controls">
        {include file="common/colorpicker.tpl" cp_name="payment_data[processor_params][label_text_color]" cp_id="paypal_adv_label_text_color" cp_value=$processor_params.label_text_color}
    </div>
</div>
