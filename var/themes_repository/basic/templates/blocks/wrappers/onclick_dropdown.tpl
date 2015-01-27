{if $content|trim}
    {assign var="dropdown_id" value=$block.snapping_id}
    <div class="dropdown-box {if $block.user_class} {$block.user_class}{/if}{if $content_alignment == "RIGHT"} float-right{elseif $content_alignment == "LEFT"} float-left{/if}">
        <div id="sw_dropdown_{$dropdown_id}" class="popup-title cm-combination {if $header_class}{$header_class}{/if}">
            {hook name="wrapper:onclick_dropdown_title"}
            {if $smarty.capture.title|trim}
                {$smarty.capture.title nofilter}
            {else}
                <a>{$title nofilter}</a>
            {/if}
            {/hook}
        </div>
        <div id="dropdown_{$dropdown_id}" class="cm-popup-box popup-content hidden">
            {$content|default:"&nbsp;" nofilter}
        </div>
    </div>
{/if}