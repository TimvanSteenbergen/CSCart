{if $completed_steps.step_two}
    {assign var="profile_fields" value="I"|fn_get_profile_fields}
    
    {if $profile_fields.B}
        <h4>{__("billing_address")}:</h4>

        <ul id="tygh_billing_adress" class="shipping-adress clearfix">
            {foreach from=$profile_fields.B item="field"}
                {assign var="value" value=$cart.user_data|fn_get_profile_field_value:$field}
                {if $value}
                    <li class="{$field.field_name|replace:"_":"-"}">{$value}</li>
                {/if}
            {/foreach}
        </ul>

        <hr />
    {/if}

    {if $profile_fields.S}
        <h4>{__("shipping_address")}:</h4>
        <ul id="tygh_shipping_adress" class="shipping-adress clearfix">
            {foreach from=$profile_fields.S item="field"}
                {assign var="value" value=$cart.user_data|fn_get_profile_field_value:$field}
                {if $value}
                    <li class="{$field.field_name|replace:"_":"-"}">{$value}</li>
                {/if}
            {/foreach}
        </ul>
        <hr />
    {/if}

    {if !$cart.shipping_failed && !empty($cart.chosen_shipping) && $cart.shipping_required}
        <h4>{__("shipping_method")}:</h4>
        <ul id="tygh_shipping_method">
            {foreach from=$cart.chosen_shipping key="group_key" item="shipping_id"}
                <li>{$product_groups[$group_key].shippings[$shipping_id].shipping}</li>
            {/foreach}
        </ul>
    {/if}

{/if}
{assign var="block_wrap" value="checkout_order_info_`$block.snapping_id`_wrap" scope="parent"}
