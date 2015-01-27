{capture name="buttons"}
    <div class="right">
        {include file="buttons/button.tpl" but_text=__("continue_shopping") but_role="action" but_meta="cm-notification-close"}
    </div>
{/capture}

{capture name="info"}
    {$notification_msg nofilter}
{/capture}

{include file="views/products/components/notification.tpl" product_buttons=$smarty.capture.buttons product_info=$smarty.capture.info}
