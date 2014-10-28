{include file="common/pagination.tpl"}

{script src="js/tygh/exceptions.js"}

{* add-new *}
{if $products}
<table width="100%" class="table">
<thead>
<tr>
    {hook name="product_list:table_head"}
    {if $hide_amount}
    <th class="center" width="1%">
        {if $show_radio}&nbsp;{else}{include file="common/check_items.tpl"}{/if}
    </th>
    {/if}
    <th width="80%">{__("product_name")}</th>
    {if $show_price}
    <th class="right">{__("price")}</th>
    {/if}
    {if !$hide_amount}
    <th class="center" width="5%">{__("quantity")}</th>
    {/if}
    {/hook}
</tr>
</thead>
{if !$checkbox_name}{assign var="checkbox_name" value="add_products_ids"}{/if}
{foreach from=$products item=product}
<tr id="picker_product_row_{$product.product_id}">
    {hook name="product_list:table_content"}
    {if $hide_amount}
    <td class="center" width="1%"><input type="{if $show_radio}radio{else}checkbox{/if}" name="{$checkbox_name}[]" value="{$product.product_id}" class="cm-item mrg-check" id="checkbox_id_{$product.product_id}" /></td>
    {/if}
    <td>
        <input type="hidden" id="product_{$product.product_id}" value="{$product.product}" />
        {if $hide_amount}
            <label for="checkbox_id_{$product.product_id}">{$product.product nofilter}</label>
        {else}
            <span>{$product.product nofilter}</span>
        {/if}

        {if !$hide_options}
            {include file="views/products/components/select_product_options.tpl" id=$product.product_id product_options=$product.product_options name="product_data" show_aoc=$show_aoc additional_class=$additional_class}
        {/if}
    </td>
    {if $show_price}
    <td class="cm-picker-product-options right">{if !$product.price|floatval && $product.zero_price_action == "A"}<input class="input-medium" id="product_price_{$product.product_id}" type="text" size="3" name="product_data[{$product.product_id}][price]" value="" />{else}{include file="common/price.tpl" value=$product.price}{/if}</td>
    {/if}
    {if !$hide_amount}
    <td class="center nowrap cm-value-changer" width="5%">

        <div class="input-prepend input-append">
            <a class="btn no-underline strong increase-font cm-decrease"><i class="icon-minus"></i></a>
            <input id="product_id_{$product.product_id}" type="text" value="0" name="product_data[{$product.product_id}][amount]" size="3" class="input-micro cm-amount"{if $product.qty_step > 1} data-ca-step="{$product.qty_step}"{/if} />
            <a class="btn no-underline strong increase-font cm-increase"><i class="icon-plus"></i></a>
        </div>

    </td>
    {/if}
    {/hook}
    
    {hook name="product_list:table_columns"}
    {/hook}
    
</tr>
{/foreach}
</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}


<script type="text/javascript">
(function(_, $) {

    function _switchAOC(id, disable)
    {
        var aoc = $('#sw_option_' + id + '_AOC');
        if (aoc.length) {
            aoc.addClass('cm-skip-avail-switch');
            aoc.prop('disabled', disable);
            disable = aoc.prop('checked') ? true : disable;
        }

        $('.cm-picker-product-options', $('#picker_product_row_' + id)).switchAvailability(disable, false);
    }

    $(document).ready(function() {

        $.ceEvent('on', 'ce.commoninit', function(context) {
            if (context.find('tr[id^=picker_product_row_]').length) {
                context.find('.cm-picker-product-options').switchAvailability(true, false);
            }
        });

        $(_.doc).on('click', '.cm-increase,.cm-decrease', function() {
            var inp = $('input', $(this).closest('.cm-value-changer'));
            var new_val = parseInt(inp.val()) + ($(this).is('a.cm-increase') ? 1 : -1);
            var disable = new_val > 0 ? false : true;
            var _id = inp.prop('id').replace('product_id_', '');

            _switchAOC(_id, disable);
        });

        $(_.doc).on('change', '.cm-amount', function() {
            var new_val = parseInt($(this).val());
            var disable = new_val > 0 ? false : true;
            var _id = $(this).prop('id').replace('product_id_', '');

            _switchAOC(_id, disable);
        });        
        
        $(_.doc).on('click', '.cm-item', function() {
            var disable = (this.checked) ? false : true;
            var _id = $(this).prop('id').replace('checkbox_id_', '');

            _switchAOC(_id, disable);
        });

        $(_.doc).on('click', '.cm-check-items', function() {
            var _checked = this.checked;
            $('.cm-item').each(function () {
                if (_checked && !this.checked || !_checked && this.checked) {
                    $(this).click();
                }
            });
        });
    });
}(Tygh, Tygh.$));
</script>

{include file="common/pagination.tpl"}
