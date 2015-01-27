{script src="js/tygh/tabs.js"}

{capture name="mainbox"}
<form action="{""|fn_url}" method="post" name="static_data_tree_form">
<div class="{if !""|fn_allow_save_object:"static_data":$section_data.skip_edition_checking} cm-hide-inputs{/if}" id="static_data_list">
{if $section_data.multi_level == true}
    <input name="section" type="hidden" value="{$section}" />
    {if $section_data.owner_object}
        {assign var="request_key" value=$section_data.owner_object.key}
        {assign var="owner_condition" value="$request_key=`$smarty.request.$request_key`"}
        {assign var="request_value" value=$smarty.request.$request_key}

        <input type="hidden" name="{$request_key}" value="{$request_value}" />
    {else}
        {assign var="owner_condition" value=""}
    {/if}

        <div class="items-container multi-level">
            {if $static_data}
            <table class="table table-middle table-tree hidden-inputs">
                {include file="views/static_data/components/multi_list.tpl" items=$static_data header=true}
            </table>
            {else}
                <p class="no-items">{__("no_data")}</p>
            {/if}
        </div>
{else}
    {include file="views/static_data/components/single_list.tpl"}
{/if}
<!--static_data_list--></div>

{capture name="buttons"}
    {capture name="tools_list"}
        {hook name="static_data:manage_tools_list"}
            {if $static_data}
                <li>{btn type="delete_selected" dispatch="dispatch[static_data.m_delete]" form="static_data_tree_form"}</li>
            {/if}
        {/hook}
    {/capture}
    {dropdown content=$smarty.capture.tools_list}

    {if $section_data.multi_level == true && $static_data}
        {include file="buttons/save.tpl" but_name="dispatch[static_data.m_update]" but_role="submit-link" but_target_form="static_data_tree_form"}
    {/if}
{/capture}

{capture name="adv_buttons"}
    {if ""|fn_allow_save_object:"static_data":$section_data.skip_edition_checking}
        {capture name="add_new_picker"}
            {include file="views/static_data/update.tpl" static_data=[]}
        {/capture}
        {include file="common/popupbox.tpl" id="add_new_section" text=__($section_data.add_title) content=$smarty.capture.add_new_picker title=__($section_data.add_button) act="general" icon="icon-plus"}
    {/if}
{/capture}

</form>
{/capture}
{if $owner_object_name}
    {assign var="title" value="{__($section_data.mainbox_title)}: `$owner_object_name`"}
{else}
    {assign var="title" value=__($section_data.mainbox_title)}
{/if}

{include file="common/mainbox.tpl" title=$title content=$smarty.capture.mainbox tools=$smarty.capture.tools buttons=$smarty.capture.buttons select_languages=true adv_buttons=$smarty.capture.adv_buttons}