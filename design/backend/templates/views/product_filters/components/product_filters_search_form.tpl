{if $in_popup}
    <div class="adv-search">
    <div class="group">
{else}
    <div class="sidebar-row">
    <h6>{__("search")}</h6>
{/if}

<form action="{""|fn_url}" name="product_filters_search_form" method="get" class="{$form_meta}">
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
        <label>{__("category")}</label>
        <div class="break clear correct-picker-but">
        {if "categories"|fn_show_picker:$smarty.const.CATEGORY_THRESHOLD}
            {if $search.category_ids}
                {assign var="s_cid" value=$search.category_ids}
            {else}
                {assign var="s_cid" value="0"}
            {/if}
            {include file="pickers/categories/picker.tpl" data_id="location_category" input_name="category_ids" item_ids=$s_cid hide_link=true hide_delete_button=true default_name=__("all_categories") extra=""}
        {else}
            {include file="common/select_category.tpl" name="category_ids" id=$search.category_ids}
        {/if}
        </div>
</div>
<div class="sidebar-field">
    <label for="elm_feature_name">{__("feature")}:</label>
    <div class="break">
        <input type="text" name="feature_name" id="elm_feature_name" value="{$search.feature_name}" size="30" class="search-input-text" />
    </div>
</div>
<div class="sidebar-field">
    <label for="elm_filter_name">{__("filter")}:</label>
    <div class="break">
        <input type="text" name="filter_name" id="elm_filter_name" value="{$search.filter_name}" size="30" class="search-input-text" />
    </div>
</div>
{/capture}

{capture name="advanced_search"}
    {hook name="product_filters:search_form"}
    {/hook}
{/capture}

{include file="common/advanced_search.tpl" simple_search=$smarty.capture.simple_search advanced_search=$smarty.capture.advanced_search dispatch=$dispatch view_type="product_filters" in_popup=$in_popup}

</form>
{if $in_popup}
    </div></div>
{else}
    </div><hr>
{/if}