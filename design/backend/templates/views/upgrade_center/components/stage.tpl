{if $stages}
<div><p>
{__("stage")} {$stages.stage_number} {__("of")} {$stages.total}. {__("processing")} {$stages.stage}.
</p></div>
{/if}