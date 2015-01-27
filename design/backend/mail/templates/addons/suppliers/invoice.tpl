{if $order_info}

<style type="text/css">
body,th,td,tt,p,div,span {$ldelim}
    color: #000000;
    font-family: tahoma, verdana, arial, sans-serif;
    font-size: 11px;
{$rdelim}
p,ul {$ldelim}
    margin-top: 6px;
    margin-bottom: 6px;
{$rdelim}
.form-field-caption {$ldelim}
    font-style:italic;
{$rdelim}
.form-title    {$ldelim}
    background-color: #ffffff;
    color: #141414;
    font-weight: bold;
{$rdelim}
</style>

<table cellpadding="0" cellspacing="0" width="100%"    border="0">
<tr>
    <td><img src="{$images_dir}/spacer.gif" width="1" height="1" border="0" alt="" /></td>
    <td width="600" style="border: #444444; border-style: solid; border-width: 2px" align="center">
        <table cellpadding="10" cellspacing="0" width="100%" border="0">
        <tr>
            <td>
            {* Customer info *}
            {if !$profile_fields}
            {assign var="profile_fields" value='I'|fn_get_profile_fields}
            {/if}
            {if $profile_fields.C}
                {assign var="contact_fields" value="`$profile_fields.C`"|array_slice:0:4}
            {/if}
            <table cellpadding="4" cellspacing="0" border="0" width="100%">
            <tr>
                {if $profile_fields.C}
                    <td valign="top" width="50%">
                    <table>
                    <tr>
                        <td>
                            <table>
                                {include file="profiles/profile_fields_info.tpl" fields=$contact_fields title=__("contact_information") user_data=$order_info}
                            </table>
                        </td>
                    </tr>
                    </table>
                    </td>
                    <td width="1%">&nbsp;</td>
                {/if}
                {if $profile_fields.S}
                    <td valign="top" width="49%">
                        <table>
                            {include file="profiles/profile_fields_info.tpl" fields=$profile_fields.S title=__("shipping_address") user_data=$order_info}
                        </table>
                    </td>
                {/if}
            </tr>
            </table>
            <p></p><br />
            {* /Customer info *}

            {* Ordered products *}
            <table cellpadding="0" cellspacing="0" border="0" width="100%">
            <tr>
            <td valign="top">
            <table cellpadding="2" cellspacing="1" border="0" width="100%" bgcolor="#000000">
            <tr>
                <td width="10%" align="center" bgcolor="#dddddd"><b>{__("sku")}</b></td>
                <td width="50%" bgcolor="#dddddd"><b>{__("product")}</b></td>
                <td width="10%" align="center" bgcolor="#dddddd"><b>{__("amount")}</b></td>
            </tr>
            {foreach from=$order_info.products item="oi"}
                {if (!empty($oi.extra.supplier_id) && $oi.extra.supplier_id == $supplier_id) || (fn_get_product_supplier_id($oi.product_id) == $supplier_id)}
                    <tr>
                        <td bgcolor="#ffffff">{$oi.product_code|default:"-"}</td>
                        <td bgcolor="#ffffff">{$oi.product}
                            {if $oi.product_options}<div style="padding-top: 1px; padding-bottom: 2px;">{include file="common/options_info.tpl" product_options=fn_get_selected_product_options_info($oi.product_options)}</div>{/if}</td>
                        <td bgcolor="#ffffff" align="center">{$oi.amount}</td>
                    </tr>
                {/if}
            {/foreach}
            </table>
            </td>
            </tr>
            </table>
            {* /Ordered products *}

            {* Order totals *}
            {if $supplier.shippings}
                <div align="right">
                    <table>
                        {foreach from=$supplier.shippings item="shipping"}
                            <tr>
                                <td align="right" nowrap="nowrap"><b>{__("shipping_method")}:</b>&nbsp;</td>
                                <td align="right" nowrap="nowrap">{$shipping.shipping}</td>
                            </tr>
                        {/foreach}
                    </table>
                </div>

                <div align="right">
                    <table>
                        <tr>
                            <td align="right" nowrap="nowrap"><b>{__("shipping_cost")}:</b>&nbsp;</td>
                            <td align="right" nowrap="nowrap">{include file="common/price.tpl" value=$supplier.cost}</td>
                        </tr>
                    </table><br />
                </div>
            {/if}
            {* /Order totals *}
            </td>
        </tr>
        </table>
    </td>
    <td><img src="{$images_dir}/spacer.gif" width="1" height="1" border="0" alt="" /></td>
</tr>
</table>

{/if}