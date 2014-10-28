<div class="control-group">
    <label class="control-label" for="username">{__("username")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][username]" id="username" size="60" value="{$processor_params.username}" >
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="password">{__("password")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][password]" id="password" size="60" value="{$processor_params.password}" >
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="vendor">{__("vendor")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][vendor]" id="vendor" size="60" value="{$processor_params.vendor}" >
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="partner">{__("partner")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][partner]" id="partner" size="60" value="{$processor_params.partner}" >
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="pf_currency">{__("currency")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][currency]" id="pf_currency">
            <option value="840"{if $processor_params.currency == "840"} selected="selected"{/if}>{__("currency_code_usd")}</option>
            <option value="978"{if $processor_params.currency == "978"} selected="selected"{/if}>{__("currency_code_eur")}</option>
            <option value="124"{if $processor_params.currency == "124"} selected="selected"{/if}>{__("currency_code_cad")}</option>
            <option value="36"{if $processor_params.currency == "36"} selected="selected"{/if}>{__("currency_code_aud")}</option>
            <option value="986"{if $processor_params.currency == "986"} selected="selected"{/if}>{__("currency_code_brl")}</option>
            <option value="826"{if $processor_params.currency == "826"} selected="selected"{/if}>{__("currency_code_gbp")}</option>
            <option value="203"{if $processor_params.currency == "203"} selected="selected"{/if}>{__("currency_code_czk")}</option>
            <option value="208"{if $processor_params.currency == "208"} selected="selected"{/if}>{__("currency_code_dkk")}</option>
            <option value="344"{if $processor_params.currency == "344"} selected="selected"{/if}>{__("currency_code_hkd")}</option>
            <option value="348"{if $processor_params.currency == "348"} selected="selected"{/if}>{__("currency_code_huf")}</option>
            <option value="376"{if $processor_params.currency == "376"} selected="selected"{/if}>{__("currency_code_ils")}</option>
            <option value="392"{if $processor_params.currency == "392"} selected="selected"{/if}>{__("currency_code_jpy")}</option>
            <option value="484"{if $processor_params.currency == "484"} selected="selected"{/if}>{__("currency_code_mxn")}</option>
            <option value="901"{if $processor_params.currency == "901"} selected="selected"{/if}>{__("currency_code_twd")}</option>
            <option value="554"{if $processor_params.currency == "554"} selected="selected"{/if}>{__("currency_code_nzd")}</option>
            <option value="578"{if $processor_params.currency == "578"} selected="selected"{/if}>{__("currency_code_nok")}</option>
            <option value="608"{if $processor_params.currency == "608"} selected="selected"{/if}>{__("currency_code_php")}</option>
            <option value="985"{if $processor_params.currency == "985"} selected="selected"{/if}>{__("currency_code_pln")}</option>
            <option value="702"{if $processor_params.currency == "702"} selected="selected"{/if}>{__("currency_code_sgd")}</option>
            <option value="752"{if $processor_params.currency == "752"} selected="selected"{/if}>{__("currency_code_sek")}</option>
            <option value="756"{if $processor_params.currency == "756"} selected="selected"{/if}>{__("currency_code_chf")}</option>
            <option value="764"{if $processor_params.currency == "764"} selected="selected"{/if}>{__("currency_code_thb")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="order_prefix">{__("order_prefix")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][order_prefix]" id="order_prefix" size="60" value="{$processor_params.order_prefix}" >
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
