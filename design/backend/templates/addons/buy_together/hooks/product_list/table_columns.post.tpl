<input type="hidden" id="price_{$product.product_id}" value="{$product.base_price}" />

{foreach from=$product.product_options key="option_id" item="option"}
    {foreach from=$option.variants key="variant_id" item="variant"}
        {if $variant.modifier != 0}
            {if $variant.modifier_type == "A"}
                {assign var="op_modifier" value=$variant.modifier}
            {else}
                {math equation="(price / 100) * modifier" price=$product.base_price modifier=$variant.modifier assign="op_modifier"}
            {/if}
            <input type="hidden" id="bt_option_modifier_{$option_id}_{$variant_id}" value="{$op_modifier}" />
        {/if}
    {/foreach}
{/foreach}