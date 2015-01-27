<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head></head>

<body>

{if $order_info}
{literal}
<style type="text/css" media="screen,print">
body,p,div {
    color: #000000;
    font: 12px Arial;
}
body {
    padding: 0;
    margin: 0;
}
a, a:link, a:visited, a:hover, a:active {
    color: #000000;
    text-decoration: underline;
}
a:hover {
    text-decoration: none;
}
</style>
<style media="print">
body {
    background-color: #ffffff;
}
.scissors {
    display: none;
}
</style>
{/literal}
{include file="common/scripts.tpl"}
{if !$company_placement_info}
{assign var="company_placement_info" value=$order_info.company_id|fn_get_company_placement_info:$smarty.const.CART_LANGUAGE}
{/if}
<table cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #f4f6f8; height: 100%;">
<tr>
    <td align="center" style="width: 100%; height: 100%; padding: 24px 0;">
    <div style="background-color: #ffffff; border: 1px solid #e6e6e6; margin: 0px auto; padding: 0px 44px 0px 46px; width: 510px; text-align: left;">
        {assign var="profile_fields" value='I'|fn_get_profile_fields}

        {if $profile_fields.S}
        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="padding-top: 32px;">
        <tr valign="top">
            <td width="100%" align="center" style="border-bottom: 1px dashed #000000; padding-bottom: 20px;">
                <h3 style="font: bold 17px Tahoma; padding: 0px 0px 3px 1px; margin: 0px;">{__("ship_to")}:</h3>
                {if $order_info.s_firstname || $order_info.s_lastname}
                <p style="margin: 2px 0px 3px 0px;">
                    {$order_info.s_firstname} {$order_info.s_lastname}
                </p>
                {/if}
                {if $order_info.s_address || $order_info.s_address_2}
                <p style="margin: 2px 0px 3px 0px;">
                    {$order_info.s_address} {$order_info.s_address_2}
                </p>
                {/if}
                {if $order_info.s_city || $order_info.s_state_descr || $order_info.s_zipcode}
                <p style="margin: 2px 0px 3px 0px;">
                    {$order_info.s_city} {$order_info.s_state_descr} {$order_info.s_zipcode}
                </p>
                {/if}
                {if $order_info.s_country_descr}
                <p style="margin: 2px 0px 3px 0px;">
                    {$order_info.s_country_descr}
                </p>
                {/if}
                {include file="profiles/profiles_extra_fields.tpl" fields=$profile_fields.S}
            </td>
        </tr>
        <tr valign="top" class="scissors">
            <td width="100%" style="padding-left: 20px;">
                <img src="{$images_dir}/scissors.gif" border="0" />
            </td>
        </tr>
        </table>
        
        {/if}
        {* Customer info *}
        
        <table cellpadding="0" cellspacing="0" border="0" width="100%">
        <tr>
            <td style="width: 50%; padding: 14px 0px 0px 2px;">
                <h2 style="font: bold 12px Arial; margin: 0px 0px 3px 0px;">{$company_placement_info.company_name}</h2>
                {$company_placement_info.company_address}<br />
                {$company_placement_info.company_city}{if $company_placement_info.company_city && ($company_placement_info.company_state_descr || $company_placement_info.company_zipcode)},{/if} {$company_placement_info.company_state_descr} {$company_placement_info.company_zipcode}<br />
                {$company_placement_info.company_country_descr}
                <table cellpadding="0" cellspacing="0" border="0">
                {if $company_placement_info.company_phone}
                <tr valign="top">
                    <td style="font: 12px verdana, helvetica, arial, sans-serif; text-transform: uppercase; color: #000000; padding-right: 10px;    white-space: nowrap;">{__("phone1_label")}:</td>
                    <td width="100%">{$company_placement_info.company_phone}</td>
                </tr>
                {/if}
                {if $company_placement_info.company_phone_2}
                <tr valign="top">
                    <td style="font: 12px verdana, helvetica, arial, sans-serif; text-transform: uppercase; color: #000000; padding-right: 10px; white-space: nowrap;">{__("phone2_label")}:</td>
                    <td width="100%">{$company_placement_info.company_phone_2}</td>
                </tr>
                {/if}
                {if $company_placement_info.company_fax}
                <tr valign="top">
                    <td style="font: 12px verdana, helvetica, arial, sans-serif; text-transform: uppercase; color: #000000; padding-right: 10px; white-space: nowrap;">{__("fax")}:</td>
                    <td width="100%">{$company_placement_info.company_fax}</td>
                </tr>
                {/if}
                {if $company_placement_info.company_website}
                <tr valign="top">
                    <td style="font: 12px verdana, helvetica, arial, sans-serif; text-transform: uppercase; color: #000000; padding-right: 10px; white-space: nowrap;">{__("web_site")}:</td>
                    <td width="100%">{$company_placement_info.company_website}</td>
                </tr>
                {/if}
                {if $company_placement_info.company_orders_department}
                <tr valign="top">
                    <td style="font: 12px verdana, helvetica, arial, sans-serif; text-transform: uppercase; color: #000000; padding-right: 10px; white-space: nowrap;">{__("email")}:</td>
                    <td width="100%"><a href="mailto:{$company_placement_info.company_orders_department}">{$company_placement_info.company_orders_department|replace:",":"<br>"|replace:" ":""}</a></td>
                </tr>
                {/if}
                </table>
            </td>
            
            <td style="padding-top: 14px;" valign="top">
                <h2 style="font: bold 17px Tahoma; margin: 0px;">{__("packing_slip_for_order")}&nbsp;#{$order_info.order_id}</h2>
                <table cellpadding="0" cellspacing="0" border="0">
                {if $settings.General.use_shipments == "Y" && $shipment}
                    {if !"ULTIMATE:FREE"|fn_allowed_for}
                        <tr valign="top">
                            <td style="font: 12px verdana, helvetica, arial, sans-serif; text-transform: uppercase; color: #000000; padding-right: 10px; white-space: nowrap;">{__("order_date")}:</td>
                            <td>{$order_info.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}</td>
                        </tr>
                        <tr valign="top">
                            <td style="font: 12px verdana, helvetica, arial, sans-serif; text-transform: uppercase; color: #000000; padding-right: 10px; white-space: nowrap;">{__("shipment_date")}:</td>
                            <td>{$shipment.shipment_timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}</td>
                        </tr>
                    {/if}
                {else}
                <tr valign="top">
                    <td style="font: 12px verdana, helvetica, arial, sans-serif; text-transform: uppercase; color: #000000; padding-right: 10px; white-space: nowrap;">{__("date")}:</td>
                    <td>{$order_info.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}</td>
                </tr>
                {/if}
                </table>
            </td>
        </tr>
        </table>
        
        {if $profile_fields}
        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="padding: 20px 0px 24px 0px;">
        <tr valign="top">
            {if $profile_fields.B}
            <td width="54%">
                <h3 style="font: bold 17px Tahoma; padding: 0px 0px 3px 1px; margin: 0px;">{__("bill_to")}:</h3>
                {if $order_info.b_firstname || $order_info.b_lastname}
                <p style="margin: 2px 0px 3px 0px;">
                    {$order_info.b_firstname} {$order_info.b_lastname}
                </p>
                {/if}
                {if $order_info.b_address || $order_info.b_address_2}
                <p style="margin: 2px 0px 3px 0px;">
                    {$order_info.b_address} {$order_info.b_address_2}
                </p>
                {/if}
                {if $order_info.b_city || $order_info.b_state_descr || $order_info.b_zipcode}
                <p style="margin: 2px 0px 3px 0px;">
                    {$order_info.b_city}{if $order_info.b_city && ($order_info.b_state_descr || $order_info.b_zipcode)},{/if} {$order_info.b_state_descr} {$order_info.b_zipcode}
                </p>
                {/if}
                {if $order_info.b_country_descr}
                <p style="margin: 2px 0px 3px 0px;">
                    {$order_info.b_country_descr}
                </p>
                {/if}
                {include file="profiles/profiles_extra_fields.tpl" fields=$profile_fields.B}
            </td>
            {/if}
            {if $profile_fields.S}
            <td width="54%">
                <h3 style="font: bold 17px Tahoma; padding: 0px 0px 3px 1px; margin: 0px;">{__("ship_to")}:</h3>
                {if $order_info.s_firstname || $order_info.s_lastname}
                <p style="margin: 2px 0px 3px 0px;">
                    {$order_info.s_firstname} {$order_info.s_lastname}
                </p>
                {/if}
                {if $order_info.s_address || $order_info.s_address_2}
                <p style="margin: 2px 0px 3px 0px;">
                    {$order_info.s_address} {$order_info.s_address_2}
                </p>
                {/if}
                {if $order_info.s_city || $order_info.s_state_descr || $order_info.s_zipcode}
                <p style="margin: 2px 0px 3px 0px;">
                    {$order_info.s_city}{if $order_info.s_city && ($order_info.s_state_descr || $order_info.s_zipcode)},{/if} {$order_info.s_state_descr} {$order_info.s_zipcode}
                </p>
                {/if}
                {if $order_info.s_country_descr}
                <p style="margin: 2px 0px 3px 0px;">
                    {$order_info.s_country_descr}
                </p>
                {/if}
                {include file="profiles/profiles_extra_fields.tpl" fields=$profile_fields.S}
            </td>
            {/if}
        </tr>
        </table>
        {/if}
        {* Customer info *}
        
        <table cellpadding="0" cellspacing="0" border="0">
        <tr valign="top">
            <td style="font: 12px verdana, helvetica, arial, sans-serif; text-transform: uppercase; color: #000000; padding-right: 10px; white-space: nowrap;">{__("status")}:</td>
            <td width="100%">{include file="common/status.tpl" status=$order_info.status display="view"}</td>
        </tr>
        <tr valign="top">
            <td style="font: 12px verdana, helvetica, arial, sans-serif; text-transform: uppercase; color: #000000; padding-right: 10px; white-space: nowrap;">{__("payment_method")}:</td>
            <td valign="bottom">{$order_info.payment_method.payment|default:" - "}</td>
        </tr>
        {if $order_info.shipping || $shipment}
        <tr valign="top">
            <td style="font: 12px verdana, helvetica, arial, sans-serif; text-transform: uppercase; color: #000000; padding-right: 10px; white-space: nowrap;">{__("shipping_method")}:</td>
            <td valign="bottom">
                {if !$shipment}
                    {foreach from=$order_info.shipping item="shipping" name="f_shipp"}
                        {$shipping.shipping}
                        {if !$smarty.foreach.f_shipp.last}, {/if}
                        {if $shipping.tracking_number}{assign var="tracking_number_exists" value="Y"}{/if}
                    {/foreach}
                {else}
                    {$shipment.shipping}
                    {if $shipment.tracking_number}
                    &nbsp;({__("tracking_number")}: {$shipment.tracking_number})
                    {/if}
                {/if}
            </td>
        </tr>
        {if $tracking_number_exists}
            <tr valign="top">
                <td style="font: 12px verdana, helvetica, arial, sans-serif; text-transform: uppercase; color: #000000; padding-right: 10px; white-space: nowrap;">{__("tracking_number")}:</td>
                <td valign="bottom">
                    {foreach from=$order_info.shipping item="shipping" name="f_shipp"}
                        {if $shipping.tracking_number}{$shipping.tracking_number}
                            {if !$smarty.foreach.f_shipp.last},{/if}
                        {/if}
                    {/foreach}</td>
            </tr>
        {/if}
        {/if}
        </table>
        
        {* Ordered products *}
    
        <table width="100%" cellpadding="0" cellspacing="1" style="background-color: #dddddd; margin-top: 20px;">
        <tr>
            <th width="70%" style="background-color: #eeeeee; padding: 6px 10px; white-space: nowrap;">{__("product")}</th>
            <th style="background-color: #eeeeee; padding: 6px 10px; white-space: nowrap;">{__("sku")}</th>
            <th style="background-color: #eeeeee; padding: 6px 10px; white-space: nowrap;">{__("quantity")}</th>
        </tr>
        {foreach from=$order_info.products item="oi"}
            {if $oi.amount > 0}
            <tr>
                <td style="padding: 5px 10px; background-color: #ffffff;">
                    {$oi.product|default:__("deleted_product") nofilter}
                    {if $oi.product_options}<br/>{include file="common/options_info.tpl" product_options=$oi.product_options skip_modifiers=true}{/if}
                </td>
                <td style="padding: 5px 10px; background-color: #ffffff; text-align: left;">{$oi.product_code}</td>
                <td style="padding: 5px 10px; background-color: #ffffff; text-align: center;">{$oi.amount}</td>
            </tr>
            {/if}
        {/foreach}
        </table>
        
        {* /Ordered products *}
        
        {if $settings.General.use_shipments != "Y" && $shipment}
            {if !"ULTIMATE:FREE"|fn_allowed_for}
                {if $order_info.notes}
                    <div style="float: left; padding-top: 20px;"><strong>{__("notes")}:</strong></div>
                    <div style="padding-left: 7px; padding-bottom: 15px; overflow-x: auto; clear: both; width: 505px; height: 100%; padding-bottom: 20px; overflow-y: hidden;">{$order_info.notes|wordwrap:90:"\n":true|nl2br}</div>
                {/if}
            {/if}

        {elseif $shipment.comments}
            <div style="float: left; padding-top: 20px;"><strong>{__("comments")}:</strong></div>
            <div style="padding-left: 7px; padding-bottom: 15px; overflow-x: auto; clear: both; width: 505px; height: 100%; padding-bottom: 20px; overflow-y: hidden;">{$shipment.comments|wordwrap:90:"\n":true|nl2br}</div>
        {/if}
        
        {hook name="orders:invoice"}
        {/hook}
    </div>
    </td>
</tr>
</table>
{/if}

</body>
</html>