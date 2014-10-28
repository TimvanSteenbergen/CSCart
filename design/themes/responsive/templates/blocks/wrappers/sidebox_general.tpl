{if $content|trim}
    <div class="{$sidebox_wrapper|default:"ty-sidebox"}{if isset($hide_wrapper)} cm-hidden-wrapper{/if}{if $hide_wrapper} hidden{/if}{if $block.user_class} {$block.user_class}{/if}{if $content_alignment == "RIGHT"} ty-float-right{elseif $content_alignment == "LEFT"} ty-float-left{/if}">
        <h2 class="ty-sidebox__title {if $header_class} {$header_class}{/if}">
            {hook name="wrapper:sidebox_general_title"}
            {if $smarty.capture.title|trim}
                {$smarty.capture.title nofilter}
            {else}
                <span class="ty-sidebox__title-wrapper">{$title nofilter}</span>
            {/if}
            <div class="ty-sidebox__title-toggle cm-combination visible-phone" id="sw_sidebox_{$block.block_id}">
                <i class="ty-sidebox__icon-open ty-icon-down-open"></i>
                <i class="ty-sidebox__icon-hide ty-icon-up-open"></i>
            </div>
            {/hook}
        </h2>
        <div class="ty-sidebox__body" id="sidebox_{$block.block_id}">{$content|default:"&nbsp;" nofilter}</div>
    </div>

{/if}