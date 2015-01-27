{script src="js/tygh/tabs.js"}
{script src="js/lib/bootstrap_switch/js/bootstrapSwitch.js"}
{style src="lib/bootstrap_switch/stylesheets/bootstrapSwitch.css"}

{capture name="sidebar"}

    {include file="common/settings_sidebar.tpl" settings=$em_settings}

    {include file="addons/email_marketing/views/em_subscribers/components/subscribers_search_form.tpl" dispatch="em_subscribers.manage"}
{/capture}

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
    <th>{__("name")}</th>
    <th>{__("registered")}</th>
    <th>{__("status")}</th>
    <th>&nbsp;</th>
</tr>
</thead>
{foreach from=$subscribers item="s"}
<tbody>
<tr>
    <td>
        <input type="checkbox" name="subscriber_ids[]" value="{$s.subscriber_id}" class="cm-item" /></td>
    <td>
        <input type="hidden" name="subscribers[{$s.subscriber_id}][email]" value="{$s.email}" />
        <a href="mailto:{$s.email|escape:url}">{$s.email}</a>
    </td>
    <td>
        {$s.name|default:"-"}
    </td>
    <td>
        {$s.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}
    </td>
    <td class="center nowrap">
        {if $s.status == "A"}{__("active")}{else}{__("pending")}{/if}
    </td>
    <td class="nowrap right">
        {capture name="tools_list"}
            <li>{btn type="list" class="cm-confirm" text=__("delete") href="em_subscribers.delete?subscriber_id=`$s.subscriber_id`"}</li>
        {/capture}
        <div class="hidden-tools">
            {dropdown content=$smarty.capture.tools_list}
        </div>
    </td>
</tr>
</tbody>
{/foreach}
</table>
{else}
    <div class="no-items">
        {__("no_data")}
        {if $em_support.import}
        <p>
        {btn type="text_add" text=__("import") href="em_subscribers.import"|fn_url}
        </p>
        {/if}
    </div>
{/if}

{include file="common/pagination.tpl"}
</form>

{capture name="add_new_picker"}

    <form action="{""|fn_url}" method="post" name="subscribers_form_0" class="form-horizontal form-edit ">
    <input type="hidden" name="subscriber_id" value="0" />
    <div class="tabs cm-j-tabs">
        <ul class="nav nav-tabs">
            <li id="tab_mailing_list_details_0" class="cm-js active"><a>{__("general")}</a></li>
        </ul>
    </div>

    <div class="cm-tabs-content" id="content_tab_mailing_list_details_0">
    <fieldset>
        <div class="control-group">
            <label for="elm_subscribers_email" class="control-label cm-required cm-email">{__("email")}</label>
            <div class="controls">
            <input type="text" name="subscriber_data[email]" id="elm_subscribers_email" value="" class="span6" />
            </div>
        </div>

        <div class="control-group">
            <label for="elm_subscribers_name" class="control-label">{__("person_name")}</label>
            <div class="controls">
            <input type="text" name="subscriber_data[name]" id="elm_subscribers_name" value="" class="span6" />
            </div>
        </div>

        <div class="control-group">
            <label for="elm_subscribers_status" class="control-label">{__("language")}</label>
            <div class="controls">
                <select name="subscriber_data[lang_code]">
                    {foreach from=""|fn_get_translation_languages item="language"}
                        <option value="{$language.lang_code}" {if $settings.Appearance.frontend_default_language == $language.lang_code}selected="selected"{/if}>{$language.name}</option>
                    {/foreach}
                </select>
            </div>
        </div>

    </fieldset>
    </div>

    <div class="buttons-container">
        {include file="buttons/save_cancel.tpl" but_name="dispatch[em_subscribers.update]" cancel_action="close"}
    </div>

    </form>
{/capture}

{/capture}

{capture name="adv_buttons"}
    {include file="common/popupbox.tpl" id="add_new_subscribers" text=__("email_marketing.new_subscriber") content=$smarty.capture.add_new_picker act="general" icon="icon-plus" title=__("add_subscriber")}
{/capture}

{capture name="buttons"}

        {capture name="tools_list"}
            {if $em_support.manual_sync}
            <li>{btn type="list" text=__("email_marketing.sync") href="em_subscribers.sync"}</li>
            {/if}

            {if $subscribers}
            <li>{btn type="list" text=__("export_selected") dispatch="dispatch[em_subscribers.export_range]" form="subscribers_form"}</li>
            <li>{btn type="list" text=__("email_marketing.export_all") href="exim.delete_range?section=subscribers&pattern_id=em_subscribers"|fn_url}</li>
            <li>{btn type="delete_selected" dispatch="dispatch[em_subscribers.m_delete]" form="subscribers_form"}</li>
            {/if}
        {/capture}
        {dropdown content=$smarty.capture.tools_list}

        {if $subscribers}
            {include file="buttons/save.tpl" but_name="dispatch[em_subscribers.m_update]" but_role="submit-link" but_target_form="subscribers_form"}
        {/if}
{/capture}

{include file="common/mainbox.tpl" title=__("subscribers") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons sidebar=$smarty.capture.sidebar select_languages=false}
