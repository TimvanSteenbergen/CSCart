<div class="float-right nowrap right" id="post_{$post.post_id}">
    {include file="common/select_popup.tpl" id=$post.post_id status=$post.status hidden="" object_id_name="post_id" table="discussion_posts" items_status="discussion"|fn_get_predefined_statuses}
    <span>{$post.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}</span>&nbsp;-&nbsp;
<!--post_{$post.post_id}--></div>