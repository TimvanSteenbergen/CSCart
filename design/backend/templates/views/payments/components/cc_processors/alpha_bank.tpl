<div class="control-group">
	<label class="control-label" for="ab_merchant_id">{__("merchant_id")}:</label>
	<div class="controls">
		<input type="text" name="payment_data[processor_params][merchant_id]" id="ab_merchant_id" value="{$processor_params.merchant_id}" class="input-text" size="60" />
	</div>
</div>

<div class="control-group">
    <label class="control-label" for="ab_shared_secret">{__("shared_secret")}:</label>
    <div class="controls">
        <input type="password" name="payment_data[processor_params][shared_secret]" id="ab_shared_secret" value="{$processor_params.shared_secret}"   size="60">
    </div>
</div>

<div class="control-group">
	<label class="control-label" for="ab_currency">{__("currency")}:</label>
	<div class="controls">
		<select name="payment_data[processor_params][currency]" id="ab_currency">
			<option value="EUR"{if $processor_params.currency == "EUR"} selected="selected"{/if}>{__("currency_code_eur")}</option>
		</select>
	</div>
</div>

<div class="control-group">
    <label class="control-label" for="ab_language">{__("language")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][language]" id="ab_language">
            <option value="en"{if $processor_params.language eq "en"} selected="selected"{/if}>{__("english")}</option>
            <option value="el"{if $processor_params.language eq "el"} selected="selected"{/if}>{__("greek")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ab_mode">{__("test_live_mode")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][mode]" id="ab_mode">
            <option value="test"{if $processor_params.mode eq "test"} selected="selected"{/if}>{__("test")}</option>
            <option value="live"{if $processor_params.mode eq "live"} selected="selected"{/if}>{__("live")}</option>
        </select>
    </div>
</div>
