{if $order_info.use_gift_certificates}
{if $order_info.payment_id == 0}
    {include file="common/subheader.tpl" title=__("payment_information")}
{/if}
    <tr>
        <td class="right muted strong">{__("gift_certificate")}</td>
        <td>&nbsp;</td>
    </tr>
    {foreach from=$order_info.use_gift_certificates item="certificate" key="code"}
        <tr>
            <td><a href="{"gift_certificates.update?gift_cert_id=`$certificate.gift_cert_id`"|fn_url}">{$code}</a></td>
            <td class="right text-success">-{include file="common/price.tpl" value=$certificate.cost}</td>
        </tr>
    {/foreach}
{/if}