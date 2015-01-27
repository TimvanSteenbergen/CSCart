{if $oi.extra.in_use_certificate}
<div>({__("gift_certificate")}:{foreach from=$oi.extra.in_use_certificate item="c" key="c_key" name="f_fciu"}&nbsp;<a href="{"gift_certificates.update?gift_cert_id=`$order_info.use_gift_certificates.$c_key.gift_cert_id`"|fn_url}">{$c_key}</a>{if !$smarty.foreach.f_fciu.last},{/if}{/foreach})</div>
{/if}