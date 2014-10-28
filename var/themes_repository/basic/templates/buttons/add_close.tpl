{if $is_js == true}
    {include file="buttons/button.tpl" but_name="submit" but_text=$but_close_text but_onclick=$but_close_onclick but_role="button_main" but_meta="cm-process-items cm-dialog-closer"}&nbsp;
    {if $but_text}
        {include file="buttons/button.tpl" but_name="submit" but_text=$but_text but_onclick=$but_onclick but_role="submit" but_meta="cm-process-items"}&nbsp;
    {/if}
{else}
    {include file="buttons/button.tpl" but_name="submit" but_text=$but_close_text but_role="button_main" but_meta="cm-process-items"}&nbsp;
{/if}

&nbsp;{__("or")}&nbsp;&nbsp;&nbsp;<a class="cm-dialog-closer text-button nobg">{__("cancel")}</a>