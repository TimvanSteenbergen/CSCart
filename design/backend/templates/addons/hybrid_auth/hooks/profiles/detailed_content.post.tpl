{if $providers_list}
{include file="common/subheader.tpl" title=__("hybrid_auth.link_provider")}

<div id="hybrid_providers">
{foreach from=$providers_list item="provider_data" key="provider_id"}

    <span class="hybrid-auth-icon{if !in_array($provider_id, $link_providers)} link-unlink-provider{/if}">
        <img src="{$images_dir}/addons/hybrid_auth/icons/flat_32x32/{$provider_id}.png" title="{$provider_id}" />
    </span>

{/foreach}
<!--hybrid_providers--></div>
{/if}