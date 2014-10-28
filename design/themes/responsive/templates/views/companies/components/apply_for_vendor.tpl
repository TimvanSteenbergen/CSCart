{capture name="buttons"}
    <div class="ty-float-right">
        {include file="buttons/button.tpl" but_text=__("close") but_meta="ty-btn__tertiary cm-notification-close"}
    </div>
{/capture}
{capture name="info"}

    <div class="clearfix">
        <br />
        <div class="ty-float-left"> {__('text_successful_request')}</div>
         
    </div>
{/capture}
{include file="views/products/components/notification.tpl" product_buttons=$smarty.capture.buttons product_info=$smarty.capture.info}