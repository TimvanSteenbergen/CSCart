<div class="ty-gift-certificate-validate gift-validate">
    <form name="gift_certificate_verification_form" class="cm-ajax cm-form-dialog-opener cm-dialog-auto-size" action="{""|fn_url}">
        <input type="hidden" name="result_ids" value="gift_cert_verify" />
        <h3 class="ty-gift-certificate-validate__title">{__("certificate_verification")}</h3>
        <div class="ty-input-append">
            {strip}
                <i class="ty-gift-certificate__icon ty-icon-gift"></i>
                <label for="id_verify_code" class="hidden cm-required">{__("promo_code")}</label>
                <input type="text" name="verify_code" id="id_verify_code" value="{__("enter_code")}" class="ty-input-text cm-hint" />
                {include file="buttons/go.tpl" but_name="gift_certificates.verify" alt=__("go")}
            {/strip}
        </div>
    </form>
</div>

<div title="{__("gift_certificate_verification")}" id="gift_cert_verify">
<!--gift_cert_verify--></div>
{script src="js/tygh/tabs.js"}
