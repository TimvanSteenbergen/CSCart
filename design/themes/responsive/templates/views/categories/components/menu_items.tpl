{strip}
{assign var="foreach_name" value="cats_$cid"}
{foreach from=$items item="category" name=$foreach_name}
<li class="{if $separated}b-border {/if}{if $category.subcategories}dir{/if}">
    {if $category.subcategories}
        <i class="ty-icon-right-open"></i><i class="ty-icon-left-open"></i>
        <div class="hide-border">&nbsp;</div>
        <ul>
            {include file="views/categories/components/menu_items.tpl" items=$category.subcategories separated=true submenu=true cid=$category.category_id}
        </ul>
    {/if}
    <a href="{"categories.view?category_id=`$category.category_id`"|fn_url}">{$category.category}</a>
</li>
{/foreach}
{/strip}