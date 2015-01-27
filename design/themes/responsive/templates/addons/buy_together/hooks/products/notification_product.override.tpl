{if $product.extra.buy_together}
<div class="ty-buy-together-notification ty-product-notification__item clearfix">
{include file="common/image.tpl" image_width="50" image_height="50" images=$product.main_pair no_ids=true class="ty-product-notification__image"}
    <div class="clearfix">
        <a href="{"products.view?product_id=`$product.product_id`"|fn_url}">{$product.product_id|fn_get_product_name}</a>
        <div class="ty-product-notification__price">
            {$product.amount}&nbsp;x&nbsp;{include file="common/price.tpl" value=$product.display_price span_id="price_`$key`" class="none"}
        </div>
        <ul class="ty-buy-together-notification__items">
            {if $product.product_option_data}
                <li class="ty-buy-together-notification__item">{include file="common/options_info.tpl" product_options=$product.product_option_data}</li>
            {/if}
            <li class="ty-buy-together-notification__item"><ul class="ty-buy-together-notification__items">
            {foreach from=$added_products item="_product" key="_key"}
                {if $_product.extra.parent.buy_together == $key}
                    <li class="ty-buy-together-notification__item">
                        <a href="{"products.view?product_id=`$_product.product_id`"|fn_url}" class="ty-buy-together-notification__item-link">{$_product.product_id|fn_get_product_name}</a>
                        <div class="ty-product-notification__price">
                            {$_product.amount}&nbsp;x&nbsp;{include file="common/price.tpl" value=$_product.display_price span_id="price_`$_key`" class="none"}
                        </div>
                    </li>
                    {if $_product.product_option_data}
                        <li class="ty-buy-together-notification__item">{include file="common/options_info.tpl" product_options=$_product.product_option_data}</li>
                    {/if}
                {/if}
            {/foreach}
            </ul></li>
        </ul>
    </div>
</div>
{elseif $product.extra.parent.buy_together}
    &nbsp;
{/if}