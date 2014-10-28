{capture name="buttons"}
    <div class="float-right">
        {include file="buttons/button.tpl" but_href="product_features.compare" but_text=__("view_compare_list")}
    </div>
{/capture}
{include file="views/products/components/notification.tpl" product_buttons=$smarty.capture.buttons}