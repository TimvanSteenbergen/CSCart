{include file="common/letter_header.tpl"}

{__("text_usergroup_request")}<br>
<p>
<table>
<tr>
    <td>{__("usergroup")}:</td>
    <td><b>{$usergroups.$usergroup_id.usergroup}</b></td>
</tr>
{if $settings.General.use_email_as_login != "Y"}
<tr>
    <td>{__("username")}:</td>
    <td>{$user_data.user_login}</td>
</tr>
{/if}
<tr>
    <td>{__("person_name")}:</td>
    <td>{$user_data.firstname}&nbsp;{$user_data.lastname}</td>
</tr>
<tr>
    <td>{__("email")}:</td>
    <td>{$user_data.email}</td>
</tr>
</table>
</p>
{include file="common/letter_footer.tpl" user_type='A'}