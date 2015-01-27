{if is_array($providers_list)}
{capture name="hybrid_auth"}

{strip}
    {foreach from=$providers_list item="provider_data"}
        {if $provider_data.status == 'A'}
        <input type="hidden" name="redirect_url" id="redirect_url" value="{$config.current_url}" />
        <a class="cm-login-provider hybrid-auth-icon" data-idp="{$provider_data.provider}"><img src="{$images_dir}/addons/hybrid_auth/icons/{$addons.hybrid_auth.icons_pack}/{$provider_data.provider}.png" title="{$provider_data.provider}" /></a>
        {/if}
    {/foreach}
{/strip}
{/capture}

{if $smarty.capture.hybrid_auth}
    {__("hybrid_auth.social_login")}:

    <p class="text-center">{$smarty.capture.hybrid_auth nofilter}</p>
{/if}

{/if}