{if !$smarty.session.auth.age && $product.age_verification == "Y"}
<div class="product-container clearfix">
    <div class="product-description">
        <a href="{"products.view?product_id=`$product.product_id`"|fn_url}" class="product-title">{$product.product nofilter}</a>
    </div>
    <div class="box margin-top">
        {__("product_need_age_verification")}
        <div class="buttons-container">
            {include file="buttons/button.tpl" but_text=__("verify") but_href="products.view?product_id=`$product.product_id`" but_role="text"}
        </div>
    </div>
</div>
{/if}