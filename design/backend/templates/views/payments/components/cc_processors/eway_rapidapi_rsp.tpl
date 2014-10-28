<div class="control-group">
    <label class="control-label" for="rapid_username">{__("username")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][username]" id="rapid_username" value="{$processor_params.username}" class="input-text-large"  size="60" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="rapid_password">{__("password")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][password]" id="rapid_password" value="{$processor_params.password}" class="input-text-large"  size="60" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="test">{__("currency")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][currency]" id="currency">
            <option value="GBP" {if $processor_params.currency == "GBP"}selected="selected"{/if}>{__("currency_code_gbp")}</option>
            <option value="AUD" {if $processor_params.currency == "AUD"}selected="selected"{/if}>{__("currency_code_aud")}</option>
            <option value="NZD" {if $processor_params.currency == "NZD"}selected="selected"{/if}>{__("currency_code_nzd")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="test">{__("theme")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][theme]" id="theme">
            <option value="Default" {if $processor_params.theme == "Default"}selected="selected"{/if}>{__("Default")}</option>
            <option value="Bootstrap" {if $processor_params.theme == "Bootstrap"}selected="selected"{/if}>Bootstrap</option>
            <option value="BootstrapAmelia" {if $processor_params.theme == "BootstrapAmelia"}selected="selected"{/if}>BootstrapAmelia</option>
            <option value="BootstrapCerulean" {if $processor_params.theme == "BootstrapCerulean"}selected="selected"{/if}>BootstrapCerulean</option>
            <option value="BootstrapCosmo" {if $processor_params.theme == "BootstrapCosmo"}selected="selected"{/if}>BootstrapCosmo</option>
            <option value="BootstrapCyborg" {if $processor_params.theme == "BootstrapCyborg"}selected="selected"{/if}>BootstrapCyborg</option>
            <option value="BootstrapFlatly" {if $processor_params.theme == "BootstrapFlatly"}selected="selected"{/if}>BootstrapFlatly</option>
            <option value="BootstrapJournal" {if $processor_params.theme == "BootstrapJournal"}selected="selected"{/if}>BootstrapJournal</option>
            <option value="BootstrapReadable" {if $processor_params.theme == "BootstrapReadable"}selected="selected"{/if}>BootstrapReadable</option>
            <option value="BootstrapSimplex" {if $processor_params.theme == "BootstrapSimplex"}selected="selected"{/if}>BootstrapSimplex</option>
            <option value="BootstrapSlate" {if $processor_params.theme == "BootstrapSlate"}selected="selected"{/if}>BootstrapSlate</option>
            <option value="BootstrapSpacelab" {if $processor_params.theme == "BootstrapSpacelab"}selected="selected"{/if}>BootstrapSpacelab</option>
            <option value="BootstrapUnited" {if $processor_params.theme == "BootstrapUnited"}selected="selected"{/if}>BootstrapUnited</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="rapid_headertext">{__("payments.eway_rapidapi_rsp.header_text")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][headertext]" id="rapid_headertext" value="{$processor_params.headertext}" class="input-text-large"  size="60" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="rapid_mode">{__("test_live_mode")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][mode]" id="rapid_mode">
            <option value="test" {if $processor_params.mode == "test"}selected="selected"{/if}>{__("test")}</option>
            <option value="live" {if $processor_params.mode == "live"}selected="selected"{/if}>{__("live")}</option>
        </select>
    </div>
</div>
