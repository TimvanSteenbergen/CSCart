{script src="js/tygh/exceptions.js"}

<script type="text/javascript">
//<![CDATA[
(function(_, $) {
    $.ceEvent('on', 'ce.commoninit', function(context) {
        var elm = context.find('.step-title-active');
        if (elm.length) {
            $.scrollToElm(elm);
        }
    });
}(Tygh, Tygh.$));
//]]>
</script>

{if $settings.General.checkout_style == "multi_page"}
    {if $cart_products}
    {include file="views/checkout/components/progressbar.tpl"}
    {/if}

    {include file="views/checkout/components/checkout_steps.tpl"}
    {capture name="mainbox_title"}<span class="secure-page-title classic-checkout-title">{__("secure_checkout")}<i class="icon-lock"></i></span>{/capture}
{else}
    {$smarty.capture.checkout_error_content nofilter}
    <a name="checkout_top"></a>
    {include file="views/checkout/components/checkout_steps.tpl"}

    {capture name="mainbox_title"}<span class="secure-page-title">{__("secure_checkout")}<i class="icon-lock"></i></span>{/capture}
{/if}
