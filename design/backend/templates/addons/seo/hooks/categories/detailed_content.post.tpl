{if $runtime.company_id && "ULTIMATE"|fn_allowed_for || "MULTIVENDOR"|fn_allowed_for}
{include file="addons/seo/common/seo_name_field.tpl" object_data=$category_data object_name="category_data" object_id=$category_data.category_id object_type="c"}
{/if}