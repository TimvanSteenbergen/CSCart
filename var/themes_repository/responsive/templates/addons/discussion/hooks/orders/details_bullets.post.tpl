{assign var="discussion" value=$order_info.order_id|fn_get_discussion:"O"}
{if $addons.discussion.order_initiate == "Y" && !$discussion}
    {include file="buttons/button.tpl" but_meta="ty-btn__text" but_role="text" but_text=__("start_communication") but_href="orders.initiate_discussion?order_id=`$order_info.order_id`" but_icon="ty-orders__actions-icon ty-icon-chat"}
{/if}