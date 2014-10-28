{** block-description:tag_cloud **}

{if $items}
<div class="ty-tag-cloud">
    {foreach from=$items item="tag"}
        {assign var="tag_name" value=$tag.tag|escape:url}
        <a href="{"tags.view?tag=`$tag_name`"|fn_url}" class="ty-tag-cloud__item ty-tag-level-{$tag.level}">{$tag.tag}</a>
    {/foreach}
</div>
{/if}