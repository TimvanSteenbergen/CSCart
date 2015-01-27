{assign var="id" value=$id|default:"supplier_id"}
{assign var="name" value=$name|default:"supplier_id"}

<div class="{$class|default:"control-group"}">
    <input type="hidden" name="{$name}" id="{$id}" value="{$search.supplier_id|default:''}" />
    <label class="control-label">{__("search_by_supplier")}</label>
    <div class="controls">
    {include file="common/ajax_select_object.tpl" data_url="suppliers.get_suppliers_list?show_all=Y&search=Y" text=$search.supplier_id|fn_get_supplier_name result_elm=$id id="`$id`_selector"}
    </div>
</div>