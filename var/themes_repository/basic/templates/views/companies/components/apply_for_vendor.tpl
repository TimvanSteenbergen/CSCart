{capture name="buttons"}
    <div class="float-right">
        {include file="buttons/button.tpl" but_text=__("close") but_extra_class="cm-notification-close"}
    </div>
{/capture}
{capture name="info"}    

    <div class="clearfix">
        <br>
        <div class="float-left"> {__('text_successful_request')}</div>
         
    </div>
{/capture}
{include file="views/products/components/notification.tpl" product_buttons=$smarty.capture.buttons product_info=$smarty.capture.info}