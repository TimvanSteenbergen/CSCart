{script src="js/tygh/tabs.js"}
{literal}
    <script type="text/javascript">
    function fn_check_option_type(value, tag_id)
    {
        var id = tag_id.replace('option_type_', '').replace('elm_', '');
        Tygh.$('#tab_option_variants_' + id).toggleBy(!(value == 'S' || value == 'R' || value == 'C'));
        Tygh.$('#required_options_' + id).toggleBy(!(value == 'I' || value == 'T' || value == 'F'));
        Tygh.$('#extra_options_' + id).toggleBy(!(value == 'I' || value == 'T'));
        Tygh.$('#file_options_' + id).toggleBy(!(value == 'F'));

        if (value == 'C') {
            var t = Tygh.$('table', '#content_tab_option_variants_' + id);
            Tygh.$('.cm-non-cb', t).switchAvailability(true); // hide obsolete columns
            Tygh.$('tbody:gt(1)', t).switchAvailability(true); // hide obsolete rows

        } else if (value == 'S' || value == 'R') {
            var t = Tygh.$('table', '#content_tab_option_variants_' + id);
            Tygh.$('.cm-non-cb', t).switchAvailability(false); // show all columns
            Tygh.$('tbody', t).switchAvailability(false); // show all rows
            Tygh.$('#box_add_variant_' + id).show(); // show "add new variants" box

        } else if (value == 'I' || value == 'T') {
            Tygh.$('#extra_options_' + id).show(); // show "add new variants" box
        }
    }
    </script>
{/literal}

{capture name="mainbox"}

    {if $object == "global"}
        {assign var="select_languages" value=true}
        {assign var="delete_target_id" value="pagination_contents"}
    {else}
        {assign var="delete_target_id" value="product_options_list"}
    {/if}

    {include file="common/pagination.tpl"}

    {if !($runtime.company_id && $product_data.shared_product == "Y" && $runtime.company_id != $product_data.company_id)}
        {capture name="toolbar"}
            {capture name="add_new_picker"}
                {if $product_data}
                    {include file="views/product_options/update.tpl" option_id="0" company_id=$product_data.company_id disable_company_picker=true}
                {else}
                    {include file="views/product_options/update.tpl" option_id="0"}
                {/if}
            {/capture}
            {if $object == "product"}
                {assign var="position" value="pull-right"}
            {/if}
            {if $view_mode == "embed"}
                {include file="common/popupbox.tpl" id="add_new_option" text=__("new_option") link_text=__("add_option") act="general" content=$smarty.capture.add_new_picker meta=$position icon="icon-plus"}

            {else}
                {include file="common/popupbox.tpl" id="add_new_option" text=__("new_option") title=__("add_option") act="general" content=$smarty.capture.add_new_picker meta=$position icon="icon-plus"}
            {/if}

        {/capture}
        {$extra nofilter}
    {/if}
        {if $object != "global"}
            <div class="btn-toolbar clearfix cm-toggle-button">
                {$smarty.capture.toolbar nofilter}
            </div>
        {else}
            {capture name="buttons"}
                {if $product_options && $object == "global"}
                    {include file="buttons/button.tpl" but_text=__("apply_to_products") but_role="action" but_href="product_options.apply"}
                {/if}
            {/capture}
            {capture name="adv_buttons"}
                {$smarty.capture.toolbar nofilter}
            {/capture}
        {/if}

        <div class="items-container" id="product_options_list">
            {if $product_options}
            <table width="100%" class="table table-middle table-objects">
                <tbody>
                    {foreach from=$product_options item="po"}
                        {if $object == "product" && !$po.product_id}
                            {assign var="details" value="({__("global")})"}
                            {assign var="query_product_id" value=""}
                        {else}
                            {assign var="details" value=""}
                            {assign var="query_product_id" value="&product_id=`$product_id`"}
                        {/if}

                        {if $object == "product"}
                            {if !$po.product_id}
                                {assign var="query_product_id" value="&object=`$object`"}
                            {else}
                                {assign var="query_product_id" value="&product_id=`$product_id`&object=`$object`"}
                            {/if}
                            {assign var="query_delete_product_id" value="&product_id=`$product_id`"}
                            {assign var="allow_save" value=$product_data|fn_allow_save_object:"products"}
                        {else}
                            {assign var="query_product_id" value=""}
                            {assign var="query_delete_product_id" value=""}
                            {assign var="allow_save" value=$po|fn_allow_save_object:"product_options"}
                        {/if}

                        {if "MULTIVENDOR"|fn_allowed_for}
                            {if $allow_save}
                                {assign var="link_text" value=__("edit")}
                                {assign var="additional_class" value="cm-no-hide-input"}
                                {assign var="hide_for_vendor" value=false}
                            {else}
                                {assign var="link_text" value=__("view")}
                                {assign var="additional_class" value=""}
                                {assign var="hide_for_vendor" value=true}
                            {/if}
                        {/if}

                        {assign var="status" value=$po.status}
                        {assign var="href_delete" value="product_options.delete?option_id=`$po.option_id``$query_delete_product_id`"}

                        {if "ULTIMATE"|fn_allowed_for}
                            {assign var="non_editable" value=false}
                            {if $runtime.company_id && (($product_data.shared_product == "Y" && $runtime.company_id != $product_data.company_id) || ($object == "global" && $runtime.company_id != $po.company_id))}
                                {assign var="link_text" value=__("view")}
                                {assign var="href_delete" value=false}
                                {assign var="non_editable" value=true}
                                {assign var="is_view_link" value=true}
                            {/if}
                        {/if}

                        {include file="common/object_group.tpl"
                        no_table=true
                        id=$po.option_id
                        id_prefix="_product_option_"
                        details=$details
                        text=$po.option_name
                        hide_for_vendor=$hide_for_vendor
                        status=$status
                        table="product_options"
                        object_id_name="option_id"
                        href="product_options.update?option_id=`$po.option_id``$query_product_id`"
                        href_delete=$href_delete
                        delete_target_id=$delete_target_id
                        header_text="{__("editing_option")}: `$po.option_name`"
                        skip_delete=!$allow_save
                        additional_class=$additional_class
                        prefix="product_options"
                        link_text=$link_text
                        non_editable=$non_editable
                        company_object=$po}
                {/foreach}
                </tbody>
            </table>
            {else}
                <p class="no-items">{__("no_data")}</p>
            {/if}
            <!--product_options_list--></div>
    {include file="common/pagination.tpl"}

{/capture}

{if $object == "product"}
    {$smarty.capture.mainbox nofilter}
{else}
    {include file="common/mainbox.tpl" title=__("options") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons select_language=$select_language}
{/if}
