{if $settings.Appearance.calendar_date_format == "month_first"}
    {assign var="date_format" value="%m/%d/%Y"}
{else}
    {assign var="date_format" value="%d/%m/%Y"}
{/if}

<div class="calendar">
    <input type="text" id="{$date_id}" name="{$date_name}" class="{if $date_meta}{$date_meta}{/if} cm-calendar" value="{if $date_val}{$date_val|fn_parse_date|date_format:"`$date_format`"}{/if}" {$extra nofilter} size="10" />
    <span data-ca-external-focus-id="{$date_id}" class="icon-calendar cm-external-focus"></span>
</div>

<script type="text/javascript">
(function(_, $) {$ldelim}
    $(document).ready(function() {$ldelim}
        $('#{$date_id}').datepicker(
        {$ldelim}
            changeMonth: true,
            duration: 'fast',
            changeYear: true,
            numberOfMonths: 1,
            selectOtherMonths: true,
            showOtherMonths: true,
            firstDay: {if $settings.Appearance.calendar_week_format == "sunday_first"}0{else}1{/if},
            dayNamesMin: ['{__("weekday_abr_0")}', '{__("weekday_abr_1")}', '{__("weekday_abr_2")}', '{__("weekday_abr_3")}', '{__("weekday_abr_4")}', '{__("weekday_abr_5")}', '{__("weekday_abr_6")}'],
            monthNamesShort: ['{__("month_name_abr_1")}', '{__("month_name_abr_2")}', '{__("month_name_abr_3")}', '{__("month_name_abr_4")}', '{__("month_name_abr_5")}', '{__("month_name_abr_6")}', '{__("month_name_abr_7")}', '{__("month_name_abr_8")}', '{__("month_name_abr_9")}', '{__("month_name_abr_10")}', '{__("month_name_abr_11")}', '{__("month_name_abr_12")}'],
            yearRange: '{$start_year|default:$settings.Company.company_start_year}:{math equation="x+y" x=$end_year|default:1 y=$smarty.const.TIME|date_format:"%Y"}',
            dateFormat: '{if $settings.Appearance.calendar_date_format == "month_first"}mm/dd/yy{else}dd/mm/yy{/if}'
        {$rdelim});
    {$rdelim});
{$rdelim}(Tygh, Tygh.$));
</script>