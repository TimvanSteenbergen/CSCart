{include file="common/letter_header.tpl"}

{__("hello")},<br /><br />

{__("text_company_status_new_to_pending", ["[company]" => $company_data.company_name])}

<br /><br />

{if $reason}
{__("reason")}: {$reason}
<br /><br />
{/if}

{assign var="vendor_area" value=""|fn_url:"V":"http"}
{if $e_account == 'updated'}
{__("text_company_status_new_to_active_administrator_updated", ["[link]" => $vendor_area, "[login]" => $e_username])}
{elseif $e_account == 'new'}
{__("text_company_status_new_to_active_administrator_created", ["[link]" => $vendor_area, "[login]" => $e_username, "[password]" => $e_password])}
{/if}

{include file="common/letter_footer.tpl"}