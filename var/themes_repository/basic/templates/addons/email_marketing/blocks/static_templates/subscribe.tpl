{** block-description:email_marketing.tmpl_subscription **}
{if $addons.email_marketing}
    <div class="subscribe-block">

    <form action="{""|fn_url}" method="post" name="subscribe_form">
        <input type="hidden" name="redirect_url" value="{$config.current_url}" />
    
        <p><span>{__("stay_connected")}</span></p>
        <p class="subscribe-notice">{__("stay_connected_notice")}</p>
    
        <div class="control-group input-append subscribe">
            <label class="cm-required cm-email hidden" for="elm_subscr_email{$block.block_id}">{__("email")}</label>
            <input type="text" name="subscribe_email" id="elm_subscr_email{$block.block_id}" size="20" value="{__("enter_email")}" class="cm-hint subscribe-email input-text input-text-menu" />
            {include file="buttons/go.tpl" but_name="em_subscribers.update" alt=__("go")}
        </div>
    </form>

    </div>
{/if}