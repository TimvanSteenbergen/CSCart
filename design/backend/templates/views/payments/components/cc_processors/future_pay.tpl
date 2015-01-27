<div class="control-group">
    <label class="control-label" for="merchant_id">{__("merchant_id")}:</label>
     <div class="controls">
         <input type="text" name="payment_data[processor_params][merchant_id]" id="merchant_id" value="{$processor_params.merchant_id}" />
     </div>
</div>

<div class="control-group">
    <label class="control-label" for="mode">{__("test_live_mode")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][mode]" id="mode">
            <option value="T" {if $processor_params.mode == "T"}selected="selected"{/if}>{__("test")}</option>
            <option value="P" {if $processor_params.mode == "P"}selected="selected"{/if}>{__("live")}</option>
        </select>
    </div>
</div>
