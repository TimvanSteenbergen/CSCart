{include file="common/subheader.tpl" title=__("customer_information")}

{assign var="profile_fields" value=$location|fn_get_profile_fields}
{$contact_fields = $profile_fields.C}

<table class="orders-info valign-top">
<tr class="valign-top">
    {if $profile_fields.B}
        <td id="tygh_order_billing_adress" style="width: 31%">
            <h5>{__("billing_address")}</h5>
            <div class="orders-field">{include file="views/profiles/components/profile_fields_info.tpl" fields=$profile_fields.B title=__("billing_address")}</div>
        </td>
    {/if}
    {if $profile_fields.S}
        <td id="tygh_order_shipping_adress" style="width: 31%">
            <h5>{__("shipping_address")}</h5>
            <div class="orders-field">{include file="views/profiles/components/profile_fields_info.tpl" fields=$profile_fields.S title=__("shipping_address")}</div>
        </td>
    {/if}
    <td style="width: 35%">
        {if $contact_fields}
            {capture name="contact_information"}
                {include file="views/profiles/components/profile_fields_info.tpl" fields=$contact_fields title=__("contact_information")}
            {/capture}
            {if $smarty.capture.contact_information|trim != ""}
                <h5>{__("contact_information")}</h5>
                <div class="orders-field">{$smarty.capture.contact_information nofilter}</div>
            {/if}
        {/if}
    </td>
</tr>
</table>