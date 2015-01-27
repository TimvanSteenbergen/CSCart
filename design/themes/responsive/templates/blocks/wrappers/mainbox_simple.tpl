{if $content|trim}
    <div class="ty-mainbox-simple-container clearfix{if isset($hide_wrapper)} cm-hidden-wrapper{/if}{if $hide_wrapper} hidden{/if}{if $block.user_class} {$block.user_class}{/if}{if $content_alignment == "RIGHT"} ty-float-right{elseif $content_alignment == "LEFT"} ty-float-left{/if}">
        {if $title || $smarty.capture.title|trim}
            <h2 class="ty-mainbox-simple-title">
                {hook name="wrapper:mainbox_simple_title"}
                {if $smarty.capture.title|trim}
                    {$smarty.capture.title nofilter}
                {else}
                    {$title nofilter}
                {/if}
                {/hook}
            </h2>
        {/if}
        <div class="ty-mainbox-simple-body">{$content nofilter}</div>
    </div>
{/if}