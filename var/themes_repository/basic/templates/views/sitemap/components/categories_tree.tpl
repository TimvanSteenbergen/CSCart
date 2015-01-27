<div class="sitemap-tree-section">
{foreach from=$all_categories_tree item=category key=cat_key name="categories"}
   {if $category.level == "0"}
       {if $ul_subcategories == "started"}
       </ul>
            {assign var="ul_subcategories" value=""}
       {/if}  
       {if $ul_subcategories != "started"}
       <ul>
               <li class="parent"><a href="{"categories.view?category_id=`$category.category_id`"|fn_url}" class="strong">{$category.category}</a></li>
            {assign var="ul_subcategories" value="started"}
        {/if}
   {else}
           <li style="padding-left: {if $category.level == "1"}0px{elseif $category.level > "1"}{math equation="x*y+0" x="5" y=$category.level}px{/if};"><a href="{"categories.view?category_id=`$category.category_id`"|fn_url}">{$category.category}</a></li>
   {/if}
   {if $smarty.foreach.categories.last}
        </ul>
     {/if}
{/foreach}
</div>
<div class="clear"></div>