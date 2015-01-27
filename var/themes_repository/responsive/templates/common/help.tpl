{if $content}

{if !$link_only}<span class="ty-help-info">{/if}
    {capture name="notes_picker"}
        {$content nofilter}
    {/capture}
    {include file="common/popupbox.tpl" act="notes" id="content_`$id`_notes" text=$text content=$smarty.capture.notes_picker link_text=$link_text|default:"?" show_brackets=$show_brackets}
{if !$link_only}</span>{/if}
{/if}