{if $skip_check_permissions || $tools_list|fn_check_html_view_permissions}
<div class="btn-group {$tool_meta}">
    {if !$hide_tools && $tools_list}
    <a class="{if $override_meta}{$override_meta}{else}btn{/if} btn dropdown-toggle" data-toggle="dropdown">
        {if $link_text}
            {$link_text nofilter}
        {/if}
        {if $icon}<i class="{$icon}"></i>{/if}
        {if $caret}<span class="caret"></span>{/if}
    </a>
    <ul id="tools_list_{$prefix}" class="dropdown-menu cm-smart-position">
        {$tools_list nofilter}
    </ul>
    {/if}
    {if !$hide_actions}
        {if $tool_href|fn_check_view_permissions}
            <a class="btn cm-tooltip" {if $tool_id} id="{$tool_id}"{/if}{if $tool_href} href="{$tool_href|fn_url}"{/if}{if $tool_onclick} onclick="{$tool_onclick}; return false;"{/if} {if $title}title="{$title}"{/if}>
                {if $icon}
                    <i class="{$icon}"></i>
                {else}
                    <i class="icon-plus"></i>
                {/if}
                {$link_text nofilter}
            </a>
        {/if}
    {/if}
</div>
{/if}