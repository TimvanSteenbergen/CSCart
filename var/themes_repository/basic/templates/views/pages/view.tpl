<div class="wysiwyg-content">
    {hook name="pages:page_content"}
    <div {live_edit name="page:description:{$page.page_id}"}>{$page.description nofilter}</div>
    {/hook}
    {capture name="mainbox_title"}<span {live_edit name="page:page:{$page.page_id}"}>{$page.page}</span>{/capture}
</div>
    
{hook name="pages:page_extra"}
{/hook}