{assign var="payment_url" value="payment_notification.index_redirect?payment=deltapay"|fn_url:'C':'http'}
{assign var="result_url" value="payment_notification.process?payment=deltapay"|fn_url:'C':'http'}
<p>{__("text_deltapay_notice", ["[payment_url]" => $payment_url, "[result_url]" => $result_url])}</p>
<hr>

<div class="control-group">
    <label class="control-label" for="merchant_id">{__("merchant_id")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][merchant_id]" id="merchant_id" value="{$processor_params.merchant_id}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="order_prefix">{__("order_prefix")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][order_prefix]" id="order_prefix" value="{$processor_params.order_prefix}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="currency">{__("currency")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][currency]" id="currency">
            <option value="040"{if $processor_params.currency eq "040"} selected="selected"{/if}>Austrian Shilling</option>
            <option value="056"{if $processor_params.currency eq "056"} selected="selected"{/if}>Belgian Franc</option>
            <option vaalue="250"{if $processor_params.currency eq "250"} selected="selected"{/if}>French Franc</option>
            <option value="300"{if $processor_params.currency eq "300"} selected="selected"{/if}>Greek Dragmen</option>
            <option value="280"{if $processor_params.currency eq "280"} selected="selected"{/if}>Deutsche Mark</option>
            <option value="380"{if $processor_params.currency eq "380"} selected="selected"{/if}>Italian Lira</option>
            <option value="442"{if $processor_params.currency eq "442"} selected="selected"{/if}>Luxembourg Franc</option>
            <option value="528"{if $processor_params.currency eq "528"} selected="selected"{/if}>Netherlands Guilder</option>
            <option value="724"{if $processr_params.currency eq "724"} selected="selected"{/if}>Spanish Peseta</option>
            <option value="756"{if $processor_params.currency eq "756"} selected="selected"{/if}>Swiss Francs</option>
            <option value="826"{if $processor_params.currency eq "826"} selected="selected"{/if}>Sterling</option>
            <option value="840"{if $processor_params.currency eq "840"} selected="selected"{/if}>US Dollars</option>
            <option value="978"{if $processor_params.currency eq "978"} selected="selected"{/if}>Euro</option>
            <option value="392"{if $processor_params.currency eq "392"} selected="selected"{/if}>Japanese Yen</option>
        </select>
    </div>
</div>
