{if $content|trim}
    <div class="ty-sidebox-important{if isset($hide_wrapper)} cm-hidden-wrapper{/if}{if $hide_wrapper} hidden{/if}{if $block.user_class} {$block.user_class}{/if}{if $content_alignment == "RIGHT"} ty-float-right{elseif $content_alignment == "LEFT"} ty-float-left{/if}">
        <h3 class="ty-sidebox-important__title{if $header_class} {$header_class}{/if}">
            {hook name="wrapper:sidebox_important_title"}
            {if $smarty.capture.title|trim}
                {$smarty.capture.title nofilter}
            {else}
                <span class="ty-sidebox-important__title-wrapper">{$title nofilter}</span>
            {/if}
            {/hook}
        </h3>
        <div class="ty-sidebox-important__body">{$content|default:"&nbsp;" nofilter}</div>
    </div>
{/if}