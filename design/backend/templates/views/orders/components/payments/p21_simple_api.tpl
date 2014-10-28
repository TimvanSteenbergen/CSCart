<div class="control-group">
    <label for="dateofbirth" class="control-label cm-required">{__("date_of_birth")}:</label>
    <div class="controls">
        {include file="common/calendar.tpl" date_id="date_of_birth" date_name="payment_info[date_of_birth]" date_val=$cart.payment_info.date_of_birth|default:$user_data.birthday start_year="1902" end_year="0"}
    </div>
</div>
<div class="control-group">
    <label for="last4ssn" class="control-label cm-required">{__("last4ssn")}:</label>
    <div class="controls">
        <input id="last4ssn" maxlength="4" size="35" type="text" name="payment_info[last4ssn]" value="{$cart.payment_info.last4ssn}" class="cm-autocomplete-off" />
    </div>
</div>
<div class="control-group">
    <label for="phone_number" class="control-label cm-required cm-regexp" data-ca-regexp="{literal}^([0-9]{3}[ ]{1}[0-9]{3}[ ]{1}[0-9]{4})${/literal}" data-ca-message="{__("error_validator_phone_number")}">{__("phone")}:</label>
    <div class="controls">
        <input id="phone_number" size="35" type="text" name="payment_info[phone]" value="{$cart.payment_info.phone|default:$user_data.b_phone|default:$user_data.phone}" class="cm-autocomplete-off" />
    </div>
</div>
<div class="control-group">
    <label for="passport_number" class="control-label ">{__("passport_number")}:</label>
    <div class="controls">
        <input id="passport_number" size="35" type="text" name="payment_info[passport_number]" value="{$cart.payment_info.passport_number}" class="cm-autocomplete-off" />
    </div>
</div>
<div class="control-group">
    <label for="drlicense_number" class="control-label ">{__("drlicense_number")}:</label>
    <div class="controls">
        <input id="drlicense_number" size="35" type="text" name="payment_info[drlicense_number]" value="{$cart.payment_info.drlicense_number}" class="cm-autocomplete-off" />
    </div>
</div>
<div class="control-group">
    <label for="routingcode" class="control-label cm-required">{__("routing_code")}:</label>
    <div class="controls">
        <input id="routingcode" maxlength="9" size="35" type="text" name="payment_info[routing_code]" value="{$cart.payment_info.routing_code}" class="cm-autocomplete-off" />
    </div>
</div>
<div class="control-group">
    <label for="accountnr" class="control-label cm-required">{__("account_number")}:</label>
    <div class="controls">
        <input id="accountnr" maxlength="20" size="35" type="text" name="payment_info[account_number]" value="{$cart.payment_info.account_number}" class="cm-autocomplete-off" />
    </div>
</div>
<div class="control-group">
    <label for="checknr" class="control-label cm-required">{__("check_number")}:</label>
    <div class="controls">
        <input id="checknr" maxlength="10" size="35" type="text" name="payment_info[check_number]" value="{$cart.payment_info.check_number}" class="cm-autocomplete-off" />
    </div>
</div>
<div class="control-group">
    <label for="p21agree" class="control-label cm-required">{__("p21agree")} (<a class="cm-tooltip" title="{__("p21agree_tooltip")}">?</a>):</label>
    <div class="controls">
        <input id="p21agree" maxlength="8" size="35" type="text" name="payment_info[mm_agree]" value="{$cart.payment_info.mm_agree}" class="cm
    -autocomplete-off" />
    </div>
</div>
