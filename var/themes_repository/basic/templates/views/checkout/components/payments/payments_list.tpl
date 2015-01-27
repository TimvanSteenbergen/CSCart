<div class="other-pay clearfix">
    <ul class="paym-methods">
    {hook name="checkout:payment_method"}
        {foreach from=$payments item="payment"}

            {if $payment_id == $payment.payment_id}
                {$instructions = $payment.instructions}
            {/if}

            <li>
                <input id="payment_{$payment.payment_id}" class="radio valign cm-select-payment" type="radio" name="payment_id" value="{$payment.payment_id}" {if $payment_id == $payment.payment_id}checked="checked"{/if} />

                <div class="radio1">
                    <h5>
                        <label for="payment_{$payment.payment_id}">
                            {if $payment.image}
                                <div>
                                {include file="common/image.tpl" obj_id=$payment.payment_id images=$payment.image image_width=$settings.Thumbnails.product_cart_thumbnail_width image_height=$settings.Thumbnails.product_cart_thumbnail_height}
                                </div>
                            {/if}

                            {$payment.payment}
                        </label>
                    </h5>{$payment.description}
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
    <div class="other-text">
        {$instructions nofilter}
    </div>
</div>
