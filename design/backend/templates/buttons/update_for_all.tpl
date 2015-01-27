{if $display}
    {if $hide_element}
        {$title_act = __("update_for_all_hid_act")}
        {$title_dis = __("update_for_all_hid_dis")}
    {else}
        {$title_act = __("update_for_all_act")}
        {$title_dis = __("update_for_all_dis")}
    {/if}
    {if $settings.Stores.default_state_update_for_all == 'active'}
        {$title = $title_act}
        {$visible = "visible"}
    {else}
        {$title = $title_dis}
    {/if}
    {if $runtime.simple_ultimate}
        {$visible = "hidden"}
    {/if}

    <a class="cm-update-for-all-icon exicon-ufa cm-tooltip {$visible} {$meta}" title="{$title}" data-ca-title-active="{$title_act}" data-ca-title-disabled="{$title_dis}" data-ca-disable-id="{$object_id}" {if $hide_element}data-ca-hide-id="{$hide_element}"{/if}></a>
    <input type="hidden" class="cm-no-hide-input" id="hidden_update_all_vendors_{$object_id}" name="{$name}" value="Y" {if $settings.Stores.default_state_update_for_all == 'not_active' && !$runtime.simple_ultimate}disabled="disabled"{/if} />
{/if}
