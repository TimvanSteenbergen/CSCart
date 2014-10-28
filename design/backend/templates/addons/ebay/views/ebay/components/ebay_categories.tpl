<div class="control-group" id="box_ebay_{$data_id}">
    <label class="control-label{if $required_field} cm-required{/if}" for="elm_ebay_{$data_id}">{__("ebay_`$data_id`")}:</label>
    <div class="controls">
    <select {if $ebay_categories}class="input-large"{else}disabled="disabled"{/if} id="elm_ebay_{$data_id}" name="template_data[{$data_id}]"{if $data_id == 'category'} onchange="Tygh.$.ceAjax('request', fn_url('ebay.get_category_features?data_id={$data_id}&template_id={$template_data.template_id}&category_id=' + this.value), {$ldelim}result_ids: 'box_ebay_cf_{$data_id}', caching: true{$rdelim});"{/if}>
        <option value="">{__('select')}</option>
        {foreach from=$ebay_categories item="item"}
            <option {if $selected_ebay_category == $item.category_id}selected="selected"{/if} value="{$item.category_id}">{$item.full_name}</option>
        {/foreach}
    </select>
    </div>
<!--box_ebay_{$data_id}--></div>

{if !empty($selected_ebay_category) && $data_id == 'category'}
<script type="text/javascript">
    $(document).ready(function(){$ldelim}
        Tygh.$.ceAjax('request', fn_url('ebay.get_category_features?data_id={$data_id}&template_id={$template_data.template_id}&category_id={$selected_ebay_category}'), {$ldelim}result_ids: 'box_ebay_cf_category', caching: true{$rdelim});
    {$rdelim});
</script>
{/if}