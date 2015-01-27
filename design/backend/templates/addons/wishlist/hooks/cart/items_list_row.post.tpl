{assign var="products" value=$wishlist_products}
{assign var="show_price" value=false}

<td colspan="2" class="row-more-body row-gray top">
    {assign var="wishlist_products_js_id" value="wishlist_products_`$customer.user_id`"}
    {if "ULTIMATE"|fn_allowed_for}
        {assign var="wishlist_products_js_id" value="`$wishlist_products_js_id`_`$customer.company_id`"}
    {/if}
    <div id="{$wishlist_products_js_id}">
    {if $customer.user_id == $sl_user_id}
        {if $wishlist_products}
        <table width="100%" class="table table-condensed">
        <thead>
        <tr class="no-hover">
            <th>{__("wishlist_products")}</th>
        </tr>
        </thead>
        {foreach from=$wishlist_products item="product" name="products"}
        {hook name="cart:product_row"}
        {if !$product.extra.extra.parent}
        <tr>
            <td>
            {if $product.item_type == "P"}
                {if $product.product}
                <a href="{"products.update?product_id=`$product.product_id`"|fn_url}">{$product.product nofilter}</a>
                {else}
                {__("deleted_product")}
                {/if}
            {/if}
            {hook name="cart:products_list"}
            {/hook}
            </td>
        </tr>
        {/if}
        {/hook}
        {/foreach}
        </table>
        {/if}
    {/if}
    <!--{$wishlist_products_js_id}--></div>
</td>