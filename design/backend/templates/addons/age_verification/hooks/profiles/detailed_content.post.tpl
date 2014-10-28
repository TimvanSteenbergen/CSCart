{if $user_type == "C"}
{include file="common/subheader.tpl" title=__("age_verification") target="#age_verification_fields"}
<div id="age_verification_fields" class="collapse">
    <fieldset class="form-horizontal">
        <div class="control-group">
            <label for="birthday" class="control-label">{__("birthday")}</label>
            <div class="controls">
                {include file="common/calendar.tpl" date_id="birthday" date_name="user_data[birthday]" date_val=$user_data.birthday start_year="1902" end_year="0"}
            </div>
        </div>
    </fieldset>
</div>
{/if}