{if $anchor}
<a name="{$anchor}"></a>
{/if}
<div>
    <div class="mainbox-cart-body" {if $mainbox_id}id="{$mainbox_id}"{/if}>{$content nofilter}{if $mainbox_id}<!--{$mainbox_id}-->{/if}</div>
</div>