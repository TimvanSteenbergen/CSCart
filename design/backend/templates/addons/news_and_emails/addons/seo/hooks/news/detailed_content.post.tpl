{if $runtime.company_id && "ULTIMATE"|fn_allowed_for || "MULTIVENDOR"|fn_allowed_for}
{include file="addons/seo/common/seo_name_field.tpl" object_data=$news_data object_name="news_data" object_id=$news_data.news_id object_type="n"}
{/if}