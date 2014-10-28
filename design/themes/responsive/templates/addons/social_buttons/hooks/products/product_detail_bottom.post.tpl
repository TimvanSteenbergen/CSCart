{if $display_button_block}
    <ul class="ty-social-buttons">
    {foreach from=$provider_settings item="provider_data"}
        {if $provider_data && $provider_data.template && $provider_data.data}
            <li class="ty-social-buttons__inline">{include file="addons/social_buttons/providers/`$provider_data.template`"}</li>
        {/if}
    {/foreach}
    </ul>
{/if}
