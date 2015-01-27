{if $cart.gift_certificates}
    {foreach from=$cart.gift_certificates item="certificate"}
        <tr>
            <td>&nbsp;</td>
            <td>
                {__("gift_certificate")}: <a href="{"gift_certificates.update?gift_cert_id=`$certificate.gift_cert_id`"|fn_url}">{$certificate.gift_cert_code}</a>
            </td>
            <td>
                {include file="common/price.tpl" value=$certificate.display_subtotal}
            </td>
        </tr>
    {/foreach}
{/if}