{assign var="return_url" value=""|fn_checkout_url:'C'}
<p>{__("text_linkpointc_notice", ["[return_url]" => "<span>`$return_url`</span>"])}</p>

<hr>

<div class="control-group">
    <label class="control-label" for="store">{__("store_number")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][store]" id="store" value="{$processor_params.store}"   size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="prefix">{__("order_prefix")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][prefix]" id="prefix" value="{$processor_params.prefix}"   size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="transaction_type">{__("transaction_type")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][transaction_type]" id="transaction_type">
            <option value="SALE" {if $processor_params.transaction_type == "SALE"}selected="selected"{/if}>{__("sale")}</option>
            <option value="PREAUTH" {if $processor_params.transaction_type == "PREAUTH"}selected="selected"{/if}>{__("preauth")}</option>
            <option value="POSTAUTH" {if $processor_params.transaction_type == "POSTAUTH"}selected="selected"{/if}>{__("postauth")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="test">{__("test_live_mode")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][test]" id="test">
            <option value="TEST" {if $processor_params.test == "TEST"}selected="selected"{/if}>{__("test")}</option>
            <option value="LIVE" {if $processor_params.test == "LIVE"}selected="selected"{/if}>{__("live")}</option>
        </select>
    </div>
</div>