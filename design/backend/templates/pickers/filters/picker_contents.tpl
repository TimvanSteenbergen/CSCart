{if !$smarty.request.extra}
<script type="text/javascript">
(function(_, $) {
    _.tr('text_items_added', '{__("text_items_added")|escape:"javascript"}');
    var display_type = '{$smarty.request.display|escape:javascript nofilter}';

    $.ceEvent('on', 'ce.formpost_filters_form', function(frm, elm) {
        var filters = {};

        if ($('input.cm-item:checked', frm).length > 0) {
            $('input.cm-item:checked', frm).each( function() {
                var id = $(this).val();
                filters[id] = $('#filter_title_' + id).text();
            });

            {literal}
            $.cePicker('add_js_item', frm.data('caResultId'), filters, 'f', {
                '{filter_id}': '%id',
                '{filter}': '%item'
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

{include file="views/product_filters/components/product_filters_search_form.tpl" dispatch="product_filters.picker" extra="<input type=\"hidden\" name=\"result_ids\" value=\"pagination_`$smarty.request.data_id`\">" put_request_vars=true form_meta="cm-ajax" in_popup=true}

<form action="{$smarty.request.extra|fn_url}" data-ca-result-id="{$smarty.request.data_id}" method="post" name="filters_form">

    {include file="common/pagination.tpl" div_id="pagination_`$smarty.request.data_id`"}

    {if $filters}

        <table width="100%" class="table table-middle">
            <thead>
            <tr>
                <th width="1%" class="center">
                    {if $smarty.request.display == "checkbox"}
                        {include file="common/check_items.tpl"}
                    {/if}
                </th>
                <th>{__("name")}</th>
                <th>{__("description")}</th>
                <th>{__("status")}</th>
            </tr>
            </thead>
            {foreach from=$filters item=filter}
                <tr>
                    <td class="left">
                        {if $smarty.request.display == "checkbox"}
                            <input type="checkbox" name="add_filters[]" value="{$filter.filter_id}" class="cm-item" />
                            {elseif $smarty.request.display == "radio"}
                            <input type="radio" name="selected_filter_id" value="{$filter.filter_id}" />
                        {/if}
                    </td>
                    <td id="filter_title_{$filter.filter_id}">{$filter.filter}</td>
                    <td>{$filter.filter_description nofilter}</td>
                    <td class="center">
                        {if $filter.status == "A"}
                            {__("active")}
                        {else}
                            {__("disabled")}
                        {/if}
                    </td>
                </tr>
            {/foreach}
        </table>
    {else}
        <div class="items-container"><p class="no-items">{__("no_data")}</p></div>
    {/if}

    {include file="common/pagination.tpl" div_id="pagination_`$smarty.request.data_id`"}

    {if $filters}
    <div class="buttons-container">
        {if $smarty.request.display == "radio"}
            {assign var="but_close_text" value=__("choose")}
        {else}
            {assign var="but_close_text" value=$button_names.but_close_text|default:__("add_filters_and_close")}
            {assign var="but_text" value=$button_names.but_text|default:__("add_filters")}
        {/if}
        {include file="buttons/add_close.tpl" is_js=$smarty.request.extra|fn_is_empty}
    </div>
    {/if}
</form>
