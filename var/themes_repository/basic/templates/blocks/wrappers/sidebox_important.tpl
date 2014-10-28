{if $content|trim}
    <div class="sidebox-important-wrapper{if isset($hide_wrapper)} cm-hidden-wrapper{/if}{if $hide_wrapper} hidden{/if}{if $block.user_class} {$block.user_class}{/if}{if $content_alignment == "RIGHT"} float-right{elseif $content_alignment == "LEFT"} float-left{/if}">
        <h3 class="sidebox-title{if $header_class} {$header_class}{/if}">
            {hook name="wrapper:sidebox_important_title"}
            {if $smarty.capture.title|trim}
                {$smarty.capture.title nofilter}
            {else}
                <span>{$title nofilter}</span>
            {/if}
            {/hook}
        </h3>
        <div class="sidebox-body">{$content|default:"&nbsp;" nofilter}</div>
    </div>
{/if}