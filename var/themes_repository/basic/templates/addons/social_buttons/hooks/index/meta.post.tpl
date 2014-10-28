{foreach from=$provider_settings item="provider_data"}
    {if $provider_data && $provider_data.meta_template}
        {include file="addons/social_buttons/meta_templates/`$provider_data.meta_template`"}
    {/if}
{/foreach}
