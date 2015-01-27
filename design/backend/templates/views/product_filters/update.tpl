{if $filter}
    {assign var="id" value=$filter.filter_id}
{else}
    {assign var="id" value=0}
{/if}

{assign var="allow_save" value=true}
{if "ULTIMATE"|fn_allowed_for}
    {assign var="allow_save" value=$filter|fn_allow_save_object:"product_filters"}
{/if}

{assign var="filter_fields" value=""|fn_get_product_filter_fields}

<div id="content_group{$id}">

<form action="{""|fn_url}" name="update_filter_form_{$id}" method="post" class="form-horizontal form-edit {if ""|fn_check_form_permissions || !$allow_save} cm-hide-inputs{/if}">

<input type="hidden" class="cm-no-hide-input" name="filter_id" value="{$id}" />
<input type="hidden" class="cm-no-hide-input" name="redirect_url" value="{$smarty.request.return_url}" />

<div class="tabs cm-j-tabs">
    <ul class="nav nav-tabs">
        <li id="tab_details_{$id}" class="cm-js active"><a>{__("general")}</a></li>
        {if ($filter.feature_type && "ODN"|strpos:$filter.feature_type !== false) || ($filter.field_type && $filter_fields[$filter.field_type].is_range == true) || !$id}
            <li id="tab_variants_{$id}" class="cm-js {if !$id}hidden{/if}"><a>{__("ranges")}</a></li>
        {/if}
        <li id="tab_categories_{$id}" class="cm-js"><a>{__("categories")}</a></li>
    </ul>
