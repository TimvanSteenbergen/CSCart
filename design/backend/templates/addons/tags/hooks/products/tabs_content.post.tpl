{if $runtime.company_id && "ULTIMATE"|fn_allowed_for || "MULTIVENDOR"|fn_allowed_for}
    {if $addons.tags.tags_for_products == "Y"}
        {include file="addons/tags/views/tags/components/object_tags.tpl" object=$product_data input_name="product_data" allow_save=true object_type="P" object_id=$product_data.product_id}
    {/if}
{/if}