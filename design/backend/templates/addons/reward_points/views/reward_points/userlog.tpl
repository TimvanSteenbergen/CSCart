{script src="js/tygh/tabs.js"}
{capture name="mainbox"}

{** userlog description section **}
{assign var="c_url" value=$config.current_url|fn_query_remove:"sort_by":"sort_order"}

<form action="{""|fn_url}" method="post" name="userlog_form" class="" enctype="multipart/form-data">
    <input type="hidden" name="user_id" value="{$smarty.request.user_id}">

    {include file="common/pagination.tpl" save_current_url=true}
    
    {assign var="c_icon" value="<i class=\"exicon-`$search.sort_order_rev`\"></i>"}
    {assign var="c_dummy" value="<i class=\"exicon-dummy\"></i>"}

    {if $userlog}
    <table class="table table-middle">
    <thead>
        <tr>
            <th width="5%" class="left">
                {include file="common/check_items.tpl"}</th>
            <th width="15%"><a class="cm-ajax{if $search.sort_by == "timestamp"} sort-link-{$search.sort_order_rev}{/if}" href="{"`$c_url`&sort_by=timestamp&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("date")} {if $search.sort_by == "timestamp"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
            <th width="10%"><a class="cm-ajax{if $search.sort_by == "amount"} sort-link-{$search.sort_order_rev}{/if}" href="{"`$c_url`&sort_by=amount&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("points")} {if $search.sort_by == "amount"}{$c_icon nofilter}{else}{$c_dummy nofilter}{/if}</a></th>
            <th width="60%">{__("reason")}</th>
            <th width="10%">&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$userlog item="ul"}
        <tr>
            <td class="left">
                <input type="checkbox" name="change_ids[]" value="{$ul.change_id}" class="cm-item"></td>
            <td>{$ul.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}</td>
            <td>{$ul.amount}</td>
            <td>
                {if $ul.action == $smarty.const.CHANGE_DUE_ORDER}
                    {assign var="statuses" value=$smarty.const.STATUSES_ORDER|fn_get_simple_statuses:true:true}
                    {assign var="reason" value=$ul.reason|unserialize}
                    {assign var="order_exist" value=$reason.order_id|fn_get_order_name}
                    
                    {__("order")}&nbsp;{if $order_exist}<a href="{"orders.details?order_id=`$reason.order_id`"|fn_url}" class="underlined">{/if}<span>#{$reason.order_id}</span>{if $order_exist}</a>{/if}:&nbsp;{$statuses[$reason.from]}&nbsp;&#8212;&#8250;&nbsp;{$statuses[$reason.to]}{if $reason.text}&nbsp;({__($reason.text)}){/if}
                    
                {elseif $ul.action == $smarty.const.CHANGE_DUE_USE}
                    {assign var="order_exist" value=$ul.reason|fn_get_order_name}
                    {__("text_points_used_in_order")}: {if $order_exist}<a href="{"orders.details?order_id=`$ul.reason`"|fn_url}">{/if}<span>#{$ul.reason}</span>{if $order_exist}</a>{/if}
                {elseif $ul.action == $smarty.const.CHANGE_DUE_ORDER_DELETE}
                    {assign var="reason" value=$ul.reason|unserialize}
                    {__("order")} <span>#{$reason.order_id}</span>: {__("deleted")}
                {elseif $ul.action == $smarty.const.CHANGE_DUE_ORDER_PLACE}
                    {assign var="reason" value=$ul.reason|unserialize}
                    {assign var="order_exist" value=$reason.order_id|fn_get_order_name}
                    {__("order")} {if $order_exist}<a href="{"orders.details?order_id=`$reason.order_id`"|fn_url}" class="underlined">{/if}<span>#{$reason.order_id}</span>{if $order_exist}</a>{/if}: {__("placed")}
                {else}
                    {hook name="reward_points:userlog"}
                        {$ul.reason}
                    {/hook}
                {/if}
            </td>
            <td class="nowrap right">
                <div class="hidden-tools">
                    {capture name="tools_list"}
                        <li>{btn type="delete" href="reward_points.delete?user_id=`$smarty.request.user_id`&change_id=`$ul.change_id`" class="cm-confirm"}</li>
                    {/capture}
                    {dropdown content=$smarty.capture.tools_list}
                </div>
            </td>
        </tr>
        {/foreach}
    </tbody>
    </table>
    {else}
        <p class="no-items">{__("no_data")}</p>
    {/if}

