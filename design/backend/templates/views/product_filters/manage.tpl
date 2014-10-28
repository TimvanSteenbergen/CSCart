{script src="js/tygh/tabs.js"}

<script type="text/javascript">
    var filter_fields = {ldelim}{rdelim};
    {foreach from=$filter_fields item=filter_field key=key}
    filter_fields['{$key}'] = '{$filter_field.slider}';
    {/foreach}

{literal}
function fn_check_product_filter_type(value, tab_id, id)
{
    var $ = Tygh.$;
    $('#' + tab_id).toggleBy(!(value.indexOf('R') == 0) && !(value.indexOf('D') == 0));
    $('[id^=inputs_ranges' + id + ']').toggleBy((value.indexOf('D') == 0));
    $('[id^=dates_ranges' + id + ']').toggleBy(!(value.indexOf('D') == 0));
    $('#round_to_' + id + '_container').toggleBy(!filter_fields[value.replace(/\w+-/, '')]);
    $('#display_count_' + id + '_container').toggleBy(!(value.indexOf('R') == 0) && !(value.indexOf('F') == 0) && !(value.indexOf('S') > 0));
    $('#display_more_count_' + id + '_container').toggleBy(!(value.indexOf('R') == 0) && !(value.indexOf('F') == 0) && !(value.indexOf('S') > 0));
}
</script>
{/literal}

{capture name="mainbox"}

{include file="common/pagination.tpl" object_type="filters"}

{assign var="r_url" value=$config.current_url|escape:url}

<div class="items-container{if ""|fn_check_form_permissions} cm-hide-inputs{/if}" id="manage_filters_list">
<table width="100%" class="table table-middle table-objects">
<tbody>

{foreach from=$filters item="filter"}
    
    {if $filter|fn_allow_save_object:"product_filters"}
        {include file="common/object_group.tpl"
            id=$filter.filter_id
            show_id=true
            details=$filter.filter_description
            text=$filter.filter
            status=$filter.status
            href="product_filters.update?filter_id=`$filter.filter_id`&return_url=$r_url"
            object_id_name="filter_id"
            href_delete="product_filters.delete?filter_id=`$filter.filter_id`"
            delete_target_id="pagination_contents"
            table="product_filters"
            no_table=true
            additional_class="cm-no-hide-input"
            header_text="{__("editing_filter")}: `$filter.filter`"
            link_text=__("edit")
            company_object=$filter
        }
    {else}
        {include file="common/object_group.tpl"
            id=$filter.filter_id
            show_id=true
            details=$filter.filter_description
            text=$filter.filter
            status=$filter.status
            href="product_filters.update?filter_id=`$filter.filter_id`&return_url=$r_url"
            object_id_name="filter_id"
            table="product_filters"
            no_table=true
            additional_class=""
            header_text="{__("viewing_filter")}:&nbsp;`$filter.filter`"
            link_text=__("view")
            non_editable=true
            is_view_link=true
            company_object=$filter
        }
    {/if}

{foreachelse}

    <p class="no-items">{__("no_data")}</p>

{/foreach}
</tbody>
</table>
<!--manage_filters_list--></div>

{include file="common/pagination.tpl" object_type="filters"}

{capture name="adv_buttons"}
    {capture name="add_new_picker"}
        {include file="views/product_filters/update.tpl" filter=[]}
    {/capture}
    {if !"MULTIVENDOR"|fn_allowed_for || (!$runtime.company_id && "MULTIVENDOR"|fn_allowed_for)}
    {include file="common/popupbox.tpl" id="add_product_filter" text=__("new_filter") content=$smarty.capture.add_new_picker title=__("add_filter") act="general" icon="icon-plus"}
    {/if}
{/capture}

{/capture}

{capture name="sidebar"}
{include file="common/saved_search.tpl" dispatch="product_filters.manage" view_type="product_filters"}
{include file="views/product_filters/components/product_filters_search_form.tpl" dispatch="product_filters.manage"}
{/capture}

{include file="common/mainbox.tpl" title=__("filters") content=$smarty.capture.mainbox adv_buttons=$smarty.capture.adv_buttons sidebar=$smarty.capture.sidebar select_languages=true}