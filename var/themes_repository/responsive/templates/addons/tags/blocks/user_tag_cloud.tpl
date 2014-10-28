{** block-description:tmpl_my_tag_cloud **}

{if $items}
<div class="ty-tag-cloud">
    {foreach from=$items item="tag"}
        {assign var="tag_name" value=$tag.tag|escape:url}
        <a href="{"tags.view?tag=`$tag_name`&see=my"|fn_url}" class="ty-tag-cloud__item ty-tag-level-{$tag.level}">{$tag.tag}&nbsp;({$tag.popularity})</a>
    {/foreach}
</div>
<div class="ty-mtb-xs ty-right">
    <a class="ty-extra-link" href="{"tags.summary"|fn_url}">{__("my_tags_summary")}</a>
</div>
{/if}