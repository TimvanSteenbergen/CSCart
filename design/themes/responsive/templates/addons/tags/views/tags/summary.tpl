{assign var="return_current_url" value=$config.current_url|escape:url}

{foreach from=$tags_summary item="tag"}

<div class="ty-tags-summary">
        <ul class="ty-tags-summary__list">
            <li class="ty-tags-summary__item ty-tags-list__a">
                {include file="buttons/button.tpl" but_href="tags.delete?tag_id=`$tag.tag_id`&redirect_url=`$return_current_url`" but_meta="cm-confirm ty-tags-summary__delete" but_role="delete"}
                <span class="ty-tags-summary__item-name">{$tag.tag}</span> ({$tag.total})
            </li>
        </ul>
        {if $tag.products}
            <div class="ty-tags-summary__group-title">{__("products")}:</div>
            <ul class="ty-tags-summary__group ty-tags-list-container">
            {foreach from=$tag.products item="tag_product" key="tag_product_id"}
                <li class="ty-tags-summary__group-item">
                    {include file="buttons/button.tpl" but_href="tags.delete?tag_id=`$tag.tag_id`&object_type=P&object_id=`$tag_product_id`&redirect_url=`$return_current_url`" but_meta="cm-confirm ty-tags-summary__group-delete" but_role="delete"}
                    <a href="{"products.view?product_id=`$tag_product_id`"|fn_url}">{$tag_product}</a>
                </li>
            {/foreach}
            </ul>
        {/if}
    
        {if $tag.pages}
            <div class="ty-tags-summary__group-title">{__("pages")}:</div>
            <ul class="ty-tags-list-container">
            {foreach from=$tag.pages item="tag_page" key="tag_page_id"}
                <li>
                    {include file="buttons/button.tpl" but_href="tags.delete?tag_id=`$tag.tag_id`&object_type=A&object_id=`$tag_page_id`&redirect_url=`$return_current_url`" but_meta="ty-btn__secondary cm-confirm ty-delete-icon" but_role="delete"}
                    <a href="{"pages.view?page_id=`$tag_page_id`"|fn_url}">{$tag_page}</a>
                </li>
            {/foreach}
            </ul>
        {/if}
        {hook name="tags:summary"}{/hook}
</div>

{foreachelse}
    <p class="ty-no-items">{__("no_items")}</p>
{/foreach}

{capture name="mainbox_title"}{__("my_tags_summary")}{/capture}