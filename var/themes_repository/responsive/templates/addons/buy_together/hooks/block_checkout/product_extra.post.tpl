{if $cart.products.$key.extra.buy_together}
    {foreach from=$cart_products item="_product" key="key_conf"}
        {if $cart.products.$key_conf.extra.parent.buy_together == $key}
            {capture name="is_conf_prod"}1{/capture}
        {/if}
    {/foreach}

    {if $smarty.capture.is_conf_prod}
        <div class="ty-discount-info ty-buy-together-info">
            <span class="ty-caret-info"><span class="ty-caret-outer"></span><span class="ty-caret-inner"></span></span>
            <h4 class="ty-buy-together-info__product">{__("buy_together")}</h4>
            <ul class="ty-buy-together-info__items">
                {foreach from=$cart_products item="_product" key="key_conf"}
                    {if $cart.products.$key_conf.extra.parent.buy_together == $key}
                        <li class="ty-buy-together-info__item">{$_product.product nofilter}</li>
                    {/if}
                {/foreach}
            </ul>
        </div>
    {/if}
{/if}