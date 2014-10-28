{include file="common/letter_header.tpl"}

{__("hello")},<br /><br />

{__("text_company_status_new_to_disable", ["[company]" => $company_data.company_name])}

<br /><br />

{if $reason}
{__("reason")}: {$reason}
<br /><br />
{/if}

{include file="common/letter_footer.tpl"}