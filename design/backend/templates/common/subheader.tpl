{if $notes}
    {include file="common/help.tpl" content=$notes id=$notes_id}
{/if}
<h4 class="subheader {$meta} {if $target} hand{/if}" {if $target}data-toggle="collapse" data-target="{$target}"{/if}>
    {$title}
    {if $target}<span class="exicon-collapse"></span>{/if}
</h4>