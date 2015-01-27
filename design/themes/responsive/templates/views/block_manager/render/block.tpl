{if $block.user_class || $content_alignment == 'RIGHT' || $content_alignment == 'LEFT'}
    <div class="{if $block.user_class} {$block.user_class}{/if}{if $content_alignment == 'RIGHT'} ty-float-right{elseif $content_alignment == 'LEFT'}
    ty-float-left{/if}">
{/if}
        {$content nofilter}
{if $block.user_class || $content_alignment == 'RIGHT' || $content_alignment == 'LEFT'}
    </div>
{/if}