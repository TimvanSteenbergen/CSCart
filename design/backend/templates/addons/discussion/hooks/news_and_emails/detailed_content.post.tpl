{if $runtime.company_id && "ULTIMATE"|fn_allowed_for || "MULTIVENDOR"|fn_allowed_for}
{include file="addons/discussion/views/discussion_manager/components/allow_discussion.tpl" prefix="news_data" object_id=$news_data.news_id object_type="N" title=__("discussion_title_news") non_editable=true}
{/if}