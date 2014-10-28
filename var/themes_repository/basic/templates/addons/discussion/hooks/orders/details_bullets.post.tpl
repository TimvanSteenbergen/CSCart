{assign var="discussion" value=$order_info.order_id|fn_get_discussion:"O"}
{if $addons.discussion.order_initiate == "Y" && !$discussion}
    <li><i class="icon-chat"></i><a href="{"orders.initiate_discussion?order_id=`$order_info.order_id`"|fn_url}" class="orders-communication-start">{__("start_communication")}</a></li>
{/if}