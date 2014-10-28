{include file="common/letter_header.tpl"}

{__("hello")},<br /><br />{assign var="_url" value="profiles.update?user_id=`$user_data.user_id`"|fn_url:'A':'http':$smarty.const.CART_LANGUAGE:true}
{if $settings.General.use_email_as_login == "Y"}
    {assign var="user_login" value=$user_data.email}
{else}
    {assign var="user_login" value=$user_data.user_login}
{/if}
{__("text_new_user_activation", ["[user_login]" => $user_login, "[url]" => "<a href=\"`$_url`\">`$_url`</a>"])}

{include file="common/letter_footer.tpl" user_type='A'}