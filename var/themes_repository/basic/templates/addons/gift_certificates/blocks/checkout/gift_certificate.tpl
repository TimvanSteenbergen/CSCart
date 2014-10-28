<div class="coupons-container">
    <div class="code-input discount-coupon">
        <div class="code-input gift-certificate">
            <form method="post" action="{""|fn_url}" name="gift_certificate_payment_form">
                <input type="hidden" value="cart" name="redirect_mode">
                <input type="hidden" value="checkout_steps,cart_status*,checkout_cart" name="result_ids">
                <div class="control-group">
                    <input type="text" value="" size="40" name="gift_cert_code" class="input-text" id="gc_field">
                    <input type="submit" value="" name="dispatch[checkout.apply_certificate]" class="hidden">
                    <span class="code-button">    
                    <a data-ca-target-form="gift_certificate_payment_form" data-ca-dispatch="dispatch[checkout.apply_certificate]" class="cm-submit text-button">__("apply")</a></span>
                </div>
            </form>
        </div>
    </div>
</div>