{if $runtime.company_id && !$company_id}
    {$company_id = $runtime.company_id}
{/if}

{$result_ids = "content_detailed" scope="root"}

{$supplier = fn_if_get_supplier($selected, $company_id)}

{if $supplier !== false}

    {capture name="s_body"}
        <input type="hidden" name="{$name}" id="{$id|default:"supplier_id"}" value="{$supplier.supplier_id}" />
        {if $read_only}
            {$supplier.name}
        {else}
            <div class="text-type-value ajax-select-wrap">
                {include file="common/ajax_select_object.tpl" data_url="suppliers.get_suppliers_list?company_id=`$company_id`" text=$supplier.name result_elm=$id|default:"supplier_id" id="`$id`_selector"}
            </div>
        {/if}
    {/capture}

    {if !$no_wrap}
        <div class="control-group" id="suppliers_selector">
            <label class="control-label" for="{$id|default:"supplier_id"}">{__("supplier")}{if $tooltip} {capture name="tooltip"}{$tooltip}{/capture}{include file="common/tooltip.tpl" tooltip=$smarty.capture.tooltip}{/if}</label>
            <div class="controls">
                {$smarty.capture.s_body nofilter}
            </div>
        <!--suppliers_selector--></div>
    {else}
        {$smarty.capture.c_body nofilter}
    {/if}

{/if}