{assign var="_addon" value=$smarty.request.addon}
{if $separate}
    {script src="js/tygh/tabs.js"}
    {script src="js/tygh/fileuploader_scripts.js"}
    {include file="views/profiles/components/profiles_scripts.tpl" states=1|fn_get_all_states}
{/if}

{if $separate}{capture name="mainbox"}{/if}
<div id="content_group{$_addon}">
    <div id="content_{$_addon}">
    <div class="tabs cm-j-tabs {if $separate}cm-track{/if} {if $subsections|count == 1}hidden{/if}">
        <ul class="nav nav-tabs">
            {foreach from=$subsections key="section" item="subs"}
                {assign var="tab_id" value="`$_addon`_`$section`"}
                <li class="cm-js {if $smarty.request.selected_section == $tab_id}active{/if}" id="{$tab_id}"><a>{$subs.description}</a></li>
            {/foreach}
        </ul>
    </div>
    <div class="cm-tabs-content" id="tabs_content_{$_addon}">
        <form action="{""|fn_url}" method="post" name="update_addon_{$_addon}_form" class=" form-edit form-horizontal" enctype="multipart/form-data">

        <input type="hidden" name="selected_section" value="{$smarty.request.selected_section}" />
        <input type="hidden" name="addon" value="{$smarty.request.addon}" />
        {if $smarty.request.return_url}
        <input type="hidden" name="redirect_url" value="{$smarty.request.return_url}" />
        {/if}
        
        {foreach from=$options key="section" item="field_item"}
        
        {if $subsections.$section.type == "SEPARATE_TAB"}
            {capture name="separate_section"}
        {/if}

        <div id="content_{$_addon}_{$section}" class="settings{if $subsections.$section.type == "SEPARATE_TAB"} cm-hide-save-button{/if}">
            {capture name="header_first"}false{/capture}

            {foreach from=$field_item key="name" item="data" name="fe_addons"}

                {if $data.parent_id && $field_item[$data.parent_id]}
                    {$parent_item = $field_item[$data.parent_id]}
                    {$parent_item_html_id = "addon_option_`$_addon`_`$parent_item.name`"}
                {else}
                    {$parent_item = []}
                    {$parent_item_html_id = ""}
                {/if}

                {include file="common/settings_fields.tpl" item=$data section=$_addon html_id="addon_option_`$_addon`_`$data.name`" html_name="addon_data[options][`$data.object_id`]" index=$smarty.foreach.fe_addons.iteration total=$smarty.foreach.fe_addons.total class="setting-wide" parent_item=$parent_item parent_item_html_id=$parent_item_html_id}
            {/foreach}
        </div>
        
        {if $subsections.$section.type == "SEPARATE_TAB"}
            {/capture}
            {assign var="sep_sections" value="`$sep_sections` `$smarty.capture.separate_section`"}
        {/if}
        {/foreach}
        
        <div class="buttons-container{if $separate} buttons-bg{/if} cm-toggle-button">
            {if $separate}
                {capture name="buttons"}
                    {include file="buttons/save_cancel.tpl" but_name="dispatch[addons.update]" but_target_form="update_addon_`$_addon`_form" hide_second_button=true breadcrumbs=$breadcrumbs save=true}
                {/capture}
            {else}
                {include file="buttons/save_cancel.tpl" but_name="dispatch[addons.update]" cancel_action="close" save=true}
            {/if}
        </div>
        </form> 
        {if $subsections.$section.type == "SEPARATE_TAB"}
            {$sep_sections nofilter}
        {/if}
    </div>
    <!--content_{$_addon}--></div>
<!--content_group{$_addon}--></div>
{if $separate}
    {/capture}
    {include file="common/mainbox.tpl" title=$addon_name content=$smarty.capture.mainbox buttons=$smarty.capture.buttons}
{/if}
