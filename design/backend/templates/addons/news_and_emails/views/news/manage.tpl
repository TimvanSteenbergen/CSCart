{** news section **}

{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="news_form" class="cm-hide-inputs">
<input type="hidden" name="fake" value="1" />

{include file="common/pagination.tpl" save_current_page=true div_id=$smarty.request.content_id}

{if $news}
<table width="100%" class="table table-middle">
<thead>
<tr>    
    <th width="1%">       
        {include file="common/check_items.tpl" class="cm-no-hide-input"}
    </th>
    <th width="40%" class="shift-left">{__("news")}</th>
    <th width="10%">{__("date")}</th>
    <th width="5%">&nbsp;</th>
    <th class="right" width="10%">{__("status")}</th>
</tr>
</thead>
{foreach from=$news item=n}
<tbody>
<tr class="cm-row-status-{$n.status|lower}" valign="top" >
    {assign var="allow_save" value=$n|fn_allow_save_object:"news"}
    {if $allow_save}
        {assign var="no_hide_input" value="cm-no-hide-input"}
        {assign var="display" value=""}
    {else}
        {assign var="no_hide_input" value=""}
        {assign var="display" value="text"}
    {/if}
    <td class="left {$no_hide_input}">
        <input type="checkbox" name="news_ids[]" value="{$n.news_id}" class="cm-item" /></td>
    <td class="{$no_hide_input}">
        <a class="row-status" href="{"news.update?news_id=`$n.news_id`"|fn_url}">{$n.news}</a>
        {include file="views/companies/components/company_name.tpl" object=$n}
    </td>
    <td class="left nowrap {$no_hide_input}">
        <span>{$n.date|date_format:"`$settings.Appearance.date_format`"}</span>
    </td>
    <td class="center nowrap">
        {capture name="tools_list"}
            {if $allow_save}
                <li>{btn type="list" text=__("edit") href="news.update?news_id=`$n.news_id`"}</li>
                <li>{btn type="list" class="cm-confirm" text=__("delete") href="news.delete?news_id=`$n.news_id`"}</li>
            {/if}
        {/capture}
        <div class="hidden-tools right">
            {dropdown content=$smarty.capture.tools_list}
        </div>
    </td>
    <td class="right nowrap">
        {include file="common/select_popup.tpl" id=$n.news_id status=$n.status hidden="" object_id_name="news_id" table="news" popup_additional_class="`$no_hide_input`" display=$display}
    </td>
</tr>
{/foreach}
</tbody>
</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}

{include file="common/pagination.tpl" div_id=$smarty.request.content_id}

{capture name="adv_buttons"}
    {include file="common/tools.tpl" tool_href="news.add" prefix="top" title=__("add_news") hide_tools=true}
{/capture}

{capture name="buttons"}
    {capture name="tools_list"}
        {if $news}
            <li>{btn type="delete_selected" dispatch="dispatch[news.m_delete]" form="news_form"}</li>
        {/if}
    {/capture}
    {dropdown content=$smarty.capture.tools_list}
{/capture}
</form>

{/capture}
{include file="common/mainbox.tpl" title=__("news") content=$smarty.capture.mainbox adv_buttons=$smarty.capture.adv_buttons select_languages=true buttons=$smarty.capture.buttons content_id="manage_news"}
