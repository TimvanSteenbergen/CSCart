<div class="control-group input-append">
    <label for="coupon_field{$position}" class="hidden cm-required">{__("promo_code")}</label>
    <input type="text" class="input-text cm-hint" id="coupon_field{$position}" name="coupon_code" size="40" value="{__("promo_code_or_certificate")}" />
    {include file="buttons/go.tpl" but_name="checkout.apply_coupon" alt=__("apply")}
</div>