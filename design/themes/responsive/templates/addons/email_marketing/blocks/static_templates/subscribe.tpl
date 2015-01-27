{** block-description:email_marketing.tmpl_subscription **}
{if $addons.email_marketing}
<div class="ty-footer-form-block">
    <form action="{""|fn_url}" method="post" name="subscribe_form">
        <input type="hidden" name="redirect_url" value="{$config.current_url}" />

        <h3 class="ty-footer-form-block__title">{__("stay_connected")}</h3>
        <div class="ty-footer-form-block__form ty-control-group ty-input-append">
            <label class="cm-required cm-email hidden" for="elm_subscr_email{$block.block_id}">{__("email")}</label>
            <input type="text" name="subscribe_email" id="elm_subscr_email{$block.block_id}" size="20" value="{__("enter_email")}" class="cm-hint ty-input-text" />
            {include file="buttons/go.tpl" but_name="em_subscribers.update" alt=__("go")}
        </div>
    </form>
</div>
{/if}    
