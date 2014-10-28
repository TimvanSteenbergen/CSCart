{if ($runtime.company_id && "ULTIMATE"|fn_allowed_for) || "MULTIVENDOR"|fn_allowed_for}
{include file="common/subheader.tpl" title=__("comments_and_reviews") target="#discussion_category_setting"}
<fieldset>
	<div id="discussion_category_setting" class="in collapse">
		{include file="addons/discussion/views/discussion_manager/components/allow_discussion.tpl" prefix="category_data" object_id=$category_data.category_id object_type="C" title=__("discussion_title_category")}
	</div>
</fieldset>
{/if}