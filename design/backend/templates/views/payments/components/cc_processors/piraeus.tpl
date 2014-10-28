{assign var="website_url" value="payment_notification.index_redirect?payment=piraeus"|fn_url:"C":"http"}
{assign var="referrer_url" value="payment_notification.index_redirect?payment=piraeus"|fn_url:"C":"http"}
{assign var="success_url" value="payment_notification.notify?payment=piraeus"|fn_url:"C":"http"}
{assign var="failure_url" value="payment_notification.notify?payment=piraeus"|fn_url:"C":"http"}
{assign var="backlink_url" value="payment_notification.cancel?payment=piraeus"|fn_url:"C":"http"}

{assign var="ip_address" value=$smarty.server.SERVER_ADDR}
{assign var="response_method" value="POST"}

<p>{__("text_piraeus_notice", ["[website_url]" => $website_url, "[referrer_url]" => $referrer_url, "[success_url]" => $success_url, "[failure_url]" => $failure_url, "[backlink_url]" => $backlink_url, "[ip_address]" => $ip_address, "[response_method]" => $response_method])}</p>
<hr>

<div class="control-group">
    <label class="control-label" for="acquirerid">{__("acquirerid")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][acquirerid]" id="acquirerid" value="{$processor_params.acquirerid}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="merchantid">{__("merchantid")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][merchantid]" id="merchantid" value="{$processor_params.merchantid}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="posid">{__("posid")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][posid]" id="posid" value="{$processor_params.posid}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="username">{__("username")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][username]" id="username" value="{$processor_params.username}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="password">{__("password")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][password]" id="password" value="{$processor_params.password}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="requesttype">{__("requesttype")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][requesttype]" id="requesttype">
            <option value="02" {if $processor_params.requesttype == "02"}selected="selected"{/if}>{__("sale")}</option>
            <option value="00" {if $processor_params.requesttype == "00"}selected="selected"{/if}>{__("preauthorization")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="expirepreauth">{__("expirepreauth")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][expirepreauth]" id="expirepreauth" value="{$processor_params.expirepreauth}"  size="60">
        <p><small>{__("expirepreauth_description")}</small></p>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="currencycode">{__("currencycode")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][currencycode]" id="currencycode">
            <option value="978" {if $processor_params.currencycode == "978"}selected="selected"{/if}>{__("currency_code_eur")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="languagecode">{__("language")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][languagecode]" id="languagecode">
            <option value="el-GR" {if $processor_params.languagecode == "el-GR"}selected="selected"{/if}>{__("greek")}</option>
            <option value="en-US" {if $processor_params.languagecode == "en-US"}selected="selected"{/if}>{__("english")}</option>
        </select>
    </div>
</div>
