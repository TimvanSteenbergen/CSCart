{if $in_popup}
<div class="adv-search">
    <div class="group">
{else}
    <div class="sidebar-row">
    <h6>{__("search")}</h6>
{/if}

<form action="{""|fn_url}" name="pages_search_form" method="get" class="{$form_meta}">
<input type="hidden" name="get_tree" value="" />
{if $put_request_vars}
    {foreach from=$smarty.request key="k" item="v"}
        {if $v && $k != "callback"}
            <input type="hidden" name="{$k}" value="{$v}" />
        {/if}
    {/foreach}
{/if}

{capture name="simple_search"}
{$extra nofilter}
<div class="sidebar-field">
    <label>{__("find_results_with")}</label>
    <input type="text" name="q" size="20" value="{$search.q}" />
</div>
<div class="sidebar-field">
    <label>{__("type")}</label>
    <select class="small" name="page_type">
        <option value="">--</option>
        {foreach from=$page_types key="t" item="p"}
        <option value="{$t}" {if $search.page_type == $t}selected="selected"{/if}>{__($p.name)}</option>
        {/foreach}
    </select>
</div>
<div class="sidebar-field">
    <label>{__("parent_page")}</label>
    {if "pages"|fn_show_picker:$smarty.const.PAGE_THRESHOLD}
        {include file="pickers/pages/picker.tpl" data_id="location_page" input_name="parent_id" item_ids=$search.parent_id hide_link=true hide_delete_button=true default_name=__("all_pages") extra="" no_container=true prepend=true}
    {else}
        <select name="parent_id">
            <option value="">- {__("all_pages")} -</option>
            {foreach from=""|fn_get_pages_plain_list item="p"}
                <option value="{$p.page_id}" {if $search.parent_id == $p.page_id}selected="selected"{/if}>{$p.page|escape|truncate:35:"...":true|indent:$p.level:"&#166;&nbsp;&nbsp;&nbsp;&nbsp;":"&#166;--&nbsp;" nofilter}</option>
            {/foreach}
        </select>
    {/if}
</div>
{/capture}

{capture name="advanced_search"}
<div class="group">
    <label>{__("search_in")}</label>
    <table width="100%">
    <tr>
        <td class="select-field"><label for="pname" class="checkbox"><input type="checkbox" value="Y" {if $search.pname == "Y"}checked="checked"{/if} name="pname" id="pname"/>{__("page_name")}</label></td>
        <td>&nbsp;&nbsp;&nbsp;</td>

        <td class="select-field"><label class="checkbox" for="pdescr"><input type="checkbox" value="Y" {if $search.pdescr == "Y"}checked="checked"{/if} name="pdescr" id="pdescr" />{__("description")}</label></td>
        <td>&nbsp;&nbsp;&nbsp;</td>

        <td class="select-field"><label class="checkbox" for="subpages"><input type="checkbox" value="Y" {if $search.subpages == "Y"}checked="checked"{/if} name="subpages" id="subpages" />{__("subpages")}</label></td>
    </tr>
    </table>
</div>

<div class="group form-horizontal">
    <div class="control-group">
        <label class="control-label">{__("status")}</label>
        <div class="controls">
            <select name="status">
                <option value="">--</option>
                <option value="A" {if $search.status == "A"}selected="selected"{/if}>{__("active")}</option>
                <option value="H" {if $search.status == "H"}selected="selected"{/if}>{__("hidden")}</option>
                <option value="D" {if $search.status == "D"}selected="selected"{/if}>{__("disabled")}</option>
            </select>
        </div>
    </div>

    {math equation="rand()" assign="random_value"}
    {include file="common/select_vendor.tpl" id="company_id_`$random_value`"}

    {hook name="pages:search_form"}
    {/hook}
</div>
{/capture}

{include file="common/advanced_search.tpl" simple_search=$smarty.capture.simple_search advanced_search=$smarty.capture.advanced_search dispatch=$dispatch view_type="pages" in_popup=$in_popup}

</form>
{if $in_popup}
    </div></div>
{else}
    </div><hr>
{/if}