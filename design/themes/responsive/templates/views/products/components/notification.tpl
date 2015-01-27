{* NOTE: This template doesn\'t used for direct display
   It will store in the session and then display into notification box
   ---------------------------------------------------------------
   So, it is STRONGLY recommended to use strip tags in such templates
*}
{strip}
<div class="ty-product-notification__body cm-notification-max-height">
    {include file="views/products/components/product_notification_items.tpl"}
    {$product_info nofilter}
</div>
<div class="ty-product-notification__buttons clearfix">
    {$product_buttons nofilter}
</div>
{/strip}