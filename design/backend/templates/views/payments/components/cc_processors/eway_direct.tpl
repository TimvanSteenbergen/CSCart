<div class="control-group">
    <label class="control-label" for="ew_client_id">{__("client_id")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][client_id]" id="ew_client_id" value="{$processor_params.client_id}"   size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ew_order_prefix">{__("order_prefix")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][order_prefix]" id="ew_order_prefix" value="{$processor_params.order_prefix}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ew_include_cvn">{__("include_cvn")}:</label>
    <div class="controls"><input type="hidden" name="payment_data[processor_params][include_cvn]" value="false">
        <input type="checkbox" name="payment_data[processor_params][include_cvn]" id="ew_include_cvn" value="true" {if $processor_params.include_cvn == "true"} checked="checked"{/if}></div>
    </div>

<div class="control-group">
    <label class="control-label" for="ew_test_mode">{__("test_live_mode")}:</label>
   <div class="controls">
        <select name="payment_data[processor_params][test]" id="ew_test_mode">
           <option value="Y" {if $processor_params.test == "Y"}selected="selected"{/if}>{__("test")}</option>
           <option value="N" {if $processor_params.test == "N"}selected="selected"{/if}>{__("live")}</option>
       </select>
   </div>
</div>