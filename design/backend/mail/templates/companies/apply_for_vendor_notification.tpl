{assign var="company_update_url" value="companies.update?company_id=`$company_id`"|fn_url:'A':'http':$smarty.const.CART_LANGUAGE:true}
{__("vendor_candidate_notification", ["<a>" => "<a href=$company_update_url>"])}

<br/><br/>

<table>
    <tr>
        <td class="form-field-caption" nowrap>{__("company_name")}:&nbsp;</td>
        <td >{$company.company}</td>
    </tr>
    {if $company.company_description}
    <tr>
        <td class="form-field-caption" nowrap>{__("description")}:&nbsp;</td>
        <td >{$company.company_description}</td>
    </tr>
    {/if}
    {if $company.request_account_name}
    <tr>
        <td class="form-field-caption" nowrap>{__("account_name")}:&nbsp;</td>
        <td >{$company.request_account_name}</td>
    </tr>
    {/if}
    {if $company.admin_firstname}
    <tr>
        <td class="form-field-caption" nowrap>{__("first_name")}:&nbsp;</td>
        <td >{$company.admin_firstname}</td>
    </tr>
    {/if}
    {if $company.admin_lastname}
    <tr>
        <td class="form-field-caption" nowrap>{__("last_name")}:&nbsp;</td>
        <td >{$company.admin_lastname}</td>
    </tr>
    {/if}
    <tr>
        <td class="form-field-caption" nowrap>{__("email")}:&nbsp;</td>
        <td >{$company.email}</td>
    </tr>
    <tr>
        <td class="form-field-caption" nowrap>{__("phone")}:&nbsp;</td>
        <td >{$company.phone}</td>
    </tr>
    <tr>
    {if $company.url}
        <td class="form-field-caption" nowrap>{__("url")}:&nbsp;</td>
        <td >{$company.url}</td>
    </tr>
    {/if}
    {if $company.fax}
    <tr>
        <td class="form-field-caption" nowrap>{__("fax")}:&nbsp;</td>
        <td >{$company.fax}</td>
    </tr>
    {/if}
    <tr>
        <td class="form-field-caption" nowrap>{__("address")}:&nbsp;</td>
        <td >{$company.address}</td>
    </tr>
    <tr>
        <td class="form-field-caption" nowrap>{__("city")}:&nbsp;</td>
        <td >{$company.city}</td>
    </tr>
    <tr>
        <td class="form-field-caption" nowrap>{__("country")}:&nbsp;</td>
        <td >{$company.country}</td>
    </tr>
    <tr>
        <td class="form-field-caption" nowrap>{__("state")}:&nbsp;</td>
        <td >{$company.state}</td>
    </tr>
    <tr>
        <td class="form-field-caption" nowrap>{__("zip_postal_code")}:&nbsp;</td>
        <td >{$company.zipcode}</td>
    </tr>
</table>