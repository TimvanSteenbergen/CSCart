{if $status == "Y"}
    {assign var="text_status" value=__("approved")}
{else}
    {assign var="text_status" value=__("disapproved")}
{/if}

{include file="common/letter_header.tpl"}

{__("hello")},<br /><br />

{if $products|count > 1}
    {if $status == "Y"}
        {__("products_approval_status_approved")}<br />
    {else}
        {__("products_approval_status_disapproved")}<br />
    {/if}
    {foreach name="products_list" from=$products item="product"}
        {$smarty.foreach.products_list.iteration}) <a href="{"products.update?product_id=`$product.product_id`"|fn_url:"V":"http"}">{$product.product}</a><br />
    {/foreach}
    
    {if $status == "Y"}
        <br />{__("text_shoppers_can_order_products")}
    {/if}
    {if $reason}
        <p>{$reason}</p>
    {/if}
{else}
    {assign var="product_name" value=$products.0.product}
    {assign var="product_url" value="products.update?product_id=`$products.0.product_id`"|fn_url:"V":"http"}
    {if $status == "Y"}
        {__("product_approval_status_approved", ["[product]" => "<a href='`$product_url`'>`$product_name`</a>"])}
    {else}
        {__("product_approval_status_disapproved", ["[product]" => "<a href='`$product_url`'>`$product_name`</a>"])}
    {/if}
    
    {if $reason}
        <p>{$reason}</p>
    {/if}
{/if}

{include file="common/letter_footer.tpl"}