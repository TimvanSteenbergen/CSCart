{if $is_wishlist}
<div class="ty-twishlist-item">
    <a href="{"wishlist.delete?cart_id=`$product.cart_id`"|fn_url}" class="ty-twishlist-item__remove ty-remove" title="{__("remove")}"><i class="ty-remove__icon ty-icon-cancel-circle"></i><span class="ty-twishlist-item__txt ty-remove__txt">{__("remove")}</span></a>
</div>
{/if}