{include file="common/pagination.tpl"}

</form>
{** / userlog description section **}


{capture name="sidebar"}
    <div class="sidebar-row">
    <h6>{__("log")}</h6>
    <ul class="unstyled">
        <li><strong>{__("customer")}:</strong>  <a href="{"profiles.update?user_id=`$user.user_id`"|fn_url}">{$user.firstname} {$user.lastname}</a></li>
        <li><strong>{__("points")}:</strong> {if $user.points}{$user.points}{else}0{/if}</li>
    </ul>
    </div>
{/capture}

{** Change points section **}

{capture name="buttons"}
    {if $userlog}
        {capture name="tools_list"}
            <li>{btn type="delete_selected" dispatch="dispatch[reward_points.m_delete]" form="userlog_form"}</li>
            <li>{btn type="delete" text=__("cleanup_log") class="cm-submit" dispatch="dispatch[reward_points.cleanup_logs]" form="userlog_form"}</li>
        {/capture}
        {dropdown content=$smarty.capture.tools_list}
    {/if}
{/capture}

{capture name="adv_buttons"}
    {capture name="add_new_picker"}
        <form action="{""|fn_url}" method="post" name="reward_points_form" enctype="multipart/form-data" class="">
            <input type="hidden" name="user_id" value="{$smarty.request.user_id}">
            <input type="hidden" name="redirect_url" value="{$config.current_url}">

            <div class="tabs cm-j-tabs">
                <ul class="nav nav-tabs">
                    <li id="tab_general" class="cm-js active"><a>{__("general")}</a></li>
                </ul>
            </div>

            <div class="cm-tabs-content" id="content_tab_general">
                <fieldset class="form-horizontal">
                    <div class="control-group">
                        <label class="control-label">{__("action")}:</label>
                        <div class="controls">
                            <label for="reason_action_A" class="radio inline">
                                <input type="radio" name="reason[action]" id="reason_action_A" value="A" checked="checked">
                                {__("add")}
                            </label>
                            <label for="reason_action_S" class="radio inline">
                                <input type="radio" name="reason[action]" id="reason_action_S" value="S">
                                {__("subtract")}
                            </label>
                        </div>
                    </div>

                    <div class="control-group">
                        <label for="reason_amount" class="cm-required control-label">{__("value")}:</label>
                        <div class="controls">
                            <input type="text" value="" name="reason[amount]" id="reason_amount" class="input-text" size="5" />
                        </div>
                    </div>

                    <div class="control-group">
                        <label  class="control-label" for="reason_reason">{__("reason")}:</label>
                        <div class="controls">
                            <textarea name="reason[reason]" id="reason_reason" cols="55" rows="8" class="input-textarea-long"></textarea>
                        </div>
                    </div>

                    <div class="control-group">
                        <label  class="control-label" for="notify_user">{__("notify_customer")}:</label>
                        <div class="controls">
                            <input type="checkbox" name="notify_user" value="Y" checked="checked" id="notify_user" class="checkbox" />
                        </div>
                    </div>
                </fieldset>
            </div>

            <div class="buttons-container">
                {include file="buttons/save_cancel.tpl" but_name="dispatch[reward_points.change_points]" cancel_action="close" but_text=__("change")}
            </div>
        </form>
    {/capture}
    {include file="common/popupbox.tpl" id="change_points" text=__("change_points") content=$smarty.capture.add_new_picker title=__("add_subtract_points") act="general" icon="icon-plus"}
{/capture}

{** /Change points section **}
{/capture}
{include file="common/mainbox.tpl" title=__("reward_points_log") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons sidebar=$smarty.capture.sidebar adv_buttons=$smarty.capture.adv_buttons}
