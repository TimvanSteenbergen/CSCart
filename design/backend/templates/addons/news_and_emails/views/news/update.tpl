{if $news_data}
    {assign var="id" value=$news_data.news_id}
{else}
    {assign var="id" value=0}
{/if}

{assign var="allow_save" value=$news_data|fn_allow_save_object:"news"}
{$show_save_btn = $allow_save scope = root}

{capture name="mainbox"}

{capture name="tabsbox"}

<form action="{""|fn_url}" method="post" name="news_update_form" class="form-horizontal form-edit {if !$allow_save} cm-hide-inputs{/if}">
<input type="hidden" class="cm-no-hide-input" name="fake" value="1" />
<input type="hidden" class="cm-no-hide-input" name="news_id" value="{$id}" />
<input type="hidden" class="cm-no-hide-input" name="selected_section" value="{$smarty.request.selected_section|default:"detailed"}" />

<div id="content_detailed">
<fieldset>
    <div class="control-group">
        <label for="elm_news_name" class="control-label cm-required">{__("name")}</label>
        <div class="controls">
            <input type="text" name="news_data[news]" id="elm_news_name" value="{$news_data.news}" size="40" class="input-large" />
        </div>
    </div>

    {if "MULTIVENDOR"|fn_allowed_for}
        {assign var="zero_company_id_name_lang_var" value="none"}
    {/if}
    {include file="views/companies/components/company_field.tpl"
        name="news_data[company_id]"
        id="elm_news_company_id"
        selected=$news_data.company_id
        disable_company_picker=!$allow_save
    }

    <div class="control-group">
        <label class="control-label" for="elm_news_description">{__("description")}</label>
        <div class="controls">
            <textarea id="elm_news_description" name="news_data[description]" cols="35" rows="8" class="cm-wysiwyg input-large">{$news_data.description}</textarea>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="elm_news_date">{__("date")}</label>
        <div class="controls">
        {include file="common/calendar.tpl" date_id="elm_news_date" date_name="news_data[date]" date_val=$news_data.date start_year=$settings.Company.company_start_year}
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="elm_news_separate">{__("show_on_separate_page")}</label>
        <div class="controls">
        <input type="hidden" name="news_data[separate]" value="N" />
        <input type="checkbox" name="news_data[separate]" id="elm_news_separate" value="Y" {if $news_data.separate == "Y"}checked="checked"{/if} />
        </div>
    </div>

    {include file="views/localizations/components/select.tpl" data_from=$news_data.localization data_name="news_data[localization]"}

    {hook name="news_and_emails:detailed_content"}
    {/hook}

    {include file="common/select_status.tpl" input_name="news_data[status]" id="elm_news_status" obj_id=$news_data.news_id obj=$news_data}
</fieldset>
</div>

<div id="content_addons">

{hook name="news:detailed_content"}
{/hook}
</div>

{hook name="news_and_emails:tabs_content"}
{/hook}

{capture name="buttons"}
{if !$id}
    {include file="buttons/save_cancel.tpl" but_name="dispatch[news.update]" but_role="submit-link" but_target_form="news_update_form"}
{else}
    {if !$show_save_btn}
        {assign var="hide_first_button" value=true}
        {assign var="hide_second_button" value=true}
    {/if}
    {include file="buttons/save_cancel.tpl" but_name="dispatch[news.update]" hide_first_button=$hide_first_button hide_second_button=$hide_second_button but_role="submit-link" but_target_form="news_update_form" save=$id}
{/if}
{/capture}

</form>

{if $id}
{hook name="news_and_emails:tabs_extra"}
{/hook}
{/if}

{/capture}
{include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox track=true}

{/capture}

{if $id}
    {assign var="title" value="{__("editing_news")}: `$news_data.news`"}
{else}
    {assign var="title" value=__("new_news")}
{/if}

{include file="common/mainbox.tpl" title=$title content=$smarty.capture.mainbox select_languages=true buttons=$smarty.capture.buttons}
