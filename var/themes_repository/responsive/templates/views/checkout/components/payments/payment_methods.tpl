{script src="js/tygh/tabs.js"}

{hook name="checkout:payment_method_check"}
    {if $order_id}
        {$url = "orders.details?order_id=`$order_id`"}
        {$result_ids = "elm_payments_list"}
    {else}
        {$url = "checkout.checkout"}
        {$result_ids = "checkout*,step_four"}
    {/if}
{/hook}

<script type="text/javascript">
(function(_, $) {
    $(_.doc).on('click', '.cm-select-payment', function() {
        var self = $(this);

        $.ceAjax('request', fn_url('{$url}&payment_id=' + self.val()), {
            result_ids: '{$result_ids}',
            full_render: true
        });
    });

    $(_.doc).ready(function() {
        $('.payments-form').each(function(index, form) {
            $.ceEvent('on', 'ce.formpost_' + $(form).attr('name'), function() {
                $('#ajax_overlay').show();
                $('#ajax_loading_box').html('<span class="ty-ajax-loading-box-with__text-wrapper">{__("placing_order")}</span>').addClass('ty-ajax-loading-box_text_block');
                $.toggleStatusBox('show');
            });
        });
    });
}(Tygh, Tygh.$));
</script>


{if $payment_methods|count > 1}
<div class="ty-tabs cm-j-tabs cm-track cm-j-tabs-disable-convertation clearfix">
    <ul class="ty-tabs__list" id="payment_tabs">
        {foreach from=$payment_methods key="tab_id" item="payments"}
            {assign var="tab_name" value="payments_`$tab_id`"}
            {if $tab_id == $active_tab || (!$active_tab && $payments[$payment_id])}
                {$active = $tab_id}
            {/if}
            {$first_payment = $payments|reset}

            <li id="payments_{$tab_id}" class="ty-tabs__item{if $tab_id == $active_tab || (!$active_tab && $payments[$payment_id])} active{/if}">
                <a class="ty-tabs__a cm-ajax cm-ajax-full-render" data-ca-target-id="{$result_ids}" href="{"`$url`?active_tab=`$tab_id`&payment_id=`$first_payment.payment_id`"|fn_url}">{__($tab_name)}</a>
            </li>
        {/foreach}
    </ul>
</div>
{/if}

<div class="cm-tabs-content tabs-content clearfix">
    {foreach from=$payment_methods key="tab_id" item="payments"}
        <div class="{if $active != $tab_id && $payment_methods|count > 1}hidden{/if}" id="content_payments_{$tab_id}">
            <form name="payments_form_{$tab_id}" action="{""|fn_url}" method="post" class="payments-form">
            <input type="hidden" name="payment_id" value="{$payment_id}" />
            <input type="hidden" name="result_ids" value="{$result_ids}" />

            {if $order_id}
                <input type="hidden" name="order_id" value="{$order_id}" />
            {else}
                <div class="ty-checkout__billing-options {if $payment_methods|count == 1}ty-notab{/if}">
            {/if}

                {if $payments|count == 1}
                    {assign var="payment" value=$payments|reset}
                    
                    {if $payment.template}
                        {capture name="payment_template"}
                            {if $payment.image}
                                <div class="clearfix">
                                    {include file="common/image.tpl" obj_id=$payment.payment_id images=$payment.image image_width=$settings.Thumbnails.product_cart_thumbnail_width image_height=$settings.Thumbnails.product_cart_thumbnail_height}
                                </div>
                            {/if}
                            
                            {if $payment.description}
                                <div class="ty-payments-list__description">
                                    {$payment.description}
                                </div>
                            {/if}

                            {include file=$payment.template card_id=$payment.payment_id}
                        {/capture}
                    {/if}

                    {if $payment.template && $smarty.capture.payment_template|trim != ""}
                        <div class="clearfix">
                            <div class="ty-payments-list__instruction other-text">{$payment.instructions nofilter}</div>
                            {$smarty.capture.payment_template nofilter}
                        </div>
                    {else}
                        {include file="views/checkout/components/payments/payments_list.tpl" payments=[$payment]}
                    {/if}

                {else}
                    {include file="views/checkout/components/payments/payments_list.tpl"}
                {/if}
                
                {if $order_id}
                    {include file="views/checkout/components/customer_notes.tpl"}

                    <div class="ty-checkout-buttons">
                        {if $payment_method.params.button}
                            {$payment_method.params.button}
                        {else}
                            <div class="ty-repay-button">
                                    {include file="buttons/place_order.tpl" but_text=__("repay_order") but_name="dispatch[orders.repay]" but_role="big"}
                            </div>
                        {/if}
                    </div>

                {else}
                    {include file="views/checkout/components/terms_and_conditions.tpl" suffix=$tab_id}

                    {assign var="show_checkout_button" value=false}
                    {foreach from=$payments item="payment"}
                        {if $payment_id == $payment.payment_id && $checkout_buttons[$payment_id]}
                            {assign var="show_checkout_button" value=true}
                        {/if}
                    {/foreach}

                    {if $auth.act_as_user}
                        <div class="ty-control-group">
                            <label>
                                <input type="checkbox" id="skip_payment" name="skip_payment" value="Y" class="checkbox" />
                            {__("skip_payment")}
                            </label>
                        </div>
                    {/if}

                    {hook name="checkout:extra_payment_info"}
                    {/hook}
                    </div>

                    {if $iframe_mode}
                        <div class="ty-payment-method-iframe__box">
                            <iframe width="100%" height="700" id="order_iframe_{$smarty.const.TIME}" src="{"checkout.process_payment"|fn_checkout_url:$smarty.const.AREA}" style="border: 0px" frameBorder="0" ></iframe>
                            {if $cart_agreements || $settings.General.agree_terms_conditions == "Y"}
                            <div id="payment_method_iframe{$tab_id}" class="ty-payment-method-iframe">
                                <div class="ty-payment-method-iframe__label">
                                    <div class="ty-payment-method-iframe__text">{__("checkout_terms_n_conditions_alert")}</div>
                                </div>
                            </div>
                            {/if}
                        </div>
                    {else}
                        <div class="ty-checkout-buttons ty-checkout-buttons__submit-order">
                            {if !$show_checkout_button}
                                {include file="buttons/place_order.tpl" but_text=__("submit_my_order") but_name="dispatch[checkout.place_order]" but_role="big" but_id="place_order_`$tab_id`"}
                            {else}
                                {$checkout_buttons[$payment_id] nofilter}
                            {/if}
                        </div>
                    {/if}
                {/if}

                <div class="processor-buttons hidden"></div>
            </form>

        <!--content_payments_{$tab_id}--></div>
    {/foreach}
</div>
