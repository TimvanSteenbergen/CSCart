{script src="js/tygh/exceptions.js"}

<script type="text/javascript">
(function(_, $) {
    $.ceEvent('on', 'ce.commoninit', function(context) {
        var elm = context.find('.ty-step__title-active');
        if (elm.length) {
            $.scrollToElm(elm);
        }
    });
}(Tygh, Tygh.$));
</script>

{if $settings.General.checkout_style == "multi_page"}
    {if $cart_products}
        {include file="views/checkout/components/progressbar.tpl"}
    {/if}

    {include file="views/checkout/components/checkout_steps.tpl"}
    {capture name="mainbox_title"}<span class="ty-checkout__title ty-classic-checkout__title">{__("secure_checkout")}&nbsp;<i class="ty-checkout__title-icon ty-icon-lock"></i></span>{/capture}
{else}
    {$smarty.capture.checkout_error_content nofilter}
    {include file="views/checkout/components/checkout_steps.tpl"}

    {capture name="mainbox_title"}<span class="ty-checkout__title">{__("secure_checkout")}&nbsp;<i class="ty-checkout__title-icon ty-icon-lock"></i></span>{/capture}
{/if}
