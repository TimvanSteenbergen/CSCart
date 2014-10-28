{assign var="callback_url" value="payment_notification.result?payment=paybox"|fn_url:'C':'http'}
<p>{__("text_paybox_notice", ["[callback_url]" => $callback_url, "[paybox_dir]" => "`$config.dir.payments`paybox_files"])}</p>
<hr>

<div class="control-group">
    <label class="control-label" for="site_num">{__("site_number")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][site_num]" id="site_num" size="60" value="{$processor_params.site_num}" >
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="rank_num">{__("rank_number")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][rank_num]" id="rank_num" size="60" value="{$processor_params.rank_num}" >
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="rank_num">{__("identifier")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][identifier]" id="rank_num" size="60" value="{$processor_params.identifier}" >
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="currency">{__("currency")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][currency]" id="currency">
            <option value="250"{if $processor_params.currency eq "250"} selected="selected"{/if}>{__("currency_code_frf")}</option>
            <option value="978"{if $processor_params.currency eq "978"} selected="selected"{/if}>{__("currency_code_eur")}</option>
            <option value="840"{if $processor_params.currency eq "840"} selected="selected"{/if}>{__("currency_code_usd")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="language">{__("language")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][language]" id="language">
            <option value="FRA"{if $processor_params.language eq "FRA"} selected="selected"{/if}>{__("french")}</option>
            <option value="GBR"{if $processor_params.language eq "GBR"} selected="selected"{/if}>{__("english")}</option>
            <option value="DEU"{if $processor_params.language eq "DEU"} selected="selected"{/if}>{__("german")}</option>
            <option value="ESP"{if $processor_params.language eq "ESP"} selected="selected"{/if}>{__("spanish")}</option>
            <option value="ITA"{if $processor_params.language eq "ITA"} selected="selected"{/if}>{__("italian")}</option>
            <option value="NLD"{if $processor_params.language eq "NLD"} selected="selected"{/if}>{__("dutch")}</option>
            <option value="SWE"{if $processor_params.language eq "SWE"} selected="selected"{/if}>{__("swedish")}</option>
        </select>
    </div>
</div>