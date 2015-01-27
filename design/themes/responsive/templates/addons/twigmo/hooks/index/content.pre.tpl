{assign var="state" value=$smarty.session.twg_state}
{$addon_images_path = fn_twg_get_images_path()}

{if $state.twg_can_be_used and !$state.mobile_link_closed}
<div class="mobile-avail-notice">
    <div class="buttons-container">
        <a href="{$config.current_url|fn_query_remove:"mobile":"auto":"desktop"|fn_link_attach:"mobile"}">
            {__('twg_visit_our_mobile_store')}
        </a>

        {if $state.device == "android" and $state.settings.url_on_googleplay}
            <a href="{$state.settings.url_on_googleplay}">{__('twg_app_for_android')}</a>
        {elseif ($state.device == "iphone" or $state.device == "ipad") and $state.settings.url_on_appstore}
            <a href="{$state.settings.url_on_appstore}">
                {if $state.device == "iphone"}
                    {__('twg_app_for_iphone')}
                {else}
                    {__('twg_app_for_ipad')}
                {/if}
            </a>
        {/if}
        <span id="close_notification_mobile_avail_notice" class="cm-notification-close hand close" title="Close" />&times;</span>
    </div>
</div>
{/if}