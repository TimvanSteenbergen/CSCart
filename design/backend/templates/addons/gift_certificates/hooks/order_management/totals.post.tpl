{if $cart.use_gift_certificates}
<input type="hidden" name="cert_code" value="" />
    <tr>
        <td class="right muted strong">{__("gift_certificate")}:</td>
        <td class="right">&nbsp;</td>
    </tr>
{foreach from=$cart.use_gift_certificates item="ugc" key="ugc_key"}
    <tr>
        <td class="right nowrap">
            <a href="{"gift_certificates.update?gift_cert_id=`$ugc.gift_cert_id`"|fn_url}">{$ugc_key}</a>
            <a href="{"order_management.delete_use_certificate?gift_cert_code=`$ugc_key`"|fn_url}" class="icon-trash cm-tooltip" title="{__("remove")}"></a>:
        </td>
        <td class="right text-success">-{include file="common/price.tpl" value=$ugc.cost}</td>
    </tr>
{/foreach}
{/if}