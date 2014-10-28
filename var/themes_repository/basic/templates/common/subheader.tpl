{if $anchor}
<a name="{$anchor}"></a>
{/if}
<h2 class="{$class|default:"subheader"}">
    {$extra nofilter}
    {$title nofilter}

    {if $tooltip|trim}
        {include file="common/tooltip.tpl" tooltip=$tooltip}
    {/if}
</h2>