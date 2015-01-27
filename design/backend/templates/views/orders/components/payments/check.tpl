<div class="control-group">
    <label for="customer_signature" class="control-label cm-required">{__("customer_signature")}</label>
    <div class="controls">
    	<input id="customer_signature" size="35" type="text" name="payment_info[customer_signature]" value="{$cart.payment_info.customer_signature}" class="cm-autocomplete-off" />
    </div>
</div>
<div class="control-group">
    <label for="checking_account_number" class="control-label cm-required">{__("checking_account_number")}</label>
    <div class="controls">
    	<input id="checking_account_number" size="35" type="text" name="payment_info[checking_account_number]" value="{$cart.payment_info.checking_account_number}" class="cm-autocomplete-off" />
    </div>
</div>
<div class="control-group">
    <label for="bank_routing_number" class="control-label cm-required">{__("bank_routing_number")}</label>
    <div class="controls">
    	<input id="bank_routing_number" size="35" type="text" name="payment_info[bank_routing_number]" value="{$cart.payment_info.bank_routing_number}" class="cm-autocomplete-off" />
    </div>
</div>