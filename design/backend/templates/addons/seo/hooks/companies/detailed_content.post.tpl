{if !"ULTIMATE"|fn_allowed_for && !$runtime.company_id}
{include file="addons/seo/common/seo_name_field.tpl" object_data=$company_data object_name="company_data" object_id=$company_data.company_id object_type="m"}
{/if}