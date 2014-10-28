{if !$smarty.request.extra}
<script type="text/javascript">
(function(_, $) {

    _.tr('text_items_added', '{__("text_items_added")|escape:"javascript"}');
    _.tr('options', '{__("options")|escape:"javascript"}');

{if $smarty.request.display == "options" || $smarty.request.display == "options_amount" || $smarty.request.display == "options_price"}
    _.tr('no', '{__("no")|escape:"javascript"}');
    _.tr('yes', '{__("yes")|escape:"javascript"}');
    _.tr('aoc', '{__("any_option_combinations")|escape:"javascript"}');

    function _getDescription(obj, id)
    {
        var p = {};
        var d = '';
        var aoc = $('#sw_option_' + id + '_AOC');
        if (aoc.length && aoc.prop('checked')) {
            d = _.tr('aoc');
        } else {
            $(':input', $('#option_' + id + '_AOC')).each( function() {
                var op = this;
                var j_op = $(this);
                
                if (typeof(op.name) == 'string' && op.name == '') {
                    return false;
                }

                var option_id = op.name.match(/\[(\d+)\]$/)[1];
                if (op.type == 'checkbox') {
                    var variant = (op.checked == false) ? _.tr('no') : _.tr('yes');
                }
                if (op.type == 'radio' && op.checked == true) {
                    var variant = $('#option_description_' + id + '_' + option_id + '_' + op.value).text();
                }
                if (op.type == 'select-one') {
                    var variant = op.options[op.selectedIndex].text;
                }
                if ((op.type == 'text' || op.type == 'textarea') && op.value != '') {
                    if (j_op.hasClass('cm-hint') && op.value == op.defaultValue) { //FIXME: We should not become attached to cm-hint class
                        var variant = '';
                    } else {
                        var variant = op.value;
                    }
                }
                if ((op.type == 'checkbox') || ((op.type == 'text' || op.type == 'textarea') && op.value != '') || (op.type == 'select-one') || (op.type == 'radio' && op.checked == true)) {
                    if (op.type == 'checkbox') {
                        p[option_id] = (op.checked == false) ? $('#unchecked_' + id + '_option_' + option_id).val() : op.value;
                    }else{
                        p[option_id] = (j_op.hasClass('cm-hint') && op.value == op.defaultValue) ? '' : op.value; //FIXME: We should not become attached to cm-hint class
                    }

                    d += (d ? ',  ' : '') + $('#option_description_' + id + '_' + option_id).text() + variant;
                }
            });
        }
        return {
            path: p, 
            desc: d != '' ? '<span>' + _.tr('options') + ':  </span>' + d : ''
        };
    }
{/if}

    $.ceEvent('on', 'ce.formpost_add_products', function(frm, elm) {
        var products = {};
        var _display = frm.find("[name=display_type]").val();

        if ($('input.cm-item:checked', frm).length > 0) {
            $('input.cm-item:checked', frm).each( function() {
                var id = $(this).val();
                
                if (_display == "options" || _display == "options_amount" || _display == "options_price") {

                    products[id] = {
                        option: _getDescription(frm, id),
                        value: $('#product_' + id).val()
                    };
                } else {
                    products[id] = $('#product_' + id).val();
                }
            });
            
            $.ceEvent('trigger', 'ce.picker_transfer_js_items', [products]);
            
            $.cePicker('add_js_item', frm.data('caResultId'), products, 'p', {});

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

{include file="views/products/components/products_search_form.tpl" dispatch="products.picker" picker_selected_companies=$picker_selected_companies extra="<input type=\"hidden\" name=\"result_ids\" value=\"pagination_`$smarty.request.data_id`\">" put_request_vars=true form_meta="cm-ajax" in_popup=true}

<form action="{$smarty.request.extra|fn_url}" method="post" name="add_products" data-ca-result-id="{$smarty.request.data_id}" enctype="multipart/form-data">
<input type="hidden" name="display_type" value="{$smarty.request.display}">

{$but_text = __("add_products")}
{$but_close_text = __("add_products_and_close")}

{if $smarty.request.display != "options_amount" && $smarty.request.display != "options_price"}
    {$hide_amount = true}
{/if}

{if $smarty.request.display == "options_price"}
    {$show_price = true}
{/if}

{if $smarty.request.display == "radio"}
    {$show_radio = true}
    {$hide_options = true}
    {$but_text = ""}
    {$but_close_text = __("choose")}
{/if}

{include file="views/products/components/products_list.tpl" products=$products form_name="add_products" checkbox_id="add_product_checkbox" div_id="pagination_`$smarty.request.data_id`" hide_amount=$hide_amount show_price=$show_price checkbox_name=$smarty.request.checkbox_name show_aoc=$smarty.request.aoc additional_class="option-item" show_radio=$show_radio hide_options=$hide_options}

{if $products}
<div class="buttons-container">
    {include file="buttons/add_close.tpl" but_text=$but_text but_close_text=$but_close_text is_js=$smarty.request.extra|fn_is_empty}
</div>
{/if}

</form>
