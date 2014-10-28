{style src="addons/call_requests/styles.less"}

{capture name="styles"}
    {$statuses=fn_get_schema('call_requests', 'status_colors')}
    {if $statuses}    
        {foreach from=$statuses key="status" item="color"}
            .cr-btn-status-{$status} {
                .buttonBackground(lighten({$color}, 15%), darken({$color}, 5%));
            }
        {/foreach}
    {/if}
{/capture}

{style content=$smarty.capture.styles type="less"}
