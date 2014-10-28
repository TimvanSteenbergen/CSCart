{if !$smarty.request.extra}
<script type="text/javascript">
(function(_, $) {
    _.tr('text_items_added', '{__("text_items_added")|escape:"javascript"}');

    $.ceEvent('on', 'ce.formpost_add_users_form', function(frm, elm) {
        var users = {};

        if ($('input.cm-item:checked', frm).length > 0) {

            $('input.cm-item:checked', frm).each( function() {
                var id = $(this).val();
                var item = $(this).parent().siblings();
                users[id] = {
                    email: item.find('.user-email').text(), 
                    user_name: item.find('.user-name').text()
                };
            });

            {literal}
            $.cePicker('add_js_item', frm.data('caResultId'), users, 'u', {
                '{user_id}': '%id',
                '{email}': '%item.email',
                '{user_name}': '%item.user_name'
            });
            {/literal}
            
            $.ceNotification('show', {
                type: 'N', 
                title: _.tr('notice'), 
                message: _.tr('text_items_added'), 
                message_state: 'I'
            });
        }

        return false;        
    });
}(Tygh, Tygh.$));
</script>
{/if}

{include file="views/profiles/components/users_search_form.tpl" dispatch="profiles.picker" extra="<input type=\"hidden\" name=\"result_ids\" value=\"pagination_`$smarty.request.data_id`\">" put_request_vars=true form_meta="cm-ajax" in_popup=true}

<form action="{$smarty.request.extra|fn_url}" method="post" data-ca-result-id="{$smarty.request.data_id}" name="add_users_form">

{include file="common/pagination.tpl" save_current_page=true div_id="pagination_`$smarty.request.data_id`"}

{if $users}
<table width="100%" class="table table-middle">
<thead>
<tr>
    <th width="1%" class="center">
        {if $smarty.request.display == "checkbox"}
        {include file="common/check_items.tpl"}</th>
        {/if}
    <th>{__("id")}</th>
    {if $settings.General.use_email_as_login != "Y"}
    <th>{__("username")}</th>
    {/if}
    <th>{__("email")}</th>
    <th>{__("person_name")}</th>
    <th>{__("registered")}</th>
    <th>{__("type")}</th>
    <th class="right">{__("active")}</th>
</tr>
</thead>
{foreach from=$users item=user}
<tr>
    <td class="left">
        {if $smarty.request.display == "checkbox"}
        <input type="checkbox" name="add_users[]" value="{$user.user_id}" class="cm-item" />
        {elseif $smarty.request.display == "radio"}
        <input type="radio" name="selected_user_id" value="{$user.user_id}" />
        {/if}
    </td>
    <td>{$user.user_id}</td>
    {if $settings.General.use_email_as_login != "Y"}
    <td><span>{$user.user_login}</span></td>
    {/if}
    <td><span class="user-email">{$user.email}</span></td>
    <td><span class="user-name">{if $user.firstname || $user.lastname}{$user.firstname} {$user.lastname}{else}-{/if}</span></td>
    <td>{$user.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}</td>
    <td>{if $user.user_type == "A"}{__("administrator")}{elseif $user.user_type == "V"}{__("vendor_administrator")}{elseif $user.user_type == "C"}{__("customer")}{elseif $user.user_type == "P"}{__("affiliate")}{/if}</td>
    <td class="right">{if $user.status == "D"}{__("disable")}{else}{__("active")}{/if}</td>
</tr>
{/foreach}
</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}

{include file="common/pagination.tpl" div_id="pagination_`$smarty.request.data_id`"}

{hook name="profiles:picker_opts"}
{/hook}

<div class="buttons-container">
    {if $smarty.request.display == "radio"}
        {assign var="but_close_text" value=__("choose")}
    {else}
        {assign var="but_close_text" value=__("add_users_and_close")}
        {assign var="but_text" value=__("add_users")}
    {/if}

    {include file="buttons/add_close.tpl" is_js=$smarty.request.extra|fn_is_empty}
</div>

</form>
