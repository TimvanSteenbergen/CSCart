{capture name="extra"}
    {if $global_options}
        {capture name="add_global_option"}
            <form action="{""|fn_url}" method="post" name="apply_global_option" class="form-horizontal form-edit">
                <input type="hidden" name="product_id" value="{$smarty.request.product_id}" />
                <input type="hidden" name="selected_section" value="options" />

                <div class="control-group">
                    <label class="control-label" for="global_option_id">{__("global_options")}</label>
                    <div class="controls">
                        <select name="global_option[id]" id="global_option_id">
                            {foreach from=$global_options item="option_" key="id"}
                                <option value="{$option_.option_id}">{$option_.option_name}{if $option_.company_id} ({__("vendor")}: {$option_.company_id|fn_get_company_name}){/if}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="global_option_link">{__("apply_as_link")}</label>
                    <div class="controls">
                        <input type="hidden" name="global_option[link]" value="N" />
                        <input type="checkbox" name="global_option[link]" id="global_option_link" value="Y"/>
                    </div>
                </div>

                <div class="buttons-container">
                    {include file="buttons/save_cancel.tpl" but_text=__("apply") but_name="dispatch[products.apply_global_option]" cancel_action="close"}
                </div>

            </form>
        {/capture}
        <div class="pull-right shift-left">
            {include file="common/popupbox.tpl" id="add_global_option" text=__("add_global_option") content=$smarty.capture.add_global_option link_text=__("add_global_option") act="general" icon="icon-plus"}
        </div>
    {/if}

    <div class="pull-left">
        {if $product_options}
            {if !"ULTIMATE:FREE"|fn_allowed_for}
                {if $product_data.exceptions_type == "F"}
                    {assign var="except_title" value=__("forbidden_combinations")}
                {else}
                    {assign var="except_title" value=__("allowed_combinations")}
                {/if}
                {include file="buttons/button.tpl" but_text=$except_title but_href="product_options.exceptions?product_id=`$product_data.product_id`" but_meta="btn" but_role="text"}
            {else}
                {if $product_data.exceptions_type == "F"}
                    {assign var="except_title" value=__("forbidden_combinations")}
                {else}
                    {assign var="except_title" value=__("allowed_combinations")}
                {/if}
                {include file="buttons/button.tpl" but_text=$except_title but_meta="btn cm-promo-popup" but_role="text"}
            {/if}

            {if $has_inventory}
                {include file="buttons/button.tpl" but_text=__("option_combinations") but_href="product_options.inventory?product_id=`$product_data.product_id`" but_meta="btn"  but_role="text"}
            {else}
                {capture name="notes_picker"}
                    {__("text_options_no_inventory")}
                {/capture}
                {include file="common/popupbox.tpl" act="button" id="content_option_combinations" text=__("note") content=$smarty.capture.notes_picker link_text=__("option_combinations") but_href="product_options.inventory?product_id=`$product_data.product_id`" but_role="text" extra_act="notes"}
            {/if}
        {/if}
    </div>
{/capture}

{include file="views/product_options/manage.tpl" object="product" extra=$smarty.capture.extra product_id=$smarty.request.product_id view_mode="embed"}