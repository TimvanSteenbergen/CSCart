{if !$smarty.request.extra}
<script type="text/javascript">
//<![CDATA[
(function(_, $) {
    _.tr('text_items_added', '{__("text_items_added")|escape:"javascript"}');
    var display_type = '{$smarty.request.display|escape:javascript nofilter}';

    $.ceEvent('on', 'ce.formpost_categories_form', function(frm, elm) {
        var categories = {};

        if ($('input.cm-item:checked', frm).length > 0) {
            $('input.cm-item:checked', frm).each( function() {
                var id = $(this).val();
                categories[id] = $('#category_' + id).text();
            });

            {literal}
            $.cePicker('add_js_item', frm.data('caResultId'), categories, 'c', {
                '{category_id}': '%id',
                '{category}': '%item'
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
//]]>
</script>
{/if}

<form action="{$smarty.request.extra|fn_url}" data-ca-result-id="{$smarty.request.data_id}" method="post" name="categories_form">

{assign var="level" value="0"}
<div class="category-rows">{include file="views/categories/components/categories_tree_simple.tpl" header="1" form_name="discounted_categories_form" checkbox_name=$smarty.request.checkbox_name|default:"categories_ids" parent_id=$category_id display=$smarty.request.display}</div>

<div class="buttons-container picker">
    <div>{if $smarty.request.display == "radio"}
            {assign var="but_close_text" value=__("choose")}
        {else}
            {assign var="but_close_text" value=__("add_categories_and_close")}
            {assign var="but_text" value=__("add_categories")}
        {/if}
        {include file="buttons/add_close.tpl" is_js=$smarty.request.extra|fn_is_empty}</div>
</div>

</form>
