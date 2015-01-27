
<p>{__("text_hsbc_notice", ["[cart_dir]" => $config.dir.payments])}</p>
<hr>

<div class="control-group">
    <label class="control-label" for="store_id">{__("client_id")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][store_id]" id="store_id" value="{$processor_params.store_id}" >
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="cpihashkey">{__("cpi_hash_key")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][cpihashkey]" id="cpihashkey" value="{$processor_params.cpihashkey}"  size="60">
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

<div class="control-group">
    <label class="control-label" for="currency">{__("currency")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][currency]" id="currency">
            <option value="826" {if $processor_params.currency == "826"}selected="selected"{/if}>{__("currency_code_gbp")}</option>
            <option value="978" {if $processor_params.currency == "978"}selected="selected"{/if}>{__("currency_code_eur")}</option>
            <option value="840" {if $processor_params.currency == "840"}selected="selected"{/if}>{__("currency_code_usd")}</option>
            <option value="344" {if $processor_params.currency == "344"}selected="selected"{/if}>{__("currency_code_hkd")}</option>
            <option value="392" {if $processor_params.currency == "392"}selected="selected"{/if}>{__("currency_code_jpy")}</option>
        </select>
    </div>
</div>