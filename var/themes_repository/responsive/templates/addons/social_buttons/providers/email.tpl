{if $addons.social_buttons.email_enable == "Y"  && $provider_settings.email.data}

{capture name="popup_content"}
<form name="email_share_form" action="{""|fn_url}" method="post">
<input type="hidden" name="selected_section" value="{$product_tab_id}" />
<input type="hidden" name="redirect_url" value="{$config.current_url}" />

<div class="ty-control-group">
    <label class="ty-control-group__title" for="send_name">{__("name_of_friend")}</label>
    <input id="send_name" class="ty-input-text" size="50" type="text" name="send_data[to_name]" value="{$send_data.to_name}" />
</div>

<div class="ty-control-group">
    <label for="send_email" class="ty-control-group__title cm-required cm-email">{__("email_of_friend")}</label>
    <input id="send_email" class="ty-input-text" size="50" type="text" name="send_data[to_email]" value="{$send_data.to_email}" />
</div>

<div class="ty-control-group">
    <label class="ty-control-group__title" for="send_yourname">{__("your_name")}</label>
    <input id="send_yourname" size="50" class="ty-input-text" type="text" name="send_data[from_name]" value="{if $send_data.from_name}{$send_data.from_name}{elseif $auth.user_id}{$user_info.firstname} {$user_info.lastname}{/if}" />
</div>

<div class="ty-control-group">
    <label for="send_youremail" class="ty-control-group__title cm-email">{__("your_email")}</label>
    <input id="send_youremail" class="ty-input-text" size="50" type="text" name="send_data[from_email]" value="{if $send_data.from_email}{$send_data.from_email}{elseif $auth.user_id}{$user_info.email}{/if}" />
</div>

<div class="ty-control-group">
    <label for="send_notes" class="ty-control-group__title cm-required">{__("your_message")}</label>
    <textarea id="send_notes"  class="ty-input-textarea" rows="5" cols="72" name="send_data[notes]">{strip}
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
{include file="common/popupbox.tpl" id="elm_email_sharing" link_text=__("sb_share") link_icon="ty-social-buttons__email-icon ty-icon-mail" link_meta="ty-social-buttons__email-sharing" text=__("share_via_email") content=$smarty.capture.popup_content}

{/if}
