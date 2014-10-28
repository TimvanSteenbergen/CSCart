{** block-description:tmpl_quick_links **}
<div class="quick-links-wrap">
    {hook name="index:top_links"}
        {foreach from=$quick_links item="link"}
            <a href="{$link.param|fn_url}">{$link.descr}</a>&nbsp;&nbsp;&nbsp;
        {/foreach}
    {/hook}
</div>