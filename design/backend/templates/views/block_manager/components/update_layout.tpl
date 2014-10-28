{if $layout_data}
    {$id = $layout_data.layout_id}
{else}
    {$id = 0}
{/if}

<script type="text/javascript">
Tygh.tr({
    'block_manager.error_changing_layout_in_css_mode': '{__("block_manager.error_changing_layout_in_css_mode", ["[url]" => fn_url("customization.update_mode?type=theme_editor&status=enable&s_layout=$id")])|escape:"javascript"}',
});
</script>

<form action="{""|fn_url}" method="post" name="update_layout_form" class="form-horizontal form-edit ">
<input type="hidden" name="layout_id" value="{$id}">

<div class="add-new-object-group">
    <div class="tabs cm-j-tabs">
        <ul class="nav nav-tabs">
            <li id="tab_update_layout_{$id}" class="cm-js active"><a>{__("general")}</a></li>
        </ul>
    </div>

    <div class="cm-tabs-content" id="content_tab_update_layout_{$id}">
    <fieldset>
        <div class="control-group">
            <label class="control-label cm-required" for="elm_layout_name_{$id}">{__("name")}</label>
            <div class="controls">
                <input type="text" id="elm_layout_name_{$id}" name="layout_data[name]" value="{$layout_data.name}" />
            </div>
        </div>

        {if !$id}
        {hook name="block_manager:update_layout_copy"}
        <div class="control-group">
            <label class="control-label cm-required" for="elm_layout_copy_{$id}">{__("copy_from_layout")}</label>
            <div class="controls">
                <select name="layout_data[from_layout_id]" id="elm_layout_copy_{$id}">
                    {foreach from=$default_layouts_sources item="layout_source"}
                        <option value="{$layout_source.theme_name}|{$layout_source.filename}">{__("restore_original")}: {$themes.installed[$layout_source.theme_name].title}: {$layout_source.name}</option>
                    {/foreach}
                    {foreach from=$all_layouts item="layout"}
                        <option value="{$layout.layout_id}">{$themes.installed[$layout.theme_name].title}: {$layout.name}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        {/hook}
        {/if}

        <div class="control-group">
            <label class="control-label" for="elm_layout_is_default_{$id}">{__("default")}</label>
            <div class="controls">
                <input type="checkbox" id="elm_layout_is_default_{$id}" name="layout_data[is_default]" value="1" {if $layout_data.is_default}checked="checked" disabled="disabled"{/if} />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="elm_layout_width_{$id}">{__("block_manager.grid_columns")}</label>
            <div class="controls">
                <select name="layout_data[width]" id="elm_layout_width_{$id}">
                    <option value="12" {if $layout_data.width == "12"}selected="selected"{/if}>12</option>
                    <option value="16" {if $layout_data.width == "16" || !$layout_data.width}selected="selected"{/if}>16</option>
                </select>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="elm_layout_width_{$id}">{__("block_manager.layout_width")}</label>
            <div class="controls">
                <select name="layout_data[layout_width]" id="elm_layout_type_{$id}">
                    <option value="fixed" {if $layout_data.layout_width == "fixed"}selected="selected"{/if}>{__("block_manager.fixed_layout")}</option>
                    <option value="fluid" {if $layout_data.layout_width == "fluid"}selected="selected"{/if} >{__("block_manager.fluid_layout")}</option>
                    <option value="full_width" {if $layout_data.layout_width == "full_width"}selected="selected"{/if}>{__("block_manager.full_width_layout")}</option>
                </select>
            </div>
        </div>

        <div id="fluid_layout_settings_{$id}" {if $layout_data.layout_width != "fluid"}class="hidden"{/if}>
            <div class="control-group">
                <label class="control-label" for="elm_min_width_{$id}">{__("block_manager.min_width")}</label>
                <div class="controls">
                    <input type="text" id="elm_min_width_{$id}" name="layout_data[min_width]" value="{$layout_data.min_width|default:760}" />
                </div>
            </div>

            <div class="control-group">
                <label class="control-label" for="elm_max_width_{$id}">{__("block_manager.max_width")}</label>
                <div class="controls">
                    <input type="text" id="elm_max_width_{$id}" name="layout_data[max_width]" value="{$layout_data.max_width|default:960}" />
                </div>
            </div>
        </div>

    </fieldset>
    </div>
</div>

<script type="text/javascript">
    (function(_, $) {
        var is_theme_converted_to_css = !!parseInt('{$theme_manifest.converted_to_css}'),
            prev_value;

        $("#elm_layout_type_{$id}").one('focus', function() {
            prev_value = this.value;
        }).change(function(){
            if (is_theme_converted_to_css) {
                $.ceNotification('show', {
                    type: 'E',
                    title: _.tr('error'),
                    message: _.tr('block_manager.error_changing_layout_in_css_mode'),
                });
                this.value = prev_value;
            } else {
                if(this.value == "fluid") {
                    $("#fluid_layout_settings_{$id}").removeClass('hidden');
                } else {
                    $("#fluid_layout_settings_{$id}").addClass('hidden');
                }
            }
        });
    }(Tygh, Tygh.$));
</script>

<div class="buttons-container">
    {if $id && !$layout_data.is_default}
        <a href="{"block_manager.delete_layout?layout_id=`$layout_data.layout_id`"|fn_url}" class="cm-confirm pull-left btn cm-tooltip" title="{__("delete")}"><i class="icon-trash"></i></a>
    {/if}
    {include file="buttons/save_cancel.tpl" but_name="dispatch[block_manager.update_layout]" cancel_action="close" save=$id}
</div>

</form>
