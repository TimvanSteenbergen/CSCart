{assign var="url" value="block_manager.manage"|fn_url}
<p>{__("payments.cresecure.location_notice", ["[url]" => $url])}</p>
<hr>

<div class="control-group">
    <label class="control-label" for="cresecureid">{__("cresecureid")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][cresecureid]" id="cresecureid" value="{$processor_params.cresecureid}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="cresecureapitoken">{__("cresecureapitoken")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][cresecureapitoken]" id="cresecureapitoken" value="{$processor_params.cresecureapitoken}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="test">{__("test_live_mode")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][test]" id="test">
            <option value="test"{if $processor_params.test == 'test'} selected="selected"{/if}>{__("test")}</option>
            <option value="live"{if $processor_params.test == 'live'} selected="selected"{/if}>{__("live")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="currency">{__("currency")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][currency]" id="currency">
            {foreach from=""|fn_get_simple_currencies key="code" item="currency"}
                <option value="{$code}"{if $processor_params.currency == $code} selected="selected"{/if}>{$currency}</option>
            {/foreach}
        </select>
    </div>
</div>


{include file="common/subheader.tpl" title=__("cresecure_allowed_types") target="#cresecure_allowed_types"}
<div id="cresecure_allowed_types" class="in collapse">
    <fieldset>
    
    <div class="control-group">
        <label class="control-label" for="allowed_types_visa">Visa:</label>
        <div class="controls"><input type="checkbox" name="payment_data[processor_params][allowed_types][visa]" id="allowed_types_visa" value="Visa"{if $processor_params.allowed_types && $processor_params.allowed_types.visa} checked="checked"{/if}></div>
    </div>
    
    <div class="control-group">
        <label class="control-label" for="allowed_types_mastercard">MasterCard:</label>
        <div class="controls"><input type="checkbox" name="payment_data[processor_params][allowed_types][mastercard]" id="allowed_types_mastercard" value="MasterCard"{if $processor_params.allowed_types && $processor_params.allowed_types.mastercard} checked="checked"{/if}></div>
    </div>
    
    <div class="control-group">
        <label class="control-label" for="allowed_types_amx">American Express:</label>
        <div class="controls"><input type="checkbox" name="payment_data[processor_params][allowed_types][amx]" id="allowed_types_amx" value="American Express"{if $processor_params.allowed_types && $processor_params.allowed_types.amx} checked="checked"{/if}></div>
    </div>
    
    <div class="control-group">
        <label class="control-label" for="allowed_types_discover">Discover:</label>
        <div class="controls"><input type="checkbox" name="payment_data[processor_params][allowed_types][discover]" id="allowed_types_discover" value="Discover"{if $processor_params.allowed_types && $processor_params.allowed_types.discover} checked="checked"{/if}></div>
    </div>
    
    </fieldset>
</div>
