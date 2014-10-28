{if $return_info.extra.gift_certificates}
    {capture name="url"}gift_certificates.manage?{foreach from=$return_info.extra.gift_certificates item="gift_cert" key="gift_cert_key"}gift_cert_ids[]={$gift_cert_key}&{/foreach}{/capture}
    &nbsp;|&nbsp;{include file="buttons/button.tpl" but_text=__("related_gift_cert") but_href=$smarty.capture.url but_role="tool"}
{/if}