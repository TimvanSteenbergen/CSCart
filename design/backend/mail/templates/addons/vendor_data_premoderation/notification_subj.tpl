{if $status == "Y"}
    {assign var="text_status" value=__("approved")}
{else}
    {assign var="text_status" value=__("disapproved")}
{/if}

{$company_data.company_name}: {__("products_approval_status_changed", ["[status]" => $text_status])}