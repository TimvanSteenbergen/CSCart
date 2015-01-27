{if $but_target_id || $but_target_form}
{assign var="but_role" value="submit-link"}
{else}
{assign var="but_role" value="button_main"}
{/if}

{if $save}
    {assign var="but_label" value=__("save")}
    {assign var="but_label2" value=__("save_and_close")}
{else}
    {assign var="but_label" value=__("create")}
    {assign var="but_label2" value=__("create_and_close")}
{/if}

{if $but_name}{assign var="r" value=$but_name}{else}{assign var="r" value=$but_href}{/if}

{if $cancel_action == "close"}
    <a class="cm-dialog-closer cm-cancel tool-link btn">{__("cancel")}</a>
{/if}

{if $r|fn_check_view_permissions}
    {if !$hide_first_button}
        {include file="buttons/button.tpl" but_text=$but_text|default:$but_label but_onclick=$but_onclick but_role=$but_role but_name=$but_name but_meta="btn-primary `$but_meta`"}
    {else}
        {assign var="skip_or" value=true}
    {/if}
    {if !$hide_second_button && $cancel_action != "close"}
        {include file="buttons/button.tpl" but_text=$but_label2 but_role=$but_role but_name=$but_name but_meta="cm-save-and-close btn-primary `$but_meta`" but_onclick=$but_onclick allow_href=true}
    {/if}
{else}
    {assign var="skip_or" value=true}
{/if}

{if $extra}
    {$extra nofilter}
{/if}
