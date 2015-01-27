{if $completed_steps.step_two}
    <div class="ty-order-info">
        {assign var="profile_fields" value="I"|fn_get_profile_fields}
        
        {if $profile_fields.B}
            <h4 class="ty-order-info__title">{__("billing_address")}:</h4>

            <ul id="tygh_billing_adress" class="ty-order-info__profile-field clearfix">
                {foreach from=$profile_fields.B item="field"}
                    {assign var="value" value=$cart.user_data|fn_get_profile_field_value:$field}
                    {if $value}
                        <li class="ty-order-info__profile-field-item {$field.field_name|replace:"_":"-"}">{$value}</li>
                    {/if}
                {/foreach}
            </ul>

            <hr class="shipping-adress__delim" />
        {/if}

        {if $profile_fields.S}
            <h4 class="ty-order-info__title">{__("shipping_address")}:</h4>
            <ul id="tygh_shipping_adress" class="ty-order-info__profile-field clearfix">
                {foreach from=$profile_fields.S item="field"}
                    {assign var="value" value=$cart.user_data|fn_get_profile_field_value:$field}
                    {if $value}
                        <li class="ty-order-info__profile-field-item {$field.field_name|replace:"_":"-"}">{$value}</li>
                    {/if}
                {/foreach}
            </ul>
            <hr class="shipping-adress__delim" />
        {/if}

        {if !$cart.shipping_failed && !empty($cart.chosen_shipping) && $cart.shipping_required}
            <h4>{__("shipping_method")}:</h4>
            <ul id="tygh_shipping_method">
                {foreach from=$cart.chosen_shipping key="group_key" item="shipping_id"}
                    <li>{$product_groups[$group_key].shippings[$shipping_id].shipping}</li>
                {/foreach}
            </ul>
        {/if}
    </div>
{/if}
{assign var="block_wrap" value="checkout_order_info_`$block.snapping_id`_wrap" scope="parent"}
