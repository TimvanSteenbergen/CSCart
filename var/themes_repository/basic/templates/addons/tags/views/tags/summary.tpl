{assign var="return_current_url" value=$config.current_url|escape:url}

{foreach from=$tags_summary item="tag"}

<div class="tags-wrap">
        <ul class="tag clearfix">
            <li class="tag-inner">
            <span>
            {include file="buttons/button.tpl" but_href="tags.delete?tag_id=`$tag.tag_id`&redirect_url=`$return_current_url`" but_meta="cm-confirm delete-icon" but_role="delete"}
            {$tag.tag} ({$tag.total})</span>
            </li>
        </ul>
        {if $tag.products}
            <div class="tags-group">{__("products")}:</div>
            <ul class="tags-list-container">
            {foreach from=$tag.products item="tag_product" key="tag_product_id"}
                <li>
                    {include file="buttons/button.tpl" but_href="tags.delete?tag_id=`$tag.tag_id`&object_type=P&object_id=`$tag_product_id`&redirect_url=`$return_current_url`" but_meta="cm-confirm delete-icon" but_role="delete"}
                    <a href="{"products.view?product_id=`$tag_product_id`"|fn_url}">{$tag_product}</a>
                </li>
            {/foreach}
            </ul>
        {/if}
    
        {if $tag.pages}
            <div class="tags-group">{__("pages")}:</div>
            <ul class="tags-list-container">
            {foreach from=$tag.pages item="tag_page" key="tag_page_id"}
                <li>
                    {include file="buttons/button.tpl" but_href="tags.delete?tag_id=`$tag.tag_id`&object_type=A&object_id=`$tag_page_id`&redirect_url=`$return_current_url`" but_meta="cm-confirm delete-icon" but_role="delete"}
                    <a href="{"pages.view?page_id=`$tag_page_id`"|fn_url}">{$tag_page}</a>
                </li>
            {/foreach}
            </ul>
        {/if}
    
        {hook name="tags:summary"}{/hook}
</div>
<div class="{cycle values="hidden,hidden,hidden,clear"} tags-clear"></div>

{foreachelse}
    <p class="no-items">{__("no_items")}</p>
{/foreach}

{capture name="mainbox_title"}{__("my_tags_summary")}{/capture}