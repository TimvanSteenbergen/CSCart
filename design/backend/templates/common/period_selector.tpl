{if $display != "form"}
    <div class="nowrap">
        <div class="form-inline">
{else}
    <div class="sidebar-field">
{/if}       <label for="{$prefix}period_selects">{__("period")}:</label>
            <select name="{$prefix}period" id="{$prefix}period_selects">
                <option value="A" {if $period == "A" || !$period}selected="selected"{/if}>{__("all")}</option>
                <optgroup label="=============">
                    <option value="D" {if $period == "D"}selected="selected"{/if}>{__("this_day")}</option>
                    <option value="W" {if $period == "W"}selected="selected"{/if}>{__("this_week")}</option>
                    <option value="M" {if $period == "M"}selected="selected"{/if}>{__("this_month")}</option>
                    <option value="Y" {if $period == "Y"}selected="selected"{/if}>{__("this_year")}</option>
                </optgroup>
                <optgroup label="=============">
                    <option value="LD" {if $period == "LD"}selected="selected"{/if}>{__("yesterday")}</option>
                    <option value="LW" {if $period == "LW"}selected="selected"{/if}>{__("previous_week")}</option>
                    <option value="LM" {if $period == "LM"}selected="selected"{/if}>{__("previous_month")}</option>
                    <option value="LY" {if $period == "LY"}selected="selected"{/if}>{__("previous_year")}</option>
                </optgroup>
                <optgroup label="=============">
                    <option value="HH" {if $period == "HH"}selected="selected"{/if}>{__("last_24hours")}</option>
                    <option value="HW" {if $period == "HW"}selected="selected"{/if}>{__("last_n_days", ["[N]" => 7])}</option>
                    <option value="HM" {if $period == "HM"}selected="selected"{/if}>{__("last_n_days", ["[N]" => 30])}</option>
                </optgroup>
                <optgroup label="=============">
                    <option value="C" {if $period == "C"}selected="selected"{/if}>{__("custom")}</option>
                </optgroup>
            </select>
            
{if $display != "form"}
    &nbsp;&nbsp;
{else}
    </div>
    <div class="sidebar-field">
{/if}
            <label{if $display != "form"} class="label-html"{/if}>{__("select_dates")}:</label>
            
            {assign var="time_from" value="`$prefix`time_from"}
            {assign var="time_to" value="`$prefix`time_to"}
            
            {if $display == "form"}
                {assign var="date_meta" value="input-date"}
            {else}
                {assign var="date_meta" value="input-small"}
            {/if}
            
            {include file="common/calendar.tpl" date_id="`$prefix`f_date" date_name="`$prefix`time_from" date_val=$search.$time_from  start_year=$settings.Company.company_start_year extra="onchange=\"Tygh.$('#`$prefix`period_selects').val('C');\"" date_meta=$date_meta}
            {if $display == "form"}
                -
            {else}
                &nbsp;&nbsp;-&nbsp;&nbsp;
            {/if}
            {include file="common/calendar.tpl" date_id="`$prefix`t_date" date_name="`$prefix`time_to" date_val=$search.$time_to  start_year=$settings.Company.company_start_year extra="onchange=\"Tygh.$('#`$prefix`period_selects').val('C');\"" date_meta=$date_meta}
            
{if $display != "form"}     
        </div>
    </div>
{else}
    </div>
{/if}

{script src="js/tygh/period_selector.js"}
<script type="text/javascript">
Tygh.$(document).ready(function() {$ldelim}
    Tygh.$('#{$prefix}period_selects').cePeriodSelector({$ldelim}
        from: '{$prefix}f_date',
        to: '{$prefix}t_date'
    {$rdelim});
{$rdelim});
</script>
