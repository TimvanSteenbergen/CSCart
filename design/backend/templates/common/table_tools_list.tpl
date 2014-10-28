{if $popup}
    {if $skip_check_permissions || $href|fn_check_view_permissions}
        {include file="common/popupbox.tpl" id=$id text=$text link_text=$link_text act=$act href=$href link_class=$link_class}
    {/if}
{elseif $href}
{assign var="_href" value=$href|fn_url}
{if !$_href|fn_check_view_permissions}
    {assign var="link_text" value=__("view")}
{/if}

{if $act == "link"}
    <a href="{$_href}" {$link_extra nofilter} class="{$extra_class} cm-tooltip">{$link_text}</a>
{else}
    <a href="{$_href}" {$link_extra nofilter} class="icon-edit {$extra_class} cm-tooltip" title="{$link_text|default:__("edit")}"></a>
{/if}

{/if}
{if $skip_check_permissions || $tools_list|fn_check_view_permissions}
    {$tools_list nofilter}
{/if}