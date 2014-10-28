{if $cart.use_gift_certificates}
    {foreach from=$cart.use_gift_certificates item="ugc" key="ugc_key"}
        <tr>
            <td class="ty-checkout-summary__item">
                <a href="{"gift_certificates.verify?verify_code=`$ugc_key`"|fn_url}">{$ugc_key}</a>
                {if $settings.General.checkout_style != "multi_page"}{include file="addons/gift_certificates/views/gift_certificates/components/delete_button.tpl" code=$ugc_key}
                {/if}
            </td>
            <td class="ty-checkout-summary__item ty-right">
                -{include file="common/price.tpl" value=$ugc.cost}
            </td>
        </tr>
    {/foreach}
{/if}