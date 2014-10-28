<div class="sidebar-row">
    <h6>{__("search")}</h6>
    <form action="{""|fn_url}" name="rma_search_form" method="get">
    {capture name="simple_search"}
        <div class="sidebar-field">
            <label for="cname">{__("customer")}:</label>
            <input type="text" name="cname" id="cname" value="{$search.cname}" size="30" />
        </div>

        <div class="sidebar-field">
            <label for="email">{__("email")}:</label>
            <input type="text" name="email" id="email" value="{$search.email}" size="30"/>
        </div>

        <div class="sidebar-field">
            <label for="rma_amount_from">{__("quantity")}:</label>
            <input type="text" name="rma_amount_from" id="rma_amount_from" value="{$search.rma_amount_from}" size="3" class="input-small" />&nbsp;&ndash;&nbsp;<input type="text" name="rma_amount_to" class="input-small" value="{$search.rma_amount_to}" size="3" />
        </div>
    {/capture}

{capture name="advanced_search"}

<div class="group form-horizontal">
    <div class="control-group">
    {include file="common/period_selector.tpl" period=$search.period form_name="rma_search_form"}
    </div>
</div>

<div class="row-fluid">
    <div class="group span6 form-horizontal">
        <div class="control-group">
            <label class="control-label" for="return_id">{__("rma_return")}&nbsp;{__("id")}:</label>
            <div class="controls">
                <input type="text" name="return_id" id="return_id" value="{$search.return_id}" size="30" />
            </div>
        </div>

        {if $actions}
            <div class="control-group">
                <label class="control-label" for="action">{__("action")}:</label>
                <div class="controls">
                    <select name="action" id="action">
                        <option value="0">{__("all_actions")}</option>
                        {foreach from=$actions item="action" key="action_id"}
                            <option value="{$action_id}" {if $search.action == $action_id}selected="selected"{/if}>{$action.property}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
        {/if}

        <div class="control-group">
            <label class="control-label" for="order_id">{__("order")}&nbsp;{__("id")}:</label>
            <div class="controls">
                <input type="text" name="order_id" id="order_id" value="{$search.order_id}" size="30" />
            </div>
        </div>
    </div>

    <div class="group span6">
        <div class="control-group">
            <label class="control-label">{__("return_status")}:</label>
            <div class="controls checkbox-list">
                {include file="common/status.tpl" status=$search.request_status display="checkboxes" name="request_status" status_type=$smarty.const.STATUSES_RETURN}
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">{__("order_status")}:</label>
            <div class="controls checkbox-list">
                {include file="common/status.tpl" status=$search.order_status display="checkboxes" name="order_status"}
            </div>
        </div>
    </div>
</div>

<div class="group">
    <div class="control-group">
        <div class="controls">
            {include file="common/products_to_search.tpl"}
        </div>
    </div>
</div>
{/capture}
{include file="common/advanced_search.tpl" simple_search=$smarty.capture.simple_search advanced_search=$smarty.capture.advanced_search dispatch="rma.returns" view_type="rma"}

</form>
</div>
