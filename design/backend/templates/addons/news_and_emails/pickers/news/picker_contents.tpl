
{if !$smarty.request.extra}
<script type="text/javascript">
(function(_, $) {
    _.tr('text_items_added', '{__("text_items_added")|escape:"javascript"}');

    $.ceEvent('on', 'ce.formpost_news_form', function(frm, elm) {
        var news = {};

        if ($('input.cm-item:checked', frm).length > 0) {
            $('input.cm-item:checked', frm).each( function() {
                var id = $(this).val();
                news[id] = $('#news_' + id).text();
            });

            {literal}
            $.cePicker('add_js_item', frm.data('caResultId'), news, 'n', {
                '{news_id}': '%id',
                '{news}': '%item'
            });
            {/literal}

            $.ceNotification('show', {
                type: 'N', 
                title: _.tr('notice'), 
                message: _.tr('text_items_added'), 
                message_state: 'I'
            });
        }

        return false;        
    });
}(Tygh, Tygh.$));
</script>
{/if}

<form action="{$smarty.request.extra|fn_url}" data-ca-result-id="{$smarty.request.data_id}" method="post" name="news_form" class="form-edit">

{include file="common/pagination.tpl" div_id="pagination_`$smarty.request.content_id`"}

{if $news}
<table width="100%" class="table table-middle">
<thead>
<tr>
    <th>
        {include file="common/check_items.tpl"}
    </th>
    <th>{__("news")}</th>
</tr>
</thead>
<tbody>
{foreach from=$news item=n}
<tr>
    <td>
        <input type="checkbox" name="{$smarty.request.checkbox_name|default:"news_ids"}[]" value="{$n.news_id}" class="cm-item" />
    </td>
    <td width="100%" id="news_{$n.news_id}">{$n.news}</td>
</tr>
{/foreach}
</tbody>
</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}

{include file="common/pagination.tpl" div_id="pagination_`$smarty.request.content_id`"}

{if $news}
<div class="buttons-container">
    {include file="buttons/add_close.tpl" but_text=__("add_news") but_close_text=__("add_news_and_close") is_js=$smarty.request.extra|fn_is_empty}
</div>
{/if}
</form>
