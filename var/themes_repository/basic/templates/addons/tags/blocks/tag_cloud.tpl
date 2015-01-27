{** block-description:tag_cloud **}

{if $items}
{foreach from=$items item="tag"}
    {assign var="tag_name" value=$tag.tag|escape:url}
    <a href="{"tags.view?tag=`$tag_name`"|fn_url}" class="tag-level-{$tag.level}">{$tag.tag}</a>
{/foreach}
{/if}