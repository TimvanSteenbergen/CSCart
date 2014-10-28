<p class="nowrap stars">
{if $is_link}{if $runtime.mode == "view"}<a class="cm-external-click" data-ca-scroll="content_discussion" data-ca-external-click-id="discussion">{else}<a href="{"products.view?product_id=`$product.product_id`&selected_section=discussion#discussion"|fn_url}">{/if}{/if}
{section name="full_star" loop=$stars.full}<i class="icon-star"></i>{/section}
{if $stars.part}<i class="icon-star-half"></i>{/if}
{section name="full_star" loop=$stars.empty}<i class="icon-star-empty"></i>{/section}
{if $is_link}</a>{/if}
</p>