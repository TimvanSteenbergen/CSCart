{if $runtime.company_id && (!$selected || "MULTIVENDOR"|fn_allowed_for) &&  !$disable_company_picker}
    {$selected = $runtime.company_id}
{/if}

{if !$selected}
    {if $zero_company_id_name_lang_var}
        {$selected = "0"}
    {else}
        {$selected = fn_get_default_company_id()}
    {/if}
{/if}

{if $reload_form}
    {$js_action = "fn_reload_form(elm);"}
{/if}

{capture name="c_body"}
    <input type="hidden" name="{$name}" id="{$id|default:"company_id"}" value="{$selected}">
    {if !$runtime.simple_ultimate}
        {if $runtime.company_id || $disable_company_picker}
            <div class="text-type-value">{$selected|fn_get_company_name:$zero_company_id_name_lang_var}</div>
        {else}
            <div class="text-type-value ajax-select-wrap {$meta}">
                {if $zero_company_id_name_lang_var}
                    {$url_extra = "&show_all=Y&default_label=`$zero_company_id_name_lang_var`"}
                {/if}
                {include file="common/ajax_select_object.tpl"
                    data_url="companies.get_companies_list?onclick=`$onclick`$url_extra"
                    text=$selected|fn_get_company_name:$zero_company_id_name_lang_var
                    result_elm=$id|default:"company_id"
                    id="`$id`_selector"
                    js_action=$js_action
                }
            </div>
        {/if}
    {/if}
{/capture}

{if !$runtime.simple_ultimate}
    {if !$no_wrap}
        <div class="control-group">
            <label class="control-label" for="{$id|default:"company_id"}">{__("vendor")}{if $tooltip} {capture name="tooltip"}{$tooltip}{/capture}{include file="common/tooltip.tpl" tooltip=$smarty.capture.tooltip}{/if}</label>
            <div class="controls">
                {$smarty.capture.c_body nofilter}
            </div>
        </div>
    {else}
        {$smarty.capture.c_body nofilter}
    {/if}
{else}
    {$smarty.capture.c_body nofilter}
{/if}