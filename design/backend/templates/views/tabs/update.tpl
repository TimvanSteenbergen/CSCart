{if $tab_data}
    {assign var="id" value=$tab_data.tab_id}
{else}
    {assign var="id" value=0}
{/if}


{script src="js/tygh/tabs.js"}

{assign var=html_id value="tab_`$id`"}

<script type="text/javascript">
var html_id = "{$html_id}";
{literal}
(function(_, $) {
    $(document).ready(function() {
        $(_.doc).on('click', '.cm-remove-block', function(e) {
            if (confirm(_.tr('text_are_you_sure_to_proceed')) != false) {
                var parent = $(this).parent();
                var block_id = parent.find('input[name="block_id"]').val();

                $.ceAjax('request', fn_url('block_manager.block.delete'), {
                    data: {block_id: block_id},
                    callback: function() {
                        parent.remove();
                    },
                    method: 'post'
                });
            }

            return false;
        });

        $(_.doc).on('click', '.cm-add-block', function(e) {
            /*
                Adding new block functionality
            */
            var action = $(this).prop('class').match(/bm-action-([a-zA-Z0-9-_]+)/)[1];
            
            if (action == 'new-block') {                
                var block_type = $(this).find('input[name="block_data[type]"]').val();

                var href = 'block_manager.update_block?';
                    href += 'block_data[type]=' + block_type;
                    href += '&ajax_update=1';
                    href += '&html_id=' + html_id;
                    href += '&force_close=' + 1;
                    href += '&assign_to=' + 'ajax_update_block_' + html_id;

                var prop_container = 'new_block_' + block_type;

                // Remove properties container if it exist
                if ($('#' + prop_container).length != 0) {
                    $('#' + prop_container).remove();
                }

                // Create properties container
                var container = $('<div id="' + prop_container + '"></div>').appendTo(_.body);

                $('#' + prop_container).ceDialog('open', {href: fn_url(href), title: Tygh.tr('add_block') + ': ' + $(this).find('strong').text()});
            } else if (action == 'existing-block') {
                var block_id = $(this).find('input[name="block_id"]').val();
                var block_title = $(this).find('.select-block-title').text();

                data = {
                    block_data: {
                        block_id: $(this).find('input[name="block_id"]').val()
                    },
                    assign_to: 'ajax_update_block_' + html_id,
                    force_close: '1'
                };

                $.ceAjax('request', fn_url('block_manager.update_block'), {
                    data: data,
                    method: 'post'
                });
            }

            $.ceDialog('get_last').ceDialog('close');
        });
    });

}(Tygh, Tygh.$));
{/literal}
</script>

<form action="{""|fn_url}" name="update_product_tab_form_{$id}" method="post" class=" form-horizontal">
<div id="content_group_{$html_id}">
    <input type="hidden" name="tab_data[tab_id]" value="{$id}" />
    <input type="hidden" name="result_ids" value="content_group_tab_{$id}" />

    <div class="tabs cm-j-tabs">
        <ul class="nav nav-tabs">
            <li id="general_{$html_id}" class="cm-js{if $active_tab == "block_general_`$html_id`"} active{/if}">
                <a>{__("general")}</a>
            </li>
            {if $dynamic_object_scheme && $id > 0}
                <li id="tab_status_{$html_id}" class="cm-js{if $active_tab == "block_status_`$html_id`"} active{/if}">
                    <a>{__("status")}</a>
                </li>
            {/if}
        </ul>
    </div>

    <div class="cm-tabs-content" id="tabs_content_{$html_id}">
        <div id="content_general_{$html_id}">
            <fieldset>
                <div class="control-group">
                    <label class="cm-required control-label" for="elm_description_{$html_id}">{__("name")}:</label>
                    <div class="controls">
                        <input type="text" name="tab_data[name]" value="{$tab_data.name}" id="elm_description_{$html_id}" class="input-text" size="18" />
                    </div>
                </div>

                {if !$dynamic_object_scheme}
                    {include file="common/select_status.tpl" input_name="tab_data[status]" id="elm_tab_data_`$html_id`" obj=$tab_data}
                {/if}

                <div class="control-group">
                    <label for="elm_show_in_popup_{$html_id}" class="control-label">{__("show_tab_in_popup")}:</label>
                    <div class="controls">
                        <input type="hidden" name="tab_data[show_in_popup]" value="N" />
                        <input type="checkbox" name="tab_data[show_in_popup]" id="elm_show_in_popup_{$html_id}" {if $tab_data.show_in_popup == "Y"}checked="checked"{/if} value="Y">
                    </div>
                </div>

                {if $tab_data.is_primary !== 'Y' && "block_manager.update_block"|fn_check_view_permissions}
                    <div class="control-group">
                        <label for="ajax_update_block_{$html_id}" class="cm-required control-label">{__("block")}:</label>
                        <div class="controls clearfix help-inline-wrap">
                            {include file="common/popupbox.tpl"
                                act="general"
                                id="select_block_`$html_id`"
                                text=__("select_block")
                                link_text=__("select_block")
                                href="block_manager.block_selection?extra_id=`$tab_data.tab_id`&on_product_tabs=1"
                                action="block_manager.block_selection"
                                opener_ajax_class="cm-ajax cm-ajax-force"
                                content=""
                                meta="pull-left"
                            }
                            <br><br>
                            <div id="ajax_update_block_{$html_id}">
                                <input type="hidden" name="block_data[block_id]" id="ajax_update_block_{$html_id}" value="{$tab_data.block_id|default:''}" />
                                {if $tab_data.block_id > 0}
                                    {include file="views/block_manager/render/block.tpl" block_data=$block_data external_render=true 
                                    external_id=$html_id}        
                                {/if}
                            <!--ajax_update_block_{$html_id}--></div>
                        </div>
                    </div>
                {/if}
            </fieldset>
        </div>
        {if $dynamic_object_scheme && $id > 0}
            <div id="content_tab_status_{$html_id}" >
                <fieldset>
                    <div class="control-group">
                        <label class="control-label">{__("global_status")}:</label>
                        <div class="controls">
                            <label class="radio text-value">{if $tab_data.status == 'A'}{__("active")}{else}{__("disabled")}{/if}</label>
                        </div>
                    </div>
                    <input type="hidden" class="cm-no-hide-input" name="snapping_data[object_type]" value="{$dynamic_object_scheme.object_type}" />
                    <div class="control-group">
                        <label class="control-label">{if $tab_data.status == 'A'}{__("disable_for")}{else}{__("enable_for")}{/if}:</label>
                        <div class="controls">
                            {include_ext
                                file=$dynamic_object_scheme.picker
                                data_id="tab_`$html_id`_product_ids"
                                input_name="tab_data[product_ids]"
                                item_ids=$tab_data.product_ids
                                view_mode="links"
                                params_array=$dynamic_object_scheme.picker_params
                            }
                        </div>
                    </div>
                </fieldset>
            <!--content_tab_status_{$html_id}--></div>
        {/if}
    </div>

<!--content_group_{$html_id}--></div>
<div class="buttons-container">
    {include file="buttons/save_cancel.tpl" but_name="dispatch[tabs.update]" cancel_action="close" save=$id}
</div>
</form>