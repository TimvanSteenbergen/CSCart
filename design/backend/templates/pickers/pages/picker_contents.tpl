{if !$smarty.request.extra}
<script type="text/javascript">
(function(_, $) {
    _.tr('text_items_added', '{__("text_items_added")|escape:"javascript"}');
    var display_type = '{$smarty.request.display|escape:javascript nofilter}';

    $.ceEvent('on', 'ce.formpost_pages_form', function(frm, elm) {
        var pages = {};

        if ($('input.cm-item:checked', frm).length > 0) {
            $('input.cm-item:checked', frm).each( function() {
                var id = $(this).val();
                pages[id] = $('#page_title_' + id).text();
            });

            {literal}
            $.cePicker('add_js_item', frm.data('caResultId'), pages, 'a', {
                '{page_id}': '%id',
                '{page}': '%item'
            });
            {/literal}

            if (display_type != 'radio') {
                $.ceNotification('show', {
                    type: 'N', 
                    title: _.tr('notice'), 
                    message: _.tr('text_items_added'), 
                    message_state: 'I'
                });
            }
        }

        return false;        
    });
}(Tygh, Tygh.$));
</script>
{/if}

{include file="views/pages/components/pages_search_form.tpl" dispatch="pages.picker" extra="<input type=\"hidden\" name=\"result_ids\" value=\"pagination_`$smarty.request.data_id`\"><input type=\"hidden\" name=\"get_tree\" value=\"\"><input type=\"hidden\" name=\"root\" value=\"\">" put_request_vars=true form_meta="cm-ajax" in_popup=true}

<form action="{$smarty.request.extra|fn_url}" data-ca-result-id="{$smarty.request.data_id}" method="post" name="pages_form">

    {include file="common/pagination.tpl" div_id="pagination_`$smarty.request.data_id`"}

    {if $pages_tree}
        <div class="items-container multi-level">
            {math equation="rand()" assign="random_value"}
            {assign var="combination_suffix" value=$combination_suffix|default:"_`$random_value`"}
            {include file="views/pages/components/pages_tree.tpl" header=true picker=true checkbox_name=$smarty.request.checkbox_name hide_delete_button=true display=$smarty.request.display dispatch="pages.picker" combination_suffix=$combination_suffix}
        </div>
    {else}
        <div class="items-container"><p class="no-items">{__("no_data")}</p></div>
    {/if}


    {include file="common/pagination.tpl" div_id="pagination_`$smarty.request.data_id`"}

    {if $pages_tree}
    <div class="buttons-container">
        {if $smarty.request.display == "radio"}
            {assign var="but_close_text" value=__("choose")}
        {else}
            {assign var="but_close_text" value=$button_names.but_close_text|default:__("add_pages_and_close")}
            {assign var="but_text" value=$button_names.but_text|default:__("add_pages")}
        {/if}
        {include file="buttons/add_close.tpl" is_js=$smarty.request.extra|fn_is_empty}
    </div>
    {/if}
</form>
