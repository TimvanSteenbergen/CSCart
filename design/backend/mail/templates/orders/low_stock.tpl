{include file="common/letter_header.tpl"}
<table>
<tr>
    <td>{__("product")}:</td>
    <td>{$product}</td>
</tr>
<tr>
    <td>{__("id")}:</td>
    <td>{$product_id}</td>
</tr>
<tr>
    <td>{__("sku")}:</td>
    <td>{$product_code}</td>
</tr>
<tr>
    <td>{__("amount")}:</td>
    <td><b>{$new_amount}</b></td>
</tr>
{if $product_options}
<tr>
    <td colspan="2">{__("product_options")}:<br><hr></td>
</tr>
{foreach from=$product_options item=o}
<tr>
    <td>{$o.option_name}:</td>
    <td>{$o.variant_name}</td>
</tr>
{/foreach}
{/if}
</table>
{include file="common/letter_footer.tpl"}