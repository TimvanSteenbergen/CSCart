{if $runtime.company_id && "ULTIMATE"|fn_allowed_for || "MULTIVENDOR"|fn_allowed_for}
    {if $addons.tags.tags_for_pages == "Y"}
        {include file="addons/tags/views/tags/components/object_tags.tpl" object=$page_data input_name="page_data" object_type="A" object_id=$page_data.page_id}
    {/if}
{/if}