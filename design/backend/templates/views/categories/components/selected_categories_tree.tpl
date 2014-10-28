{* --------- CATEGORY TREE --------------*}
{if $header}
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="table">
<tr>
    <th class="center">
    {include file="common/check_items.tpl"}</th>
    <th>{__("categories")}</th>
    <th>{__("products")}</th>
</tr>
{/if}
{foreach from=$categories_tree item=cur_cat}
{assign var="cat_id" value=$cur_cat.category_id}
{if isset($categories.$cat_id)}
<tr class="table-row">
    <td class="center">
        <input type="checkbox" name="{$checkbox_name}[{$cur_cat.category_id}]" value="Y" class="checkbox cm-item" /></td>
    <td width="100%" class="no-padding">
        <a href="{"categories.update?category_id=`$cur_cat.category_id`"|fn_url}" class="manage-item{if $cur_cat.status == "N"}-disabled{/if}">{$cur_cat.category}</a>{if $cur_cat.status == "N"}&nbsp;<span class="small-note">-&nbsp;[{__("disabled")}]</span>{/if}
    </td>
    <td class="center nowrap" width="64">
        <a href="{"products.manage?category_id=`$cur_cat.category_id`"|fn_url}"><u>&nbsp;{$cur_cat.product_count}&nbsp;</u></a>
    </td>
</tr>
{/if}
{if $cur_cat.subcategories}
    {include file="views/categories/components/selected_categories_tree.tpl" categories_tree=$cur_cat.subcategories header=""}
{/if}
{/foreach}
{if $header}
</table>
{/if}
{* --------- /CATEGORY TREE --------------*}