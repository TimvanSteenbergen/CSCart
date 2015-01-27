{if is_array($providers_list)}
    {if !isset($redirect_url)}
        {assign value= $config.current_url var="redirect_url"}
    {/if}
    {__("hybrid_auth.social_login")}:
    <p class="ty-text-center">{$smarty.capture.hybrid_auth nofilter}
    {strip}
	{foreach from=$providers_list item="provider_data"}
        {if $provider_data.status == 'A'}
        <input type="hidden" name="redirect_url" id="redirect_url" value="{$redirect_url}" />
        <a class="cm-login-provider ty-hybrid-auth-icon" data-idp="{$provider_data.provider}"><img src="{$images_dir}/addons/hybrid_auth/icons/{$addons.hybrid_auth.icons_pack}/{$provider_data.provider}.png" title="{$provider_data.provider}" /></a>
	    {/if}
    {/foreach}
    {/strip}
    </p>
{/if}