{if $section.section_id}
    {assign var="id" value=$section.section_id}
{else}
    {assign var="id" value=0}
{/if}

<div id="content_group{$id}">

    <form action="{""|fn_url}" method="post" name="links_form" class="form-horizontal form-edit">
    <input type="hidden" name="section_id" value="{$id}" />

    <div class="tabs cm-j-tabs">
        <ul class="nav nav-tabs">
            <li id="tab_general_{$id}" class="cm-js active"><a>{__("general")}</a></li>
            <li id="tab_links_{$id}" class="cm-js"><a>{__("section_links")}</a></li>
        </ul>
    </div>

    <div class="cm-tabs-content" id="tabs_content_{$id}">
    <div class="hidden" id="content_tab_general_{$id}">
            <div class="control-group">
                <label for="section_name_{$id}" class="control-label cm-required">{__("name")}:</label>
                <div class="controls">
                    <input type="text" name="section" size="30" value="{$section.section}" id="section_name_{$id}">
                </div>
            </div>
            {include file="common/select_status.tpl" input_name="status" id="section_status" obj=$section}
    </div>

    <div id="content_tab_links_{$id}">
    <table class="table table-middle hidden-inputs">
    <thead>
        <tr>
            <th width="4%">{__("position_short")}</th>
            <th width="40%">{__("name")}</th>
            <th width="30%">{__("url")}</th>
            <th width="10%">&nbsp;</th>
        </tr>
    </thead>
    <tbody>
    {foreach from=$links item=link key=keys name="fe_v"}
    <tr>
        <td>
            <input type="text" name="link_data[{$link.link_id}][position]" size="2" value="{$link.position}" class="input-micro input-hidden"></td>
        <td>
            <input type="hidden" name="link_data[{$link.link_id}][link_id]" value="{$link.link_id}" />
            <input type="text" name="link_data[{$link.link_id}][link]" size="25" value="{$link.link}" class="input-xlarge input-hidden"></td>
        <td>
            <input type="text" name="link_data[{$link.link_id}][link_href]" size="35" value="{$link.link_href}" class="input-xlarge input-hidden"></td>
        <td class="right">
            <div class="hidden-tools">
                {include file="buttons/multiple_buttons.tpl" only_delete="Y"}
                </a>
            </div>
        </td>
    </tr>
    {/foreach}
    <tr id="box_add_link_{$id}">
        <td>
            <input type="text" name="add_link_data[0][position]" size="2" value="" class="input-micro"></td>
        <td>
            <input type="hidden" name="add_link_data[0][link_id]" value="0" />
            <input type="text" name="add_link_data[0][link]" size="25" value="" class="ïnput-xlarge"></td>
        <td>
            <input type="text" name="add_link_data[0][link_href]" size="35" value="" class="ïnput-xlarge"></td>
        <td>
            {include file="buttons/multiple_buttons.tpl" item_id="add_link_`$id`"}
        </td>
    </tr>
    </tbody>
    </table>
    </div>
    </div>

    <div class="buttons-container">
        {include file="buttons/save_cancel.tpl" but_name="dispatch[sitemap.update_sitemap]" cancel_action="close" save=$id}
    </div>

    </form>
<!--content_group{$id}--></div>
