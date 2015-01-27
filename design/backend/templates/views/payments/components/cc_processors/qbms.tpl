{assign var="r_url" value="https"|fn_payment_url:"qbms_response.php"}
<p>{__("text_qbms_notice", ["[response_url]" => $r_url])}</p>
<hr>

<div class="control-group">
    <label class="control-label" for="app_login">{__("application_login")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][app_login]" id="app_login" value="{$processor_params.app_login}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="app_id">{__("application_id")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][app_id]" id="app_id" value="{$processor_params.app_id}" >
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="certificate_filename">{__("certificate_filename")}:</label>
    <div class="controls" id="certificate_file">
        {if $processor_params.certificate_filename}
            <div class="text-type-value pull-left">
                {$processor_params.certificate_filename}
                <a href="{'payments.delete_certificate?payment_id='|cat:$payment_id|fn_url}" class="cm-ajax" data-ca-target-id="certificate_file">
                    <i class="icon-remove-sign cm-tooltip hand" title="{__('remove')}"></i>
                </a>
            </div>
        {/if}

        <div {if $processor_params.certificate_filename}class="clear"{/if}>{include file="common/fileuploader.tpl" var_name="payment_certificate[]"}</div>
    <!--certificate_file--></div>
</div>

<div class="control-group">
    <label class="control-label" for="p">{__("connection_ticket")}:</label>
    <div class="controls">
        <input type="hidden" name="payment_data[processor_params][connection_ticket]" value="{$processor_params.connection_ticket}">
        <input type="text" name="p" id="p" value="{$processor_params.connection_ticket}"  size="60" disabled="disabled">
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

<div class="control-group">
    <label class="control-label" for="order_prefix">{__("order_prefix")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][order_prefix]" id="order_prefix" value="{$processor_params.order_prefix}" >
    </div>
</div>