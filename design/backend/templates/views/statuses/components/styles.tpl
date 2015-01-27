{capture name="styles"}
{$type=$type|default:$smarty.const.STATUSES_ORDER}
{$statuses=$type|fn_get_statuses}
{if $statuses}	
    {foreach from=$statuses key="status" item="status_data"}
        .{$type|lower}-status-{$status|lower} {
            .buttonBackground(lighten({$status_data.params.color}, 15%), darken({$status_data.params.color}, 5%));
        }
    {/foreach}
{/if}
{/capture}

{style content=$smarty.capture.styles type="less"}
