<div class="sidebar-row">
<h6>{__("search")}</h6>
<form action="{""|fn_url}" name="tags_search_form" method="get">
    {capture name="simple_search"}
    <div class="sidebar-field">
        <label for="elm_tag">{__("tag")}</label>
        <input type="text" id="elm_tag" name="tag" size="20" value="{$search.tag}" onfocus="this.select();" class="input-text" />
    </div>

    <div class="sidebar-field">
        <label for="tag_status_identifier">{__("show")}</label>
        <select name="status" id="tag_status_identifier">
            <option value="">{__("all")}</option>
            <option value="A"{if $search.status == "A"} selected="selected"{/if}>{__("approved")}</option>1
            <option value="D"{if $search.status == "D"} selected="selected"{/if}>{__("disapproved")}</option>
            <option value="P"{if $search.status == "P"} selected="selected"{/if}>{__("pending")}</option>
        </select>
    </div>
    {/capture}

    {capture name="advanced_search"}
    <div class="group form-horizontal">
        <div class="control-group">
            <label class="control-label">{__("period")}</label>
            <div class="controls">
                {include file="common/period_selector.tpl" period=$search.period form_name="tags_search_form"}
            </div>
        </div>
    </div>
    
    {hook name="tags:search_form"}
    {/hook}
    
    {/capture}
    
    {include file="common/advanced_search.tpl" simple_search=$smarty.capture.simple_search advanced_search=$smarty.capture.advanced_search dispatch=$dispatch view_type="tags"}
    
    </form>
</div>