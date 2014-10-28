{script src="js/tygh/fileuploader_scripts.js"}

{include file="views/profiles/components/profiles_scripts.tpl" states=1|fn_get_all_states}

{if $smarty.request.highlight}
{assign var="highlight" value=","|explode:$smarty.request.highlight}
{/if}


{capture name="mainbox"}

    <form action="{""|fn_url}" method="post" name="settings_form" class=" form-horizontal form-edit form-setting">
    <input name="section_id" type="hidden" value="{$section_id}" />
    <input type="hidden" id="selected_section" name="selected_section" value="{$selected_section}" />

    {capture name="tabsbox"}
        {foreach from=$options item=subsection key="ukey"}
            <div id="content_{$ukey}" {if $subsections.$section.type == "SEPARATE_TAB"}class="cm-hide-save-button"{/if}>
                {foreach from=$subsection item=item name="section"}
                    {include file="common/settings_fields.tpl" item=$item section=$section_id html_id="field_`$section`_`$item.name`_`$item.object_id`" html_name="update[`$item.object_id`]" index=$smarty.foreach.section.iteration total=$smarty.foreach.section.total}
                {/foreach}
            </div>
        {/foreach}

        {capture name="buttons"}
            {include file="buttons/save.tpl" but_name="dispatch[settings.update]" but_role="submit-link" but_target_form="settings_form"}
        {/capture}

    {/capture}
    {include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox track=true}

    </form>
{/capture}

{include file="common/mainbox.tpl" title="{__("settings")}: `$settings_title`" buttons=$smarty.capture.buttons content=$smarty.capture.mainbox sidebar_position="left"}