</div>
<div class="cm-tabs-content" id="tabs_content_{$id}">
    <div id="content_tab_details_{$id}">
    <fieldset>
        <div class="control-group">
            <label for="elm_filter_name_{$id}" class="control-label cm-required">{__("name")}</label>
            <div class="controls">
                <input type="text" id="elm_filter_name_{$id}" name="filter_data[filter]" class="span9" value="{$filter.filter}" />
            </div>
        </div>

        {if "ULTIMATE"|fn_allowed_for}
            {include file="views/companies/components/company_field.tpl"
                name="filter_data[company_id]"
                id="elm_filter_data_`$id`"
                selected=$filter.company_id
            }
        {/if}

        <div class="control-group">
            <label class="control-label" for="elm_filter_position_{$id}">{__("position_short")}</label>
            <div class="controls">
            <input type="text" id="elm_filter_position_{$id}" name="filter_data[position]" size="3" value="{$filter.position}{if !$id}0{/if}"/>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="elm_filter_show_on_home_page_{$id}">{__("show_on_home_page")}</label>
            <div class="controls">
            <input type="hidden" name="filter_data[show_on_home_page]" value="N" />
            <input type="checkbox" id="elm_filter_show_on_home_page_{$id}" name="filter_data[show_on_home_page]" {if $filter.show_on_home_page == "Y" || !$filter}checked="checked"{/if} value="Y"/>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="elm_filter_filter_by_{$id}">{__("filter_by")}</label>
            <div class="controls">
            {if !$id}
                {* F - feature, R - range field, B - base field *}
                <select name="filter_data[filter_type]" onchange="fn_check_product_filter_type(this.value, 'tab_variants_{$id}', {$id});" id="elm_filter_filter_by_{$id}">
                {if $filter_features}
                    <optgroup label="{__("features")}">
                    {foreach from=$filter_features item=feature}
                        <option value="{if "ON"|strpos:$feature.feature_type !== false}R{elseif $feature.feature_type == "D"}D{else}F{/if}F-{$feature.feature_id}">{$feature.description}</option>
                    {if $feature.subfeatures}
                    {foreach from=$feature.subfeatures item=subfeature}
                        <option value="{if "ON"|strpos:$feature.feature_type !== false}R{elseif $feature.feature_type == "D"}D{else}F{/if}F-{$subfeature.feature_id}">{$subfeature.description}</option>
                    {/foreach}
                    {/if}
                    {/foreach}
                    </optgroup>
                {/if}
                    <optgroup label="{__("product_fields")}">
                    {foreach from=$filter_fields item="field" key="field_type"}
                        {if !$field.hidden}
                            <option value="{if $field.is_range}R{else}B{/if}-{$field_type}">{__($field.description)}</option>
                        {/if}
                    {/foreach}
                    </optgroup>
                </select>
            {else}
                <input type="hidden" name="filter_data[filter_type]" value="{if $filter.feature_id}FF-{$filter.feature_id}{else}{if $filter_fields[$filter.field_type].is_range}R{else}B{/if}-{$filter.field_type}{/if}">
                <span class="shift-input">{$filter.feature}{if $filter.feature_group} ({$filter.feature_group}){/if}</span>
            {/if}
            </div>
        </div>

        <div class="control-group{if !$filter.slider} hidden{/if}" id="round_to_{$id}_container">
            <label class="control-label" for="elm_filter_round_to_{$id}">{__("round_to")}</label>
            <div class="controls">
            <select name="filter_data[round_to]" id="elm_filter_round_to_{$id}">
                <option value="1"  {if $filter.round_to == 1}   selected="selected"{/if}>1</option>
                <option value="10" {if $filter.round_to == 10}  selected="selected"{/if}>10</option>
                <option value="100"{if $filter.round_to == 100} selected="selected"{/if}>100</option>
            </select>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="elm_filter_display_{$id}">{__("display_type")}</label>
            <div class="controls">
            <select name="filter_data[display]" id="elm_filter_display_{$id}">
                <option value="Y" {if $filter.display == 'Y'} selected="selected"{/if}>{__("expanded")}</option>
                <option value="N" {if $filter.display == 'N'} selected="selected"{/if}>{__("minimized")}</option>
            </select>
            </div>
        </div>

        <div class="control-group {if !($filter.feature_id || $filter_fields[$filter.field_type].is_range || $filter.feature == 'Vendor')} hidden{/if}" id="display_count_{$id}_container">
            <label class="control-label" for="elm_filter_display_count_{$id}">{__("display_variants_count")}</label>
            <div class="controls">
            <input type="text" id="elm_filter_display_count_{$id}" name="filter_data[display_count]" value="{$filter.display_count|default:"10"}" />
            </div>
        </div>

        <div class="control-group {if !($filter.feature_id || $filter_fields[$filter.field_type].is_range || $filter.feature == 'Vendor')} hidden{/if}" id="display_more_count_{$id}_container">
            <label class="control-label" for="elm_filter_display_more_count_{$id}">{__("display_more_variants_count")}</label>
            <div class="controls">
            <input type="text" id="elm_filter_display_more_count_{$id}" name="filter_data[display_more_count]" value="{$filter.display_more_count|default:"20"}" />
            </div>
        </div>
        
    </fieldset>
    </div>

    <div class="hidden" id="content_tab_variants_{$id}">
        <table class="table table-middle">
        <thead>
        <tr>
            <th>{__("position_short")}</th>
            <th>{__("name")}</th>
            <th>{__("range_from")}&nbsp;-&nbsp;{__("range_to")}</th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        {if $filter.ranges}
        {foreach from=$filter.ranges item="range" name="fe_f"}
        {assign var="num" value=$smarty.foreach.fe_f.iteration}
        <tr  name="sub"} id="range_item_{$id}_{$range.range_id}">
            <td>
                <input type="hidden" name="filter_data[ranges][{$num}][range_id]" value="{$range.range_id}" />
                <input type="text" name="filter_data[ranges][{$num}][position]" size="3" value="{$range.position}"  />
            </td>
            <td><input type="text" name="filter_data[ranges][{$num}][range_name]" value="{$range.range_name}" /></td>
            <td class="nowrap">
                {if $features[$filter.feature_id].prefix}{$features[$filter.feature_id].prefix}&nbsp;{/if}
                {if $filter.feature_type !== "D"}
                    <input type="text" name="filter_data[ranges][{$num}][from]" size="3" value="{$range.from}" class="cm-value-decimal" />&nbsp;-&nbsp;<input type="text" name="filter_data[ranges][{$num}][to]" size="3" value="{$range.to}" class="cm-value-decimal" />
                {else}
                    {include file="common/calendar.tpl" date_id="date_1_`$id`_`$range.range_id`" date_name="filter_data[dates_ranges][`$num`][from]" date_val=$range.from|default:$smarty.const.TIME start_year=$settings.Company.company_start_year}&nbsp;-&nbsp;
                    {include file="common/calendar.tpl" date_id="date_2_`$id`_`$range.range_id`" date_name="filter_data[dates_ranges][`$num`][to]" date_val=$range.to|default:$smarty.const.TIME start_year=$settings.Company.company_start_year}
                {/if}
                {if $features[$filter.feature_id].suffix}&nbsp;{$features[$filter.feature_id].suffix}{/if}</td>
            <td class="right">
                {include file="buttons/multiple_buttons.tpl" item_id="range_item_`$id`_`$range.range_id`" tag_level="1" only_delete="Y"}
            </td>
        </tr>
        {/foreach}
        {/if}
        
        {math equation="x + 1" assign="num" x=$num|default:0}
        <tr id="box_add_to_range_{$id}">
            <td class="nowrap">
                <input type="text" name="filter_data[ranges][{$num}][position]" size="3" value="0" />
            </td>
            <td><input type="text" name="filter_data[ranges][{$num}][range_name]" /></td>
            <td class="nowrap">
                {if !$id || $filter.feature_type != "D"}
                <div id="inputs_ranges{$id}" {if $filter.feature_type == "D"}class="hidden"{/if}>
                    <input type="text" name="filter_data[ranges][{$num}][from]" value="" class="input-text-medium cm-value-decimal" />&nbsp;-&nbsp;<input type="text" name="filter_data[ranges][{$num}][to]" value="" class="cm-value-decimal" />
                </div>
                {/if}
                {if !$id || $filter.feature_type == "D"}
                <div id="dates_ranges{$id}" {if !$id || $filter.feature_type != "D"}class="hidden"{/if}>
                    {include file="common/calendar.tpl" date_id="date_3_`$id`" date_name="filter_data[dates_ranges][`$num`][from]" date_val=$smarty.const.TIME start_year=$settings.Company.company_start_year}&nbsp;-&nbsp;
                    {include file="common/calendar.tpl" date_id="date_4_`$id`" date_name="filter_data[dates_ranges][`$num`][to]" date_val=$smarty.const.TIME start_year=$settings.Company.company_start_year}
                </div>
                {/if}
            </td>
            <td>
                {include file="buttons/multiple_buttons.tpl" item_id="add_to_range_`$id`" tag_level="1"}</td>
        </tr>
        </table>
    </div>

    <div class="hidden" id="content_tab_categories_{$id}">
        {include file="pickers/categories/picker.tpl" company_ids=$picker_selected_companies multiple=true input_name="filter_data[categories_path]" item_ids=$filter.categories_path data_id="category_ids_`$id`" no_item_text=__("text_all_categories_included") use_keys="N" owner_company_id=$filter.company_id but_meta="pull-right"}
    </div>
</div>

<div class="buttons-container">
    {if "ULTIMATE"|fn_allowed_for && !$allow_save}
        {assign var="hide_first_button" value=true}
    {/if}
    {include file="buttons/save_cancel.tpl" but_name="dispatch[product_filters.update]" cancel_action="close" hide_first_button=$hide_first_button save=$id}
</div>

</form>
<!--content_group{$id}--></div>

{if !$id}
<script type="text/javascript">
    fn_check_product_filter_type(Tygh.$('#elm_filter_filter_by_{$id}').val(), 'tab_variants_{$id}', '{$id}');
</script>
{/if}
