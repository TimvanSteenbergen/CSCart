{math equation="floor(width / 20) + 1" assign="color" width=$value_width}
{if $color > 5}
    {assign var="color" value="5"}
{/if}
{strip}
<div class="progress" {if $bar_width} style="width: {$bar_width};"{/if} align="left">
  <div class="bar" {if $value_width > 0}class="graph-bar-{$color}" style="width: {$value_width}%;"{/if}></div>
</div>
{/strip}