{if !$smarty.request.extra}
<script type="text/javascript">
(function(_, $) {
    _.tr('text_items_added', '{__("text_items_added")|escape:"javascript"}');

    $.ceEvent('on', 'ce.formpost_banners_form', function(frm, elm) {

        var banners = {};

        if ($('input.cm-item:checked', frm).length > 0) {
            $('input.cm-item:checked', frm).each( function() {
                var id = $(this).val();
                banners[id] = $('#banner_' + id).text();
            });

            {literal}
            $.cePicker('add_js_item', frm.data('caResultId'), banners, 'b', {
                '{banner_id}': '%id',
                '{banner}': '%item'
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
</head>
<form action="{$smarty.request.extra|fn_url}" data-ca-result-id="{$smarty.request.data_id}" method="post" name="banners_form">
{if $banners}
<table width="100%" class="table table-middle">
<thead>
<tr>
    <th>
        {include file="common/check_items.tpl"}</th>
    <th>{__("banner")}</th>
</tr>
</thead>
{foreach from=$banners item=banner}
<tr>
    <td>
        <input type="checkbox" name="{$smarty.request.checkbox_name|default:"banners_ids"}[]" value="{$banner.banner_id}" class="cm-item" /></td>
    <td id="banner_{$banner.banner_id}" width="100%">{$banner.banner}</td>
</tr>
{/foreach}
</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}

{if $banners}
<div class="buttons-container">
    {include file="buttons/add_close.tpl" but_text=__("add_banners") but_close_text=__("add_banners_and_close") is_js=$smarty.request.extra|fn_is_empty}
</div>
{/if}

</form>
