{script src="js/tygh/tabs.js"}
{script src="js/tygh/product_features.js"}

{capture name="mainbox"}

{include file="common/pagination.tpl"}

{assign var="r_url" value=$config.current_url|escape:url}

<div class="items-container{if ""|fn_check_form_permissions} cm-hide-inputs{/if}" id="update_features_list">
{if $features}
    {if $has_ungroupped}
        <div class="object-group clear no-hover row-gray">
            <strong>{__("ungroupped_features")}</strong>
        </div>
    {/if}
<table width="100%" class="table table-middle table-objects">
<tbody>
    {if $has_ungroupped}
        {foreach from=$features item="p_feature"}
            {if $p_feature.feature_type != "G"}
                {if $p_feature|fn_allow_save_object:"product_features"}
                    {include file="common/object_group.tpl" id=$p_feature.feature_id details=$p_feature.feature_description text=$p_feature.description status=$p_feature.status hidden=true href="product_features.update?feature_id=`$p_feature.feature_id`&return_url=$r_url" object_id_name="feature_id" table="product_features" href_delete="product_features.delete?feature_id=`$p_feature.feature_id`" delete_target_id="pagination_contents" header_text="{__("editing_product_feature")}: `$p_feature.description`" no_table=true company_object=$p_feature}
                {else}
                    {include file="common/object_group.tpl" id=$p_feature.feature_id details=$p_feature.feature_description text=$p_feature.description status=$p_feature.status hidden=true href="product_features.update?feature_id=`$p_feature.feature_id`&return_url=$r_url" object_id_name="feature_id" table="product_features" additional_class="cm-hide-inputs" header_text="{__("viewing_feature")}: `$p_feature.description`" update_controller="product_features" no_table=true non_editable=true link_text=__("view") is_view_link=true company_object=$p_feature}
                {/if}
            {/if}
        {/foreach}
    {/if}

    {foreach from=$features item="gr_feature"}
        {if $gr_feature.feature_type == "G"}

            {if !$gr_feature|fn_allow_save_object:"product_features"}
                {include file="common/object_group.tpl" id=$gr_feature.feature_id details=$gr_feature.feature_description text=$gr_feature.description status=$gr_feature.status hidden=true href="product_features.update?feature_id=`$gr_feature.feature_id`&return_url=$r_url" object_id_name="feature_id" table="product_features" additional_class="cm-hide-inputs" header_text="{__("viewing_feature")}: `$gr_feature.description`" no_table=true link_meta="strong" link_text=__("view") non_editable=true is_view_link=true company_object=$gr_feature additional_class="row-gray"}
            {else}
                {include file="common/object_group.tpl" id=$gr_feature.feature_id details=$gr_feature.feature_description text=$gr_feature.description status=$gr_feature.status hidden=true href="product_features.update?feature_id=`$gr_feature.feature_id`&return_url=$r_url" object_id_name="feature_id" table="product_features" href_delete="product_features.delete?feature_id=`$gr_feature.feature_id`" delete_target_id="pagination_contents,content_add_new_feature" header_text="{__("editing_group")}: `$gr_feature.description`" no_table=true link_meta="strong" company_object=$gr_feature additional_class="row-gray"}
            {/if}


            {if $gr_feature.subfeatures}
                {foreach from=$gr_feature.subfeatures item="subfeature"}

                    {if !$subfeature|fn_allow_save_object:"product_features"}
                        {include file="common/object_group.tpl" id=$subfeature.feature_id details=$subfeature.feature_description text=$subfeature.description status=$subfeature.status hidden=true href="product_features.update?feature_id=`$subfeature.feature_id`&return_url=$r_url" object_id_name="feature_id" table="product_features" additional_class="cm-hide-inputs" header_text="{__("viewing_feature")}: `$subfeature.description`"
                         update_controller="product_features" no_table=true non_editable=true link_text=__("view") is_view_link=true company_object=$subfeature}
                    {else}
                        {include file="common/object_group.tpl" id=$subfeature.feature_id details=$subfeature.feature_description text=$subfeature.description status=$subfeature.status hidden=true href="product_features.update?feature_id=`$subfeature.feature_id`&return_url=$r_url" object_id_name="feature_id" table="product_features" href_delete="product_features.delete?feature_id=`$subfeature.feature_id`" delete_target_id="pagination_contents" header_text="{__("editing_product_feature")}: `$subfeature.description`" update_controller="product_features" no_table=true company_object=$subfeature}
                    {/if}

                {/foreach}
            {/if}

        {/if}
    {/foreach}
    </tbody>
</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}
<!--update_features_list--></div>

{include file="common/pagination.tpl"}

{capture name="adv_buttons"}
    {capture name="tools_list"}
        {capture name="add_new_picker"}
            {include file="views/product_features/update.tpl" feature=[] is_group=true}
        {/capture}
        <li>{include file="common/popupbox.tpl" id="add_new_group" text=__("new_group") content=$smarty.capture.add_new_picker link_text=__("add_group") act="link" icon="true"}</li>

        {capture name="add_new_picker_2"}
            {include file="views/product_features/update.tpl" feature=[]}
        {/capture}
        <li>{include file="common/popupbox.tpl" id="add_new_feature" text=__("new_feature") content=$smarty.capture.add_new_picker_2 link_text=__("add_feature") act="link" icon="true"}</li>
    {/capture}
    {dropdown content=$smarty.capture.tools_list icon="icon-plus" no_caret=true placement="right"}
{/capture}

{capture name="sidebar"}
    {include file="common/saved_search.tpl" dispatch="product_features.manage" view_type="product_features"}
    {include file="views/product_features/components/product_features_search_form.tpl" dispatch="product_features.manage"}
{/capture}

{/capture}
{include file="common/mainbox.tpl" title=__("features") content=$smarty.capture.mainbox select_languages=true buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons sidebar=$smarty.capture.sidebar}
