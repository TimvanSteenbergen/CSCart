{strip}
<div class="ty-polls-graph">
    <div class="ty-polls-graph__title">{$answer_description}</div>
    <div class="ty-polls-graph__ratio"><strong>{$ratio|default:"0.00"}%</strong>&nbsp;({$count|default:"0"})</div>
    <div class="clearfix"></div>
    <div class="ty-polls-graph__bar">
        <div class="ty-polls-graph__bar-result" {if $value_width > 0} style="width: {$value_width|default:"0"}%; opacity: 0.{$count|default:"0"};"{/if}></div>
    </div>
</div>
{/strip}