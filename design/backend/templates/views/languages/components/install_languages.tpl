<div class="hidden" id="content_available_languages">
    {if $langs_meta|count > 0}
        {* FIXME: HARDCODE checking permissions. We need to divide these two forms by different modes *}
        <form action="{""|fn_url}" method="post" name="languages_install_form" class="{if $runtime.company_id}cm-hide-inputs{/if}">
            <input type="hidden" name="page" value="{$smarty.request.page}" />
            <input type="hidden" name="selected_section" value="{$smarty.request.selected_section}" />

            <table class="table table-middle">
            <thead>
                <tr class="cm-first-sibling">
                    <th>{__("language_code")}</th>
                    <th>{__("name")}</th>
                    <th>{__("country")}</th>
                    <th class="right">&nbsp;</th>
                </tr>
            </thead>
            
            <tbody>
            {foreach from=$langs_meta item="language"}
                <tr class="cm-row-status-{$language.status|lower}">
                    <td>
                        {$language.lang_code}
                    </td>
                    <td>
                        {$language.name}
                    </td>
                    <td>
                        <i class="flag flag-{$language.country_code|strtolower}"></i>{$countries[$language.country_code]}
                    </td>
                    <td class="right">
                        <a class="btn lowercase" href="{"languages.install?pack=`$language.lang_code`"|fn_url}">{__("install")}</a>
                    </td>

                </tr>
            {/foreach}
            </tbody>
            </table>

        </form>
    {else}
        <p class="no-items">{__("no_items")}</p>
    {/if}
<!--content_available_languages--></div>