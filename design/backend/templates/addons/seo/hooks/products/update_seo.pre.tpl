{if $runtime.company_id && "ULTIMATE"|fn_allowed_for || "MULTIVENDOR"|fn_allowed_for}
{include file="addons/seo/common/seo_name_field.tpl" object_data=$product_data object_name="product_data" object_id=$product_data.product_id object_type="p" share_dont_hide=true}
{/if}
