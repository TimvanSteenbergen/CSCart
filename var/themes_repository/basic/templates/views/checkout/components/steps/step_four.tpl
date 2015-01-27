{script src="js/tygh/tabs.js"}

<div class="step-container{if $edit}-active{/if} step-four" data-ct-checkout="billing_options" id="step_four">
    {if $settings.General.checkout_style != "multi_page"}
        <h2 class="step-title{if $edit}-active{/if} clearfix">
            <span class="float-left">{if $profile_fields.B || $profile_fields.S}4{else}3{/if}</span>
            
            {hook name="checkout:edit_link_title"}
            <a class="title{if $complete && !$edit} cm-ajax{/if}" {if $complete && !$edit}href="{"checkout.checkout?edit_step=step_four&from_step=`$edit_step`"|fn_url}" data-ca-target-id="checkout_*"{/if}>{__("billing_options")}</a>
            {/hook}
        </h2>
    {/if}

    <div id="step_four_body" class="step-body{if $edit}-active{/if} {if !$edit}hidden{/if}">
        <div class="clearfix">
            
            {if $cart|fn_allow_place_order}
                {if $edit}
                    <div class="clearfix">

                        {if $cart.payment_id}
                            {include file="views/checkout/components/payments/payment_methods.tpl" payment_id=$cart.payment_id}
                        {else}
                            <div class="checkout-inside-block"><h2 class="subheader">{__("text_no_payments_needed")}</h2></div>

                            <form name="paymens_form" action="{""|fn_url}" method="post">
                                <div class="checkout-buttons">
                                    {include file="buttons/place_order.tpl" but_text=__("submit_my_order") but_name="dispatch[checkout.place_order]" but_role="big" but_id="place_order"}    
                                </div>
                            </form>
                        {/if}
                    </div>
                {/if}

            {else}
                {if $cart.shipping_failed}
                    <p class="error-text center">{__("text_no_shipping_methods")}</p>
                {/if}

                {if $cart.amount_failed}
                    <div class="checkout-inside-block">
                        <p class="error-text">{__("text_min_order_amount_required")}&nbsp;<strong>{include file="common/price.tpl" value=$settings.General.min_order_amount}</strong></p>
                    </div>
                {/if}

                <div class="checkout-buttons">
                    {include file="buttons/continue_shopping.tpl" but_href=$continue_url|fn_url but_role="action"}
                </div>
                
            {/if}
        </div>
    </div>
<!--step_four--></div>

<div id="place_order_data" class="hidden">
</div>