{if $products}
<table width="100%" class="table table-middle">
<thead>
<tr>
    <th class="center" width="1%">
        {include file="common/check_items.tpl"}</th>
    {if !$no_pos}
    <th>{__("position_short")}</th>
    {/if}
    <th width="100%">{__("product_name")}</th>
</tr>
</thead>
{foreach from=$products item=product}
<tr>
    <td class="center" width="1%"><input type="checkbox" name="delete[{$product.product_id}]" id="delete_checkbox" value="Y" class="cm-item" /></td>
    {if !$no_pos}
    <td class="center"><input type="text" name="position[{$product.product_id}]" value="{$product.position}" size="2" class="input-short" /></td>
    {/if}
    <td>
        <a href="{"products.update?product_id=`$product.product_id`"|fn_url}">{$product.product nofilter}</a></td>
</tr>
{/foreach}
</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}