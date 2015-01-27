<div class="ty-age-verification-birthday">
    {if !$nothing_extra}
        {include file="common/subheader.tpl" title=__("age_verification")}
    {/if}

    <div class="ty-control-group">
        <label class="ty-control-group__title" for="birthday">{__("birthday")}</label>
        {include file="common/calendar.tpl" date_id="birthday" date_name="user_data[birthday]" date_val=$user_data.birthday start_year="1902" end_year="0"}
    </div>
</div>