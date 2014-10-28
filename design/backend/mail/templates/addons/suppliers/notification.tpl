{__("dear_sirs")},<br /><br />

{if $status_inventory == 'D'}
{__("supplier_email_header")}<br /><br />
{/if}

<b>{__("invoice")}:</b><br>

{include file="addons/suppliers/invoice.tpl"}

{__("contact_information")}:<br /><br />
<span style="margin-left:20px;">&nbsp;</span>{$supplier.data.name}<br />
<span style="margin-left:20px;">&nbsp;</span>{if $supplier.data.address}{$supplier.data.address}, {/if}
                  {if $supplier.data.zipcode}{$supplier.data.zipcode}, {/if}
                  {if $supplier.data.city}{$supplier.data.city}, {/if}
                  {if $supplier.data.state && $supplier.data.country}{$supplier.data.state|fn_get_state_name:$supplier.data.country}, {/if}
                  {$supplier.data.country|fn_get_country_name}<br />
<span style="margin-left:20px;">&nbsp;</span>{if $supplier.data.phone}{__("phone")}:&nbsp;{$supplier.data.phone}{if $supplier.data.fax}, {/if}{/if}{if $supplier.data.fax}{__("fax")}:&nbsp;{$supplier.data.fax}{/if}.<br />
<span style="margin-left:20px;">&nbsp;</span>{__("email")}:&nbsp;{$supplier.data.email}