{if $user_data.user_type == "C"}
    <li><a class="tool-link" href="{"gift_certificates.add?user_id=`$id`"|fn_url}">{__("create_gift_certificate_for_customer")}</a></li>
{/if}
