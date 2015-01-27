<div class="sitemap">
    <div class="sitemap-section">
        <h2>{__("cart_info")}</h2>
        <div class="sitemap-section-body">
        {if $sitemap_settings.show_site_info == "Y"}
            <h3>{__("information")}</h3>
            <ul>
                {include file="views/pages/components/pages_tree.tpl" tree=$sitemap.pages_tree root=true no_delim=true}
            </ul>
        {/if}
        </div>
        <div class="sitemap-section-body">
        {if $sitemap.custom}
        {foreach from=$sitemap.custom item=section key=name}
            <h3>{$name}</h3>
            <ul>
                {foreach from=$section item=link}
                    <li><a href="{$link.link_href|fn_url}">{$link.link}</a></li>
                {/foreach}
            </ul>
        {/foreach}
        {/if}
        </div>
    </div>
    <div class="clear"></div>
    <div class="sitemap-section">
        <h2>{__("catalog")}</h2>
        <div class="sitemap-tree">
        {if $sitemap.categories || $sitemap.categories_tree}
        {if $sitemap.categories}
            <ul>
                {foreach from=$sitemap.categories item=category}
                    <li><a href="{"categories.view?category_id=`$category.category_id`"|fn_url}" class="strong">{$category.category}</a></li>
                {/foreach}
            </ul>
        {/if}
        {if $sitemap.categories_tree}
            {include file="views/sitemap/components/categories_tree.tpl" all_categories_tree=$sitemap.categories_tree background="white"}
        {/if}
        {/if}
        </div>
    </div>
    
</div>
{capture name="mainbox_title"}{__("sitemap")}{/capture}