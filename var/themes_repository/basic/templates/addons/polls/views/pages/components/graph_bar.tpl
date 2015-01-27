{strip}
<div class="polls-results-wrap" align="left">
    <div class="polls-desc">{$answer_description}</div>
    <div class="polls-ratio"><strong>{$ratio|default:"0.00"}%</strong>&nbsp;({$count|default:"0"})</div>
    <div class="clear"></div>
    <div class="polls-results-bar">
        <div class="polls-results-bar-bg" {if $value_width > 0} style="width: {$value_width|default:"0"}%; opacity: 0.{$count|default:"0"};"{/if}></div>
    </div>
</div>
{/strip}