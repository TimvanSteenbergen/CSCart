{assign var="r_url" value="payment_notification.notify?payment=2checkout"|fn_url:'C':'http'}
<p>{__("text_2checkout_notice", ["[return_url]" => $r_url])}</p>
<hr>

<div class="control-group">
    <label class="control-label" for="account_number">{__("account_number")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][account_number]" id="account_number" value="{$processor_params.account_number}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="secret_word">{__("secret_word")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][secret_word]" id="secret_word" value="{$processor_params.secret_word}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="mode">{__("test_live_mode")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][mode]" id="mode">
            <option value="test" {if $processor_params.mode == "test"}selected="selected"{/if}>{__("test")}</option>
            <option value="live" {if $processor_params.mode == "live"}selected="selected"{/if}>{__("live")}</option>
        </select>
    </div>
</div>



{include file="common/subheader.tpl" title=__("text_2co_ins") target="#instant_notification_service"}
<div id="instant_notification_service" class="in collapse">
    <fieldset>
    
        <div class="control-group">
            <label class="control-label" for="elm_2co_fraud_verification">{__("2co_enable_fraud_verification")}:</label>
            <div class="controls"><input type="hidden" name="payment_data[processor_params][fraud_verification]" value="N">
                <input type="checkbox" name="payment_data[processor_params][fraud_verification]" id="elm_2co_fraud_verification" value="Y" {if $processor_params.fraud_verification == "Y"}checked="checked"{/if}></div>
        </div>
    
        {assign var="statuses" value=$smarty.const.STATUSES_ORDER|fn_get_simple_statuses}
    
        <div class="control-group">
            <label class="control-label" for="elm_2co_fraud_wait">{__("2co_fraud_wait")}:</label>
            <div class="controls">
                <select name="payment_data[processor_params][fraud_wait]" id="elm_2co_fraud_wait">
                    {foreach from=$statuses item="s" key="k"}
                    <option value="{$k}" {if $processor_params.fraud_wait == $k || !$processor_params.fraud_wait && $k == 'O'}selected="selected"{/if}>{$s}</option>
                    {/foreach}
                </select>
            </div>
        </div>
    
        <div class="control-group">
            <label class="control-label" for="elm_2co_fraud_fail">{__("2co_fraud_fail")}:</label>
            <div class="controls">
                <select name="payment_data[processor_params][fraud_fail]" id="elm_2co_fraud_fail">
                    {foreach from=$statuses item="s" key="k"}
                    <option value="{$k}" {if $processor_params.fraud_fail == $k || !$processor_params.fraud_wait && $k == 'D'}selected="selected"{/if}>{$s}</option>
                    {/foreach}
                </select>
            </div>
        </div>
    
    </fieldset>
</div>
