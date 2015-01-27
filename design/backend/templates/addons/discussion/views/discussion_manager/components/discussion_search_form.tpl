<div class="sidebar-row">
<h6>{__("search")}</h6>

<form action="{""|fn_url}" name="discussion_search_form" method="get">
<input type="hidden" name="object_type" id="obj_type" value="{$search.object_type|default:"P"}">
<input type="hidden" name="dispatch" value="discussion_manager.manage">

{capture name="simple_search"}
            <div class="sidebar-field">
                <label for="author">{__("author")}</label>
                <input type="text" class="input-text" id="author" name="name" value="{$search.name}">
            </div>
            
            <div class="sidebar-field">
                <label for="message">{__("message")}</label>
                <input type="text" class="input-text" id="message" name="message" value="{$search.message}">
            </div>
            
            <div class="sidebar-field">
                <label for="rating_value">{__("rating")}</label>
                <select name="rating_value" id="rating_value" class="input-medium">
                <option value="">--</option>
                    <option value="5" {if $search.rating_value == "5"}selected="selected"{/if}>{__("excellent")}</option>
                    <option value="4" {if $search.rating_value == "4"}selected="selected"{/if}>{__("very_good")}</option>
                    <option value="3" {if $search.rating_value == "3"}selected="selected"{/if}>{__("average")}</option>
                    <option value="2" {if $search.rating_value == "2"}selected="selected"{/if}>{__("fair")}</option>
                    <option value="1" {if $search.rating_value == "1"}selected="selected"{/if}>{__("poor")}</option>
                </select>
            </div>
            
            <div class="sidebar-field">
                <label for="discussion_type">{__("discussion")}</label>
                <select name="type" id="discussion_type">
                    <option value="">--</option>
                    <option value="B" {if $search.type == "B"}selected="selected"{/if}>{__("rating")} & {__("communication")}</option>
                    <option value="R" {if $search.type == "R"}selected="selected"{/if}>{__("rating")}</option>
                    <option value="C" {if $search.type == "C"}selected="selected"{/if}>{__("communication")}</option>
                </select>
            </div>               
{/capture}

{capture name="advanced_search"}
<div class="group form-horizontal">
    <div class="control-group">
    <label class="control-label">{__("period")}</label>
    <div class="controls">
        {include file="common/period_selector.tpl" period=$search.period form_name="discussion_search_form"}
    </div>
</div>
</div>

<div class="group form-horizontal">
<div class="control-group">
    <label class='control-label' for="ip_address">{__("ip_address")}</label>
    <div class="controls">
    <input type="text" id="ip_address" name="ip_address" value="{$search.ip_address}" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="status">{__("approved")}</label>
    <div class="controls">
    <select name="status" id="status">
        <option value="">--</option>
        <option value="A" {if $search.status == "A"}selected="selected"{/if}>{__("yes")}</option>
        <option value="D" {if $search.status == "D"}selected="selected"{/if}>{__("no")}</option>
    </select>
    </div>
</div>
</div>
<div class="group form-horizontal">
<div class="control-group">
    <label class="control-label" for="sort_by">{__("sort_by")}</label>
    <div class="controls">
    <select name="sort_by" id="sort_by" class="input-small">
        <option {if $search.sort_by == "name"}selected="selected"{/if} value="name">{__("author")}</option>
        <option {if $search.sort_by == "status"}selected="selected"{/if} value="status">{__("approved")}</option>
        <option {if $search.sort_by == "timestamp"}selected="selected"{/if} value="timestamp">{__("date")}</option>
        <option {if $search.sort_by == "ip_address"}selected="selected"{/if} value="ip_address">{__("ip_address")}</option>
    </select>

    <select name="sort_order" class="input-small">
        <option {if $search.sort_order_rev == "desc"}selected="selected"{/if} value="desc">{__("desc")}</option>
        <option {if $search.sort_order_rev == "asc"}selected="selected"{/if} value="asc">{__("asc")}</option>
    </select>
    </div>
</div>
</div>

{/capture}

{include file="common/advanced_search.tpl" simple_search=$smarty.capture.simple_search advanced_search=$smarty.capture.advanced_search dispatch="discussion_manager.manage" view_type="discussion"}

</form>

</div>
<hr>