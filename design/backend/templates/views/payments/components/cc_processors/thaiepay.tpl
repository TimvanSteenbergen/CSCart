{assign var="r_url" value="payment_notification.finish?payment=thaiepay"|fn_url:'C':'http'}
{assign var="p_url" value="payment_notification.notify?payment=thaiepay"|fn_url:'C':'http'}

<div> 
    {__("text_thaiepay_notice", ["[return_url]" => $r_url, "[postback_url]" => $p_url])}
</div> 
<hr> 

<div class="control-group">
    <label class="control-label" for="merchantid">{__("merchant_id")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][merchantid]" id="merchantid" value="{$processor_params.merchantid}" >
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="details">{__("payment_details")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][details]" id="details" value="{$processor_params.details}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="currency">{__("currency")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][currency]" id="currency">
            <option value="00" {if $processor_params.currency == "00"}selected="selected"{/if}>{__("currency_code_thb")}</option>
            <option value="01" {if $processor_params.currency == "01"}selected="selected"{/if}>{__("currency_code_usd")}</option>
            <option value="02" {if $processor_params.currency == "02"}selected="selected"{/if}>{__("currency_code_jpy")}</option>
            <option value="03" {if $processor_params.currency == "03"}selected="selected"{/if}>{__("currency_code_sgd")}</option>
            <option value="04" {if $processor_params.currency == "04"}selected="selected"{/if}>{__("currency_code_hkd")}</option>
            <option value="05" {if $processor_params.currency == "05"}selected="selected"{/if}>{__("currency_code_eur")}</option>
            <option value="06" {if $processor_params.currency == "06"}selected="selected"{/if}>{__("currency_code_gbp")}</option>
            <option value="07" {if $processor_params.currency == "07"}selected="selected"{/if}>{__("currency_code_aud")}</option>
            <option value="08" {if $processor_params.currency == "08"}selected="selected"{/if}>{__("currency_code_chf")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="add_param_name">Additional parameter Name:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][add_param_name]" id="add_param_name" value="{$processor_params.add_param_name}" >
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="add_param_value">Additional parameter Value:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][add_param_value]" id="add_param_value" value="{$processor_params.add_param_value}" >
    </div>
</div>