{if $runtime.company_id && "ULTIMATE"|fn_allowed_for || "MULTIVENDOR"|fn_allowed_for}
{include file="common/subheader.tpl" title=__("seo.rich_snippets") target="#acc_addon_seo_richsnippets"}
<div id="acc_addon_seo_richsnippets" class="collapsed in">

<div class="seo-rich-snippet">

    <h3>
        <a class="srs-title cm-seo-srs-title" href="{"products.view?product_id=`$product_data.product_id`"|fn_url:"C"}" target="_blank">{$product_data.product|strip_tags|truncate:60:"..."}</a>
    </h3>
    <div>
        <div>
            <cite class="srs-url">{""|fn_url:"C"}</cite>
        </div>

        <div class="srs-price">{strip}
            {hook name="products:seo_snippet_attributes"}
            {include file="common/price.tpl" value=$product_data.price span_id="elm_seo_srs_price"} - {__("in_stock")}
            {/hook}
        {/strip}</div>

        {$description = $product_data.full_description|default:$product_data.short_description}
        <span class="srs-description cm-seo-srs-description">{$description|strip_tags|truncate:145:"..." nofilter}</span>
    </div>
</div>

</div>
{/if}
