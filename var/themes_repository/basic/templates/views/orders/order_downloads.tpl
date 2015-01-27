{if $products}
    <p><a href="{"orders.downloads"|fn_url}">{__("all_downloads")}</a> | <a href="{"orders.details?order_id=`$smarty.request.order_id`"|fn_url}">{__("order")} #{$smarty.request.order_id}</a></p>
    {foreach from=$products item=dp}
    {include file="views/products/download.tpl" product=$dp no_capture=true hide_order=true}
    {/foreach}
{else}
    <p class="no-items">{__("text_downloads_empty")}</p>
{/if}
{capture name="mainbox_title"}{__("downloads")}: {__("order")|lower} #{$smarty.request.order_id}{/capture}