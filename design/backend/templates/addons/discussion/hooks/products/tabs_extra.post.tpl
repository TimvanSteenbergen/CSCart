{if $runtime.company_id && "ULTIMATE"|fn_allowed_for || "MULTIVENDOR"|fn_allowed_for}
{include file="addons/discussion/views/discussion_manager/components/discussion.tpl" object_company_id=$product_data.company_id}
{/if}