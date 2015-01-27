{if $addons.social_buttons.email_enable == "Y"  && $provider_settings.email.data}

{capture name="popup_content"}
<form name="email_share_form" action="{""|fn_url}" method="post">
<input type="hidden" name="selected_section" value="{$product_tab_id}" />
<input type="hidden" name="redirect_url" value="{$config.current_url}" />

<div class="control-group">
    <label for="send_name">{__("name_of_friend")}</label>
    <input id="send_name" class="input-text" size="50" type="text" name="send_data[to_name]" value="{$send_data.to_name}" />
</div>

<div class="control-group">
    <label for="send_email" class="cm-required cm-email">{__("email_of_friend")}</label>
    <input id="send_email" class="input-text" size="50" type="text" name="send_data[to_email]" value="{$send_data.to_email}" />
</div>

<div class="control-group">
    <label for="send_yourname">{__("your_name")}</label>
    <input id="send_yourname" size="50" class="input-text" type="text" name="send_data[from_name]" value="{if $send_data.from_name}{$send_data.from_name}{elseif $auth.user_id}{$user_info.firstname} {$user_info.lastname}{/if}" />
</div>

<div class="control-group">
    <label for="send_youremail" class="cm-email">{__("your_email")}</label>
    <input id="send_youremail" class="input-text" size="50" type="text" name="send_data[from_email]" value="{if $send_data.from_email}{$send_data.from_email}{elseif $auth.user_id}{$user_info.email}{/if}" />
</div>

<div class="control-group">
    <label for="send_notes" class="cm-required">{__("your_message")}</label>
    <textarea id="send_notes"  class="input-textarea" rows="5" cols="72" name="send_data[notes]">{strip}
        {if $send_data.notes}
            {$send_data.notes}
        {elseif $product}
            {$product.product nofilter}
        {elseif $page}
            {$page.page nofilter}
        {/if}
    {/strip}</textarea>
</div>

{include file="common/image_verification.tpl" option="use_for_email_share" align="left"}

<div class="buttons-container">
    {include file="buttons/button.tpl" but_text=__("send") but_name="dispatch[share_by_email.send]" but_role="submit"}
</div>

</form>
{/capture}
{include file="common/popupbox.tpl" id="elm_email_sharing" link_text=__("sb_share") link_icon="icon-mail" link_meta="email-sharing" text=__("share_via_email") content=$smarty.capture.popup_content}

{/if}
