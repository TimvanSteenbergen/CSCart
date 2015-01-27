<form action="{""|fn_url}" method="post" name="add_global_options" class="form-table form-horizontal form-edit">
{capture name="mainbox"}

{if "ULTIMATE"|fn_allowed_for && $runtime.company_id}
    {assign var="company_id" value=$runtime.company_id}
{/if}

{include file="pickers/products/picker.tpl" data_id="added_products" input_name="apply_options[product_ids]" no_item_text=__("text_no_items_defined", ["[items]" => __("products")]) type="links" company_id=$company_id placement="right"}

{include file="common/subheader.tpl" title=__("select_options")}
{foreach from=$product_options item="po"}
    <label class="checkbox">
        <input type="checkbox" value="{$po.option_id}" name="apply_options[options][]" />
        {$po.option_name}
        {include file="views/companies/components/company_name.tpl" object=$po}
    </label>
{/foreach}

{/capture}

{capture name="buttons"}
<div class="btn-group btn-hover dropleft">
    {include file="buttons/button.tpl" but_text=__("apply") but_name="dispatch[product_options.apply]" but_role="submit" but_meta="btn-primary dropdown-toggle"}
    <ul class="dropdown-menu">
        <li><a>
            <label for="link">
                <input type="hidden" name="apply_options[link]" value="N" />
                <input type="checkbox" name="apply_options[link]" id="link" value="Y"/>
            {__("apply_as_link")}
            </label>
        </a></li>
    </ul>
</div>
{/capture}

{include file="common/mainbox.tpl" title=__("apply_to_products") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons select_languages=$select_languages}
</form>