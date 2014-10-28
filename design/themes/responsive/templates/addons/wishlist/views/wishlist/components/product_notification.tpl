{capture name="buttons"}
    <div class="ty-float-right">
        {include file="buttons/button.tpl" but_href="wishlist.view" but_text=__("view_wishlist") but_meta="ty-btn__secondary"}
    </div>
{/capture}
{include file="views/products/components/notification.tpl" product_buttons=$smarty.capture.buttons}