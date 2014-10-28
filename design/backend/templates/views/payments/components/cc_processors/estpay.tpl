<div class="control-group">
    <label class="control-label" for="merchant_name">{__("merchant_name")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][merchant_name]" id="merchant_name" value="{$processor_params.merchant_name}"   size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="merchant_password">{__("password")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][merchant_password]" id="merchant_password" value="{$processor_params.merchant_password}"   size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="client_id">{__("client_id")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][client_id]" id="client_id" value="{$processor_params.client_id}"   size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="currency">{__("currency")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][currency]" id="currency">
            <option value="949"{if $processor_params.currency eq "949"} selected="selected"{/if}>{__("currency_code_try")}</option>
            <option value="978"{if $processor_params.currency eq "978"} selected="selected"{/if}>{__("currency_code_eur")}</option>
            <option value="840"{if $processor_params.currency eq "840"} selected="selected"{/if}>{__("currency_code_usd")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="mode">{__("test_live_mode")}:</label>
   <div class="controls">
        <select name="payment_data[processor_params][mode]" id="mode">
           <option value="test"{if $processor_params.mode eq "test"} selected="selected"{/if}>{__("test")}</option>
           <option value="live"{if $processor_params.mode eq "live"} selected="selected"{/if}>{__("live")}</option>
       </select>
   </div>
</div>