{if $newsletter_type ==  $smarty.const.NEWSLETTER_TYPE_NEWSLETTER}
    {assign var="object_names" value=__("newsletters")}
{elseif $newsletter_type ==  $smarty.const.NEWSLETTER_TYPE_TEMPLATE}
    {assign var="object_names" value=__("newsletter_templates")}
{elseif $newsletter_type ==  $smarty.const.NEWSLETTER_TYPE_AUTORESPONDER}
    {assign var="object_names" value=__("newsletter_autoresponders")}
{/if}

{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="newsletters_form" enctype="multipart/form-data">
<input type="hidden" name="fake" value="1" />
<input type="hidden" name="newsletter_type" value="{$newsletter_type}" />

{include file="common/pagination.tpl" save_current_page=true save_current_url=true}

{if $newsletters}
<table width="100%" class="table table-middle">
<thead>
<tr>
    <th width="1%">
        {include file="common/check_items.tpl"}</th>
    <th width="40%">{__("subject")}</th>
    {if $newsletter_type == $smarty.const.NEWSLETTER_TYPE_NEWSLETTER}
    <th>{__("mailing_lists")}</th>
    <th>{__("date")}</th>
    {/if}
    <th>&nbsp;</th>
    <th class="right">{__("status")}</th>
</tr>
</thead>
{foreach from=$newsletters item=newsletter}
<tbody>
<tr class="cm-row-status-{$newsletter.status|lower}">
    <td class="left">
        <input type="checkbox" name="newsletter_ids[]" value="{$newsletter.newsletter_id}" class="cm-item" /></td>
    <td>
        <a class="row-status" href="{"newsletters.update?newsletter_id=`$newsletter.newsletter_id`"|fn_url}">{$newsletter.newsletter}</a>
    </td>
    {if $newsletter_type == $smarty.const.NEWSLETTER_TYPE_NEWSLETTER}
        <td>
            {$newsletter.mailing_list_names|default:" - "}
        </td>
        <td class="nowrap">
            {if $newsletter.sent_date}
                {$newsletter.sent_date|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}
            {else}
            &nbsp;-&nbsp;
            {/if}
        </td>
    {/if}

    <td class="nowrap right">
        {capture name="tools_list"}
            <li>{btn type="list" text=__("edit") href="newsletters.update?newsletter_id=`$newsletter.newsletter_id`"}</li>
            <li>{btn type="list" class="cm-confirm" text=__("delete") href="newsletters.delete?newsletter_id=`$newsletter.newsletter_id`"}</li>
        {/capture}
        <div class="hidden-tools">
            {dropdown content=$smarty.capture.tools_list}
        </div>
    </td>
    <td class="right" width="10%">
        {include file="common/select_popup.tpl" id=$newsletter.newsletter_id status=$newsletter.status items_status="news"|fn_get_predefined_statuses object_id_name="newsletter_id" table="newsletters" popup_additional_class="dropleft"}
    </td>
</tr>
{/foreach}
</tbody>
</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}

{include file="common/pagination.tpl"}
</form>
{/capture}

{capture name="buttons"}
    {capture name="tools_list"}
        {if $newsletters}
            <li>{btn type="delete_selected" dispatch="dispatch[newsletters.m_delete]" form="newsletters_form"}</li>
        {/if}
    {/capture}
    {dropdown content=$smarty.capture.tools_list}
{/capture}

{capture name="adv_buttons"}
    {if $newsletter_type ==  $smarty.const.NEWSLETTER_TYPE_NEWSLETTER}
        {include file="common/tools.tpl" tool_href="newsletters.add?type=`$smarty.const.NEWSLETTER_TYPE_NEWSLETTER`" prefix="top" hide_tools="true" title=__("add_newsletter")}
    {elseif $newsletter_type ==  $smarty.const.NEWSLETTER_TYPE_TEMPLATE}
        {include file="common/tools.tpl" tool_href="newsletters.add?type=`$smarty.const.NEWSLETTER_TYPE_TEMPLATE`" prefix="top" hide_tools="true" title=__("add_template")}
    {elseif $newsletter_type ==  $smarty.const.NEWSLETTER_TYPE_AUTORESPONDER}
        {include file="common/tools.tpl" tool_href="newsletters.add?type=`$smarty.const.NEWSLETTER_TYPE_AUTORESPONDER`" prefix="top" hide_tools="true" title=__("add_autoresponder")}
    {/if}
{/capture}

{include file="common/mainbox.tpl" title=$object_names content=$smarty.capture.mainbox select_languages=true buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons}