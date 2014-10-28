<h3 class="{$class|default:"ty-subheader"}">
    {$extra nofilter}
    {$title nofilter}

    {if $tooltip|trim}
        {include file="common/tooltip.tpl" tooltip=$tooltip params="ty-subheader__tooltip"}
    {/if}
</h3>