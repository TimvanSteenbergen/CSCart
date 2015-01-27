<a class="cm-dialog-closer cm-cancel tool-link btn">{__("cancel")}</a>
{if $is_js == true}
    {include file="buttons/button.tpl" but_name="submit" but_text=$but_close_text but_onclick=$but_close_onclick but_role="button_main" but_meta="cm-process-items cm-dialog-closer btn-primary"}
    {if $but_text}
        {include file="buttons/button.tpl" but_name="" but_text=$but_text but_onclick=$but_onclick but_role="submit" but_meta="cm-process-items btn-primary"}
    {/if}
{else}
    {include file="buttons/button.tpl" but_name="submit" but_text=$but_close_text but_role="button_main" but_meta="cm-process-items btn-primary"}
{/if}