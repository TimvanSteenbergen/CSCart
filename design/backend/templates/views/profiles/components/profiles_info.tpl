{assign var="profile_fields" value=$location|fn_get_profile_fields}

{* billing_address *}
{capture name="billing_address"}
    {if $user_data.b_firstname || $user_data.b_lastname || $user_data.b_address || $user_data.b_address_2 || $user_data.b_city || $user_data.b_country_descr || $user_data.b_state_descr || $user_data.b_zipcode || $profile_fields.B}

        <h4>{__("billing_address")}</h4>

        {if $profile_fields.B}
            {if $user_data.b_firstname || $user_data.b_lastname}
                <p class="strong">{$user_data.b_firstname} {$user_data.b_lastname}</p>
            {/if}
            {if $user_data.b_address}
                <p>{$user_data.b_address}</p>
            {/if}
            {if $user_data.b_address_2}
                <p>{$user_data.b_address_2}</p>
            {/if}
            {if $user_data.b_city || $user_data.b_state_descr || $user_data.b_zipcode}
                <p>{$user_data.b_city}{if $user_data.b_city && ($user_data.b_state_descr || $user_data.b_zipcode)},{/if} {$user_data.b_state_descr} {$user_data.b_zipcode}</p>
            {/if}
            {if $user_data.b_country_descr}<p>{$user_data.b_country_descr}</p>{/if}
            {include file="views/profiles/components/profile_fields_info.tpl" fields=$profile_fields.B}
            {if $user_data.b_phone}
                <p>{$user_data.b_phone}</p>
            {/if}
        {/if}
    {/if}
{/capture}

{* billing address *}
{capture name="shipping_address"}
    {if $user_data.s_firstname || $user_data.s_lastname || $user_data.s_address || $user_data.s_address_2 || $user_data.s_city || $user_data.s_country_descr || $user_data.s_state_descr || $user_data.s_zipcode || $profile_fields.S}

        <h4>{__("shipping_address")}</h4>

        {if $profile_fields.S}
            {if $user_data.s_firstname || $user_data.s_lastname}
                <p class="strong">{$user_data.s_firstname} {$user_data.s_lastname}</p>
            {/if}
            {if $user_data.s_address}
                <p>{$user_data.s_address}</p>
            {/if}
            {if $user_data.s_address_2}
                <p>{$user_data.s_address_2}</p>
            {/if}
            {if $user_data.s_city || $user_data.s_state_descr || $user_data.s_zipcode}
                <p>{$user_data.s_city}{if $user_data.s_city && ($user_data.s_state_descr || $user_data.s_zipcode)},{/if}  {$user_data.s_state_descr} {$user_data.s_zipcode}</p>
            {/if}
            {if $user_data.s_country_descr}<p>{$user_data.s_country_descr}</p>{/if}
            {include file="views/profiles/components/profile_fields_info.tpl" fields=$profile_fields.S}
            {if $user_data.s_phone}
                <p>{$user_data.s_phone}</p>
            {/if}
            {if $user_data.s_address_type}
                <p>{__("address_type")}: {$user_data.s_address_type}</p>
            {/if}
        {/if}
    {/if}
{/capture}

{* customer information *}

{capture name="customer_information"}
    
    {if $user_data}
        <p class="strong">
            {if $user_data.user_id}
                <a href="{"profiles.update?user_id=`$user_data.user_id`"|fn_url}">{$user_data.firstname} {$user_data.lastname}</a>,
            {else}
                {$user_data.firstname} {$user_data.lastname},
            {/if}
            <a href="mailto:{$user_data.email|escape:url}">{$user_data.email}</a>
        </p>
    {/if}

    {if $user_data.ip_address}
        <span>{__("ip_address")}:</span>
        {$user_data.ip_address}
    {/if}
    
    <div class="clear">
        {if $user_data.phone}
            <span>{__("phone")}:</span>
            <span>{$user_data.phone}</span>
        {/if}
        {if $user_data.fax}
            <span>{__("fax")}:</span>
            <span>{$user_data.fax}</span>
        {/if}
        {if $user_data.company}
            <span>{__("company")}:</span>
            <span>{$user_data.company}</span>
        {/if}
        {if $user_data.url}
            <span>{__("website")}:</span>
            <span>{$user_data.url}</span>
        {/if}
    </div>

    {include file="views/profiles/components/profile_fields_info.tpl" fields=$profile_fields.C customer_info="Y"}

    {if $email_changed}
        <span class="attention strong">{__("attention")}</span>
        <span class="attention">{__("notice_update_customer_details")}</span>

        <label for="update_customer_details" class="checkbox">
            <input type="checkbox" name="update_customer_details" id="update_customer_details" value="Y"  />
        {__("update_customer_info")}</label>
    {/if}
{/capture}

<table width="100%" class="profile-info">
<tr valign="top">
    <td width="{if $payment_info}34%{else}50%{/if}">
        {$smarty.capture.billing_address nofilter}
    </td>
    <td width="{if $payment_info}34%{else}50%{/if}">
        {$smarty.capture.shipping_address nofilter}
    </td>
</tr>
{if $user_data.email || $user_data.phone || $user_data.fax || $user_data.company || $user_data.url}
<tr>
    <td colspan="2">
        {$smarty.capture.customer_information nofilter}
    </td>
</tr>
{/if}
</table>