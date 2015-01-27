{math equation="floor(width / 20) + 1" assign="color" width=$value_width|default:"0"}
{if $color > 5}
    {assign var="color" value="5"}
{/if}
{strip}
<div class="progress pull-left" style="width: {$bar_width}px;">
    <div class="bar" {if $value_width > 0}style="width: {$value_width}%;"{/if}></div>
</div>&nbsp;
{/strip}