{if $cart.use_gift_certificates}
<input type="hidden" name="cert_code" value="" />
{foreach from=$cart.use_gift_certificates item="ugc" key="ugc_key"}
    <tr>
        <td class="right nowrap"><a href="{"order_management.delete_use_certificate?gift_cert_code=`$ugc_key`"|fn_url}"><i class="icon-trash cm-tooltip" title="{__("delete")}"></i></a>&nbsp;<span>{__("gift_certificate")}</span>&nbsp;(<a href="{"gift_certificates.update?gift_cert_id=`$ugc.gift_cert_id`"|fn_url}">{$ugc_key}</a>)&nbsp;<span>:</span></td>
        <td>&nbsp;</td>
        <td class="right nowrap">{include file="common/price.tpl" value=$ugc.cost}</td>
    </tr>
{/foreach}
{/if}