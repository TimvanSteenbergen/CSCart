
{if $grid}
    {assign var="id" value=$grid.grid_id}
{else}
    {assign var="id" value=0}
{/if}

<div id="grid_properties_{$id}">
<form action="{""|fn_url}" method="post" class="form-horizontal form-edit " name="grid_update_form">
<input type="hidden" name="grid_id" value="{$id}" />

<input type="hidden" name="container_id" value="{$params.container_id}" />
<input type="hidden" name="parent_id" value="{$params.parent_id|default:$grid.parent_id|default:0}" />

<div class="tabs cm-j-tabs">
    <ul class="nav nav-tabs">
        <li class="cm-js active"><a>{__("general")}</a></li>
    </ul>
</div>

<div class="cm-tabs-content">
    <div class="control-group cm-no-hide-input">
        <label class="control-label" for="elm_grid_width_{$id}">{__("width")}</label>
        <div class="controls">
        <select id="elm_grid_width_{$id}" name="width">
            {section name="width" start=$params.min_width|default:1-1|default:0 loop=$params.max_width|default:24}
                {assign var="index" value=$smarty.section.width.index+1}
                <option value="{$index}" {if $index == $grid.width}selected="selected"{/if}>{$index}</option>
            {/section}
        </select>
        </div>
    </div>

    <div class="control-group cm-no-hide-input">
        <label class="control-label" for="elm_grid_content_align_{$id}">{__("content_alignment")}</label>
        <div class="controls">
        <select id="elm_grid_content_align_{$id}" name="content_align">
            <option value="FULL_WIDTH" {if $grid.content_align == "FULL_WIDTH"}selected="selected"{/if}>{__("full_width")}</option>            
            <option value="LEFT" {if $grid.content_align == "LEFT"}selected="selected"{/if}>{__("left")}</option>
            <option value="RIGHT" {if $grid.content_align == "RIGHT"}selected="selected"{/if}>{__("right")}</option>
        </select>
        </div>
    </div>

    <div class="control-group cm-no-hide-input">
        <label class="control-label" for="elm_grid_offset_{$id}">{__("offset")}</label>
        <div class="controls">
        <select id="elm_grid_offset_{$id}" name="offset">
            {section name="offset" start=0 loop=$params.max_width|default:24}
                {assign var="index" value=$smarty.section.offset.index}
                <option value="{$index}" {if $index == $grid.offset}selected="selected"{/if}>{$index}</option>
            {/section}
        </select>
        </div>
    </div>

    <div class="control-group cm-no-hide-input">
        <label class="control-label" for="elm_grid_user_class_{$id}">{__("user_class")}</label>
        <div class="controls">
        <input id="elm_grid_user_class_{$id}" name="user_class" value="{$grid.user_class}" type="text" />
        </div>
    </div>

</div>

<div class="buttons-container">
    {include file="buttons/save_cancel.tpl" but_name="dispatch[block_manager.update_location]" cancel_action="close" but_meta="cm-dialog-closer" save=$id}
</div>
</form>
<!--grid_properties_{$id}--></div>
