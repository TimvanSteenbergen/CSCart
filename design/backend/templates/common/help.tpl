{if $content}
<div class="pull-right note-subheader">
    {capture name="notes_picker"}
        {$content nofilter}
    {/capture}
    {include file="common/popupbox.tpl" act="notes" id="content_`$id`_notes" text=__("note") content=$smarty.capture.notes_picker}
</div>
{/if}