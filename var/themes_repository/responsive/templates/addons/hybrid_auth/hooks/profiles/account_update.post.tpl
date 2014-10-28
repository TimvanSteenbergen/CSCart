{include file="common/subheader.tpl" title=__("hybrid_auth.link_provider")}
<p>{__("text_hybrid_auth.link_provider")}</p>

<div id="hybrid_providers">
    {foreach from=$providers_list item="provider_data"}
    <a class="{if in_array($provider_data.provider, $link_providers)}cm-unlink-provider {else}cm-link-provider ty-link-unlink-provider {/if}ty-hybrid-auth-icon" data-idp="{$provider_data.provider}">
        <img src="{$images_dir}/addons/hybrid_auth/icons/{$addons.hybrid_auth.icons_pack}/{$provider_data.provider}.png" title="{$provider_data.provider}"/>
    </a>
    {/foreach}
<!--hybrid_providers--></div>