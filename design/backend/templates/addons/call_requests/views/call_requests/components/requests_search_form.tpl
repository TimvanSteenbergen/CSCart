<div class="sidebar-row">
<h6>{__("search")}</h6>

{if $page_part}
    {assign var="_page_part" value="#`$page_part`"}
{/if}

<form action="{""|fn_url}{$_page_part}" name="{$product_search_form_prefix}search_form" method="get" class="cm-disable-empty {$form_meta}">
<input type="hidden" name="type" value="{$search_type|default:"simple"}" />
{if $smarty.request.redirect_url}
    <input type="hidden" name="redirect_url" value="{$smarty.request.redirect_url}" />
{/if}
{if $selected_section != ""}
    <input type="hidden" id="selected_section" name="selected_section" value="{$selected_section}" />
{/if}

{if $put_request_vars}
{foreach from=$smarty.request key="k" item="v"}
{if $v && $k != "callback"}
<input type="hidden" name="{$k}" value="{$v}" />
{/if}
{/foreach}
{/if}

{$extra nofilter}

{capture name="simple_search"}
    <div class="sidebar-field">
        <label>{__("id")}</label>
        <input type="text" name="id" size="20" value="{$search.id}" />
    </div>

    <div class="sidebar-field">
        <label>{__("person_name")}</label>
        <input type="text" name="name" size="20" value="{$search.name}" />
    </div>

    <div class="sidebar-field">
        <label>{__("phone")}</label>
        <input type="text" name="phone" size="20" value="{$search.phone}" />
    </div>

{/capture}

{capture name="advanced_search"}

<div class="row-fluid">
    <div class="group span6 form-horizontal">

        <div class="control-group">
            <label for="status" class="control-label">{__("status")}</label>
            <div class="controls">
                <select name="status" id="status">
                    <option value="">--</option>
                    {foreach from=$call_request_statuses item=status key=key}
                        <option value="{$key}" {if $search.status == $key}selected="selected"{/if}>{$status}</option>
                    {/foreach}
                </select>
            </div>
        </div>

        <div class="control-group">
            <label for="order_exists" class="control-label">{__("call_requests.order_exists")}</label>
            <div class="controls">
                <select name="order_exists" id="order_exists">
                    <option value="">--</option>
                    <option value="Y" {if $search.order_exists == "Y"}selected="selected"{/if}>{__("yes")}</option>
                    <option value="N" {if $search.order_exists == "N"}selected="selected"{/if}>{__("no")}</option>
                </select>
            </div>
        </div>

        <div class="control-group">
            <label for="order_status" class="control-label">{__("order_status")}</label>
            <div class="controls">
                <select name="order_status" id="order_status">
                    <option value="">--</option>
                    {foreach from=$order_statuses key=key item=status}
                        <option value="{$key}" {if $search.order_status == $key}selected="selected"{/if}>{$status.description}</option>
                    {/foreach}
                </select>
            </div>
        </div>

        <div class="control-group">
            <label for="user_id" class="control-label">{__("call_requests.responsible")}</label>
            <div class="controls">
                <select name="user_id" id="user_id">
                    <option value="">--</option>
                    {foreach from=$responsibles key=user_id item=name}
                        <option value="{$user_id}" {if $search.user_id == $user_id}selected="selected"{/if}>{$name}</option>
                    {/foreach}
                </select>
            </div>
        </div>

    </div>

</div>
{/capture}

{include file="common/advanced_search.tpl" simple_search=$smarty.capture.simple_search advanced_search=$smarty.capture.advanced_search dispatch=$dispatch view_type="products"}

</form>

</div><hr>
