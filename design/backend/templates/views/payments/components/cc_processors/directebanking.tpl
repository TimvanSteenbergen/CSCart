{if $addons.directebanking.status == 'A'}
<br>
<a href="{"directebanking.update?payment_id=`$payment_id`"|fn_url}">{__("create_account_in_directebanking_system")}</a>
<br>
{/if}

{assign var="success_url" value="payment_notification.success?payment=directebanking&order_id=-USER_VARIABLE_0-"|fn_url:"C":"http"}
{assign var="abort_url" value="payment_notification.abort?payment=directebanking&order_id=-USER_VARIABLE_0-"|fn_url:"C":"http"}
{assign var="notification_url" value="payment_notification.notification?payment=directebanking&order_id=-USER_VARIABLE_0-"|fn_url:"C":"http"}

<p>{__("text_directebanking_notice", ["[success_url]" => $success_url, "[abort_url]" => $abort_url, "[notification_url]" => $notification_url])}</p>

{*
Set 'Success link' to: <b>[success_url]</b><br>
Set 'Abort link' to: <b>[abort_url]</b><br>
Add new HTTP notifications and set 'Notification URL' to: <b>[notification_url]</b><br>
Activate input check and set 'Hash Algoritm' to the 'SHA1'<br>
*}

<hr>
<div class="control-group">
    <label class="control-label" for="user_id">{__("customer_id")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][user_id]" id="user_id" value="{$processor_params.user_id}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="project_id">{__("project_id")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][project_id]" id="project_id" value="{$processor_params.project_id}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="language_id">{__("language")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][language_id]" id="language_id">
            <option value="DE"{if $processor_params.language_id eq "DE"} selected="selected"{/if}>DE</option>
            <option value="en"{if $processor_params.language_id eq "en"} selected="selected"{/if}>EN</option>
            <option value="NL"{if $processor_params.language_id eq "NL"} selected="selected"{/if}>NL</option>
            <option value="FR"{if $processor_params.language_id eq "FR"} selected="selected"{/if}>FR</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="currency_id">{__("currency")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][currency_id]" id="currency_id">
            <option value="EUR"{if $processor_params.currency_id eq "EUR"} selected="selected"{/if}>EUR</option>
            <option value="CHF"{if $processor_params.currency_id eq "CHF"} selected="selected"{/if}>CHF</option>
            <option value="GBP"{if $processor_params.currency_id eq "GBP"} selected="selected"{/if}>GBP</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="project_password">{__("project_password")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][project_password]" id="project_password" value="{$processor_params.project_password}"  size="60">
    </div>
</div>