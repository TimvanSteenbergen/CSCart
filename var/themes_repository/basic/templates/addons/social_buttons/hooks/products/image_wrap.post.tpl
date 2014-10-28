{if $display_button_block}
    <div class="clear"></div>
    <ul class="social-buttons social-buttons_ul">
    {foreach from=$provider_settings item="provider_data"}
        {if $provider_data && $provider_data.template && $provider_data.data}
        <li class="social-buttons_li clearfix">{include file="addons/social_buttons/providers/`$provider_data.template`"}</li>
        {/if}
    {/foreach}
    </ul>
{/if}
