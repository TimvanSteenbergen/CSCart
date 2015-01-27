<p>{$age_warning_message}</p>
<p>&nbsp;</p>

<form action="{""|fn_url}" method="post" name="age_verification">
<input type="hidden" name="redirect_url" value="{$config.current_url}" />

<div class="control-group">
    <label for="age">{__("your_age")}</label>
    <input type="text" name="age" id="age" size="10" class="input-text-short">
</div>

<div class="buttons-container">
    {include file="buttons/button.tpl" but_role="submit" but_text=__("verify") but_name="dispatch[age_verification.verify]"}
</div>

</form>

{capture name="mainbox_title"}{$category_data.category nofilter}{/capture}