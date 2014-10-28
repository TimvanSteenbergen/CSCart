<div class="ty-other-pay clearfix">
    <ul class="ty-payments-list">
        {hook name="checkout:payment_method"}
            {foreach from=$payments item="payment"}

                {if $payment_id == $payment.payment_id}
                    {$instructions = $payment.instructions}
                {/if}

                <li class="ty-payments-list__item">
                    <input id="payment_{$payment.payment_id}" class="ty-payments-list__checkbox cm-select-payment" type="radio" name="payment_id" value="{$payment.payment_id}" {if $payment_id == $payment.payment_id}checked="checked"{/if} />

                    <div class="ty-payments-list__item-group">
                        <label for="payment_{$payment.payment_id}" class="ty-payments-list__item-title">
                            {if $payment.image}
                                <div class="ty-payments-list__image">
                                {include file="common/image.tpl" obj_id=$payment.payment_id images=$payment.image image_width=$settings.Thumbnails.product_cart_thumbnail_width image_height=$settings.Thumbnails.product_cart_thumbnail_height}
                                </div>
                            {/if}

                            {$payment.payment}
                        </label>
                        <div class="ty-payments-list__description">
                            {$payment.description}
                        </div>
                    </div>
                </li>

                {if $payment_id == $payment.payment_id}
                    {if $payment.template && $payment.template != "cc_outside.tpl"}
                        <div>
                            {include file=$payment.template}
                        </div>
                    {/if}
                {/if}

            {/foreach}
        {/hook}
    </ul>
    <div class="ty-payments-list__instruction">
        {$instructions nofilter}
    </div>
</div>
