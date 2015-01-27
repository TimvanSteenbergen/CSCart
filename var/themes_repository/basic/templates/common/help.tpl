{if $content}

{if !$link_only}<div class="float-right">{/if}
    {capture name="notes_picker"}
        {$content nofilter}
    {/capture}
    {include file="common/popupbox.tpl" act="notes" id="content_`$id`_notes" text=$text content=$smarty.capture.notes_picker link_text=$link_text|default:"?" show_brackets=$show_brackets}
{if !$link_only}</div>{/if}
{/if}