{if !$smarty.request.extra}
<script type="text/javascript">
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
</script>
{/if}

<form action="{$smarty.request.extra|fn_url}" data-ca-result-id="{$smarty.request.data_id}" method="post" name="categories_form">

<div class="items-container multi-level">
    {if $categories_tree}
        {include file="views/categories/components/categories_tree_simple.tpl" header=true checkbox_name=$smarty.request.checkbox_name|default:"categories_ids" parent_id=$category_id display=$smarty.request.display}    
    {else}
        <p class="no-items center">
            {__("no_categories_available")}
            {if "ULTIMATE"|fn_allowed_for}
                <a href="{"categories.manage"|fn_url}">{__("manage_categories")}.</a>
            {/if}
        </p>
    {/if}
</div>

<div class="buttons-container">
    {if $smarty.request.display == "radio"}
        {assign var="but_close_text" value=__("choose")}
    {else}
        {assign var="but_close_text" value=__("add_categories_and_close")}
        {assign var="but_text" value=__("add_categories")}
    {/if}
    {include file="buttons/add_close.tpl" is_js=$smarty.request.extra|fn_is_empty}
</div>

</form>
