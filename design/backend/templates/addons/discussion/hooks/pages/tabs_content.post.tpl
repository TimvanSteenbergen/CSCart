{if $page_type != $smarty.const.PAGE_TYPE_LINK}
    {include file="addons/discussion/views/discussion_manager/components/discussion.tpl" object_company_id=$page_data.company_id}
{/if}