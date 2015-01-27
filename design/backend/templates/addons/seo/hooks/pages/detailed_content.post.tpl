{if $runtime.company_id && "ULTIMATE"|fn_allowed_for || "MULTIVENDOR"|fn_allowed_for}
{include file="addons/seo/common/seo_name_field.tpl" object_data=$page_data object_name="page_data" object_id=$page_data.page_id object_type="a"}
{/if}