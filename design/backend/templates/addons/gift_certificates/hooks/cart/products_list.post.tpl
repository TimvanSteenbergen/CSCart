{if $product.item_type == "G"}
    {__("gift_certificate")}
{/if}
{if $product.item_type == "C"}
    <a href="{"products.update?product_id=`$product.product_id`"|fn_url}">{$product.product nofilter}</a>
{/if}