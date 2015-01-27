{capture name="section"}

<form action="{""|fn_url}" name="advanced_search_form" method="get" class="{$form_meta}">
<input type="hidden" name="search_performed" value="Y" />

{if $put_request_vars}
{foreach from=$smarty.request key="k" item="v"}
{if $v && $k != "callback"}
<input type="hidden" name="{$k}" value="{$v}" />
{/if}
{/foreach}
{/if}

{$search_extra nofilter}

{hook name="products:search_query_input"}
<div class="control-group">
    <label for="match">{__("find_results_with")}</label>
    <select name="match" id="match" class="valign">
        <option {if $search.match == "any"}selected="selected"{/if} value="any">{__("any_words")}</option>
        <option {if $search.match == "all"}selected="selected"{/if} value="all">{__("all_words")}</option>
        <option {if $search.match == "exact"}selected="selected"{/if} value="exact">{__("exact_phrase")}</option>
    </select>&nbsp;&nbsp;
    <input type="text" name="q" size="38" value="{$search.q}" class="input-text-large valign" />
</div>

<div class="control-group">
    <label>{__("search_in")}</label>
    <div class="select-field">
        <label for="pname">
            <input type="hidden" name="pname" value="N" />
            <input type="checkbox" value="Y" {if $search.pname == "Y" || !$search.pname}checked="checked"{/if} name="pname" id="pname" class="checkbox" />{__("product_name")}
        </label>

        <label for="pshort">
            <input type="checkbox" value="Y" {if $search.pshort == "Y"}checked="checked"{/if} name="pshort" id="pshort" class="checkbox" />{__("short_description")}
        </label>

        <label for="pfull">
            <input type="checkbox" value="Y" {if $search.pfull == "Y"}checked="checked"{/if} name="pfull" id="pfull" class="checkbox" />{__("full_description")}
        </label>

        <label for="pkeywords">
            <input type="checkbox" value="Y" {if $search.pkeywords == "Y"}checked="checked"{/if} name="pkeywords" id="pkeywords" class="checkbox" />{__("keywords")}
        </label>

        {hook name="products:search_in"}{/hook}
    </div>
</div>
{/hook}
<div class="control-group">
    <label>{__("search_in_category")}</label>
    {if "categories"|fn_show_picker:$smarty.const.CATEGORY_THRESHOLD}
        {if $search.cid}
            {assign var="s_cid" value=$search.cid}
        {else}
            {assign var="s_cid" value="0"}
        {/if}
        {include file="pickers/categories/picker.tpl" data_id="location_category" input_name="cid" item_ids=$s_cid hide_link=true hide_delete_button=true default_name=__("all_categories") extra=""}
    {else}
    <div class="float-left">{* dont delete this div. its really needed! *}
        {assign var="all_categories" value=0|fn_get_plain_categories_tree:false}
        <select name="cid" class="valign">
            <option value="0" {if $category_data.parent_id == "0"}selected{/if}>- {__("all_categories")} -</option>
            {foreach from=$all_categories item="cat"}
            <option value="{$cat.category_id}" {if $cat.disabled}disabled="disabled"{/if}{if $search.cid == $cat.category_id} selected="selected"{/if} title="{$cat.category}">{$cat.category|escape|truncate:50:"...":true|indent:$cat.level:"&#166;&nbsp;&nbsp;&nbsp;&nbsp;":"&#166;--&nbsp;" nofilter}</option>
            {/foreach}
        </select>
    </div>
    {/if}
    <div class="select-field subcategories-field">
        <label for="subcats">
            <input type="hidden" name="subcats" value="N" />
            <input type="checkbox" value="Y"{if $search.subcats == "Y" || !$search.subcats} checked="checked"{/if} name="subcats" id="subcats" class="checkbox" />
            {__("search_in_subcategories")}
        </label>
    </div>
</div>

{if !$simple_search_form}
    {include file="common/subheader.tpl" title=__("advanced_search_options")}
    <div class="control-group">
        <input type="hidden" name="company_id" id="company_id" value="{$search.company_id|default:''}" />
        {if "MULTIVENDOR"|fn_allowed_for}
            {include file="common/ajax_select_object.tpl" label=__("search_by_vendor") data_url="companies.get_companies_list?show_all=Y&search=Y" text=$search.company_id|fn_get_company_name|default:__("all_vendors") result_elm="company_id" id="company_id_selector"}
        {/if}
    </div>
    
    <div class="control-group">
        <label for="pcode">{__("search_by_sku")}</label>
        <input type="text" name="pcode" id="pcode" value="{$search.pcode}" onfocus="this.select();" class="input-text" size="30" />
    </div>

    {assign var="have_price_filter" value=0}
    {foreach from=$filter_features item="ff"}{if $ff.field_type eq "P"}{assign var="have_price_filter" value=1}{/if}{/foreach}
    {if !$have_price_filter}
    <div class="control-group">
        <label for="price_from">{__("search_by_price")}&nbsp;({$currencies.$primary_currency.symbol nofilter})</label>
        <input type="text" name="price_from" id="price_from" value="{$search.price_from}" onfocus="this.select();" class="input-text" size="30" />&nbsp;-&nbsp;<input type="text" name="price_to" value="{$search.price_to}" onfocus="this.select();" class="input-text" size="30" />
    </div>
    {/if}

    <div class="control-group">
        <label for="weight_from">{__("search_by_weight")}&nbsp;({if $config.localization.weight_symbol}{$config.localization.weight_symbol}{else}{$settings.General.weight_symbol}{/if})</label>
        <input type="text" name="weight_from" id="weight_from" value="{$search.weight_from}" onfocus="this.select();" class="input-text" size="30" />&nbsp;-&nbsp;<input type="text" name="weight_to" value="{$search.weight_to}" onfocus="this.select();" class="input-text" size="30" />
    </div>
    
    {include file="views/products/components/product_filters_advanced_form.tpl"}
{/if}

<div class="buttons-container">
    {include file="buttons/search.tpl" but_name="dispatch[`$dispatch`]"}&nbsp;{__("or")}&nbsp;&nbsp;<a class="text-button nobg cm-reset-link">{__("reset")}</a>
</div>

</form>

{/capture}
{include file="common/section.tpl" section_title=__("search_options") section_content=$smarty.capture.section class="search-form"}
