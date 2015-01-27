{if $gift_cert}
    <div class="ty-product-notification__item clearfix">
        {include file="addons/gift_certificates/views/gift_certificates/components/gift_certificates_cart_icon.tpl" width="50" height="50" class="ty-product-notification__image"}
        <div class="ty-product-notification__content clearfix">
            <a href="{"gift_certificates.update?gift_cert_id=`$gift_cert.gift_cert_id`"|fn_url}" class="ty-product-notification__product-name">{__("gift_certificate")}</a>
            <div class="ty-product-notification__price">
            {include file="common/price.tpl" value=$gift_cert.display_subtotal span_id="price_`$gift_cert.gift_cert_id`" class="none"}
            </div>
        </div>
    </div>
{/if}