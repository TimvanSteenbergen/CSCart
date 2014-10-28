{if $display_button_block}
    <div class="social-buttons social-buttons_blocks clearfix">
    {foreach from=$provider_settings item="provider_data"}
        {if $provider_data && $provider_data.template && $provider_data.data}
        <div class="social-buttons_wrap">{include file="addons/social_buttons/providers/`$provider_data.template`"}</div>
        {/if}
    {/foreach}
    </div>
{/if}
