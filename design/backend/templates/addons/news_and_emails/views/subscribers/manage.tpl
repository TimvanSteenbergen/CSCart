{script src="js/tygh/tabs.js"}

{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="subscribers_form">
{include file="common/pagination.tpl" save_current_page=true save_current_url=true}
{if $subscribers}
<table width="100%" class="table table-middle">
<thead>
<tr>
    <th width="1%">
        {include file="common/check_items.tpl"}</th>
    <th>{__("email")}</th>
    <th>{__("language")}</th>
    <th>{__("registered")}</th>
    <th>&nbsp;</th>
    <th>&nbsp;</th>
</tr>
</thead>
{foreach from=$subscribers item="s"}
<tbody>
<tr>
    <td>
           <input type="checkbox" name="subscriber_ids[]" value="{$s.subscriber_id}" class="cm-item" /></td>
    <td><input type="hidden" name="subscribers[{$s.subscriber_id}][email]" value="{$s.email}" />
        <span name="plus_minus" id="on_subscribers_{$s.subscriber_id}" alt="{__("expand_collapse_list")}" title="{__("expand_collapse_list")}" class="hand cm-combination-subscribers"><span class="exicon-expand"> </span></span><span name="minus_plus" id="off_subscribers_{$s.subscriber_id}" alt="{__("expand_collapse_list")}" title="{__("expand_collapse_list")}" class="hand hidden cm-combination-subscribers"><span class="exicon-collapse"> </span></span><a href="mailto:{$s.email|escape:url}">{$s.email}</a>
    </td>
    <td>
        <select class="span2" name="subscribers[{$s.subscriber_id}][lang_code]">
        {foreach from=$languages item=lng}
        <option value="{$lng.lang_code}" {if $s.lang_code == $lng.lang_code}selected="selected"{/if} >{$lng.name}</option>
        {/foreach}
        </select>
    </td>
    <td>
        {$s.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"},&nbsp;{assign var="count" value=$s.mailing_lists|@count}{__("subscribed_to", ["[num]" => $count])}
    </td>
    <td class="center nowrap">
        &nbsp;
    </td>
    <td class="nowrap right">
        {capture name="tools_list"}
            <li>{btn type="list" class="cm-confirm" text=__("delete") href="subscribers.delete?subscriber_id=`$s.subscriber_id`"}</li>
        {/capture}
        <div class="hidden-tools">
            {dropdown content=$smarty.capture.tools_list}
        </div>
    </td>
</tr>
<tr id="subscribers_{$s.subscriber_id}" class="hidden no-hover row-more">
    <td>&nbsp;</td>
    <td colspan="5" class="row-more-body row-gray">
        {if $mailing_lists}
        <table class="table table-condensed">
        <thead>
        <tr>
            <th>{__("mailing_list")}</th>
            <th class="center">{__("subscribed")}</th>
            <th class="center">{__("confirmed")}</th>
        </tr>
        </thead>
        {foreach from=$mailing_lists item="list" key="list_id"}
            <tr>
                <td>{$list.object}</td>
                <td class="center">
                    <input type="checkbox" name="subscribers[{$s.subscriber_id}][list_ids][]" value="{$list_id}" {if $s.mailing_lists[$list_id]}checked="checked"{/if} class="checkbox cm-item-{$id}"></td>
                <td class="center">
                    <input type="hidden" name="subscribers[{$s.subscriber_id}][mailing_lists][{$list_id}][confirmed]" value="{if $list.register_autoresponder}0{else}1{/if}" />
                    <input type="checkbox" name="subscribers[{$s.subscriber_id}][mailing_lists][{$list_id}][confirmed]" value="1" {if $s.mailing_lists[$list_id].confirmed || !$list.register_autoresponder}checked="checked"{/if} class="checkbox" {if !$list.register_autoresponder}disabled="disabled"{/if} />
                </td>
            </tr>
        {/foreach}
        </table>
        {else}
            <p class="no-items">{__("no_data")}</p>
        {/if}
    </td>
</tr>
</tbody>
{/foreach}
</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}

{include file="common/pagination.tpl"}
</form>

{capture name="add_new_picker"}

    <form action="{""|fn_url}" method="post" name="subscribers_form_0" class="form-horizontal form-edit ">
    <input type="hidden" name="subscriber_id" value="0" />
    <input type="hidden" name="subscriber_data[list_ids][]" value="{$smarty.request.list_id}" />
    <div class="tabs cm-j-tabs">
        <ul class="nav nav-tabs">
            <li id="tab_mailing_list_details_0" class="cm-js active"><a>{__("general")}</a></li>
        </ul>
    </div>

    <div class="cm-tabs-content" id="content_tab_mailing_list_details_0">
    <fieldset>
        <div class="control-group">
            <label for="subscribers_email_0" class="control-label cm-required cm-email">{__("email")}</label>
            <div class="controls">
            <input type="text" name="subscriber_data[email]" id="subscribers_email_0" value="" class="span6" />
            </div>
        </div>

        <div class="control-group">
            <label for="elm_lang_0" class="cm-required control-label">{__("language")}</label>
            <div class="controls">
            <select id="elm_lang_0" name="subscriber_data[lang_code]">
                {foreach from=$languages item="lng"}
                    <option value="{$lng.lang_code}">{$lng.name}</option>
                {/foreach}
            </select>
            </div>
        </div>

    </fieldset>
    </div>

    <div class="buttons-container">
        {include file="buttons/save_cancel.tpl" but_name="dispatch[subscribers.update]" cancel_action="close"}
    </div>

    </form>
{/capture}

{/capture}

{capture name="adv_buttons"}
    {capture name="tools_list"}
        <li>{include file="common/popupbox.tpl" id="add_new_subscribers" text=__("new_subscribers") content=$smarty.capture.add_new_picker link_text=__("add_subscriber") act="link"}</li>
        <li>{include file="pickers/users/picker.tpl" data_id="subscr_user" picker_for="subscribers" extra_var="subscribers.add_users?list_id=`$smarty.request.list_id`" but_text=__("ne_add_subscribers_from_users") view_mode="button" no_container=true}</li>
    {/capture}
    {dropdown content=$smarty.capture.tools_list icon="icon-plus" no_caret=true placement="right"}
{/capture}

{capture name="buttons"}
    {if $subscribers}
        {capture name="tools_list"}
            <li>{btn type="delete_selected" dispatch="dispatch[subscribers.m_delete]" form="subscribers_form"}</li>
        {/capture}
        {dropdown content=$smarty.capture.tools_list}

        {include file="buttons/save.tpl" but_name="dispatch[subscribers.m_update]" but_role="submit-link" but_target_form="subscribers_form" }
    {/if}
{/capture}

{capture name="sidebar"}
    {include file="addons/news_and_emails/views/subscribers/components/subscribers_search_form.tpl" dispatch="subscribers.manage"}
{/capture}

{include file="common/mainbox.tpl" title=__("subscribers") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons sidebar=$smarty.capture.sidebar select_languages=true}
