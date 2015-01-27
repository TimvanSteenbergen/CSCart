{capture name="mainbox"}

    {capture name="tabsbox"}

        <div id="content_languages">

            {capture name="add_language"}
                {include file="views/languages/update.tpl" lang_data=[]}
            {/capture}

            {* FIXME: HARDCODE checking permissions. We need to divide these two forms by different modes *}
            <form action="{""|fn_url}" method="post" name="languages_form" class="{if $runtime.company_id}cm-hide-inputs{/if}">
                <input type="hidden" name="page" value="{$smarty.request.page}" />
                <input type="hidden" name="selected_section" value="{$smarty.request.selected_section}" />

                <table class="table table-middle">
                <thead>
                    <tr class="cm-first-sibling">
                        <th width="3" class="left">
                            {include file="common/check_items.tpl"}</th>
                        <th>{__("language_code")}</th>
                        <th>{__("name")}</th>
                        <th>{__("country")}</th>
                        <th>&nbsp;</th>
                        <th class="right">{__("status")}</th>
                    </tr>
                </thead>
                {if $langs|count == 1}
                    {assign var="disable_change" value=true}
                {/if}
                <tbody>
                {foreach from=$langs item="language"}
                <tr class="cm-row-status-{$language.status|lower}">
                    <td class="left">
                        <input type="checkbox" name="lang_ids[]" value="{$language.lang_id}" {if $language.lang_code == $smarty.const.DEFAULT_LANGUAGE}disabled="disabled"{/if} class="checkbox cm-item"></td>
                    <td>
                        <input type="hidden" name="update_language[{$language.lang_id}][lang_code]" value="{$language.lang_code}">
                        {btn type="dialog" text=$language.lang_code href="languages.update?lang_id=`$language.lang_id`" target_id="content_group`$language.lang_id`" prefix=$language.lang_id}
                    </td>
                    <td>
                        {$language.name}
                    </td>
                    <td>
                        <i class="flag flag-{$language.country_code|strtolower}"></i>{$countries[$language.country_code]}
                    </td>
                    <td class="nowrap right">
                        <div class="hidden-tools">
                            {capture name="tools_list"}
                                <li>{btn type="dialog" text=__("edit") href="languages.update?lang_id=`$language.lang_id`" id="opener_group_`$language.lang_id`" target_id="content_group`$language.lang_id`" prefix=$language.lang_id}</li>

                                {if $language.lang_code != $smarty.const.DEFAULT_LANGUAGE}
                                    <li>{btn type="list" class="cm-confirm" text=__("delete") href="languages.delete_language?lang_id=`$language.lang_id`"}</li>
                                {/if}

                                <li>{btn type="list" class="cm-language-name" text=__("clone") href="languages.clone_language?lang_id=`$language.lang_id`"}</li>
                                <li>{btn type="list" text=__("export") href="languages.export_language?lang_id=`$language.lang_id`"}</li>
                                {if !$runtime.company_id}
                                    <li>{btn type="dialog" text=__("update_translation") title=__("update_translation") href="languages.update_translation?lang_id=`$language.lang_id`" id="opener_group_`$language.lang_id`_variables" target_id="content_group`$language.lang_id`_variables" prefix="`$language.lang_id`_variables"}</li>
                                {/if}
                            {/capture}
                            {dropdown content=$smarty.capture.tools_list}
                        </div>

                    </td>
                    {capture name="popups"}
                        {$smarty.capture.popups nofilter}
                        <div id="content_group{$language.lang_id}" class="hidden" title="{"{__("editing_language")}: `$language.name`"}"></div>
                    {/capture}

                    <td class="right">
                        {assign var="lang_id" value=$language.lang_id}
                        {include file="common/select_popup.tpl" id=$lang_id prefix="lng" status=$language.status hidden="Y" object_id_name="lang_id" table="languages" update_controller="languages" st_result_ids="content_languages" non_editable=$runtime.company_id}
                    </td>

                </tr>
                {/foreach}
                </tbody>
                </table>

            </form>

            {capture name="delete_button"}
                <li class="cm-tab-tools" id="tools_languages_delete_buttons">
                    {btn type="delete_selected" dispatch="dispatch[languages.m_delete]" form="languages_form"}
                </li>
            {/capture}

        <!--content_languages--></div>

        {if !$runtime.company_id}
            <div class="hidden" id="content_available_languages"></div>
        {/if}

        {$smarty.capture.popups nofilter}

    {/capture}

    {include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox group_name=$runtime.controller active_tab=$smarty.request.selected_section track=true}
{/capture}

{capture name="adv_buttons"}
    {hook name="languages:adv_buttons"}
    {if !$runtime.company_id}
        {include file="common/popupbox.tpl" id="add_language" text=__("new_language") title=__("add_language") content=$smarty.capture.add_language act="general" icon="icon-plus"}
    {/if}
    {/hook}
{/capture}

{capture name="buttons"}
    {capture name="tools_list"}
        {hook name="languages:manage_tools_list"}
            <li>{btn type="list" text=__("on_site_live_editing") href="customization.update_mode?type=live_editor&status=enable"|fn_url target="_blank"}</li>
            <li class="divider"></li>
            {$smarty.capture.delete_button nofilter}
        {/hook}
    {/capture}
    {dropdown content=$smarty.capture.tools_list}

    {$smarty.capture.add_button nofilter}
{/capture}

{include file="common/mainbox.tpl" title=__("languages") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons sidebar=$smarty.capture.sidebar}

<script type="text/javascript">
    (function($, _){
        $(document).ready(function(){
            $('.cm-language-name').click(function(){
                var lang_code = prompt(_.tr('enter_new_lang_code'));

                if (lang_code == null || lang_code.length == 0) {
                    // Customer hit Cancel button or did not enter lang_code
                    return false;
                }

                var href = $.attachToUrl($(this).attr('href'), 'lang_code=' + lang_code);
                $(this).attr('href', href);
            });
        });
    }(Tygh.$, Tygh));
</script>
