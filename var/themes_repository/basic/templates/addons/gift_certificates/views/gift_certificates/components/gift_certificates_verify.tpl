<div class="gift-validate code-input discount-coupon">
<form name="gift_certificate_verification_form" class="cm-ajax cm-form-dialog-opener cm-dialog-auto-size" action="{""|fn_url}">
    <input type="hidden" name="result_ids" value="gift_cert_verify" />
    <h4>{__("certificate_verification")}</h4>
    <div class="control-group input-append">
        <label for="id_verify_code" class="cm-required hidden">{__("enter_code")}</label>
        {strip}
            <i class="icon-gift"></i>
            <input type="text" name="verify_code" id="id_verify_code" value="{__("enter_code")}" class="input-text cm-hint" />
            {include file="buttons/go.tpl" but_name="gift_certificates.verify" alt=__("go")}
        {/strip}
    </div>
</form>
</div>

<div title="{__("gift_certificate_verification")}" id="gift_cert_verify">
<!--gift_cert_verify--></div>
{script src="js/tygh/tabs.js"}
