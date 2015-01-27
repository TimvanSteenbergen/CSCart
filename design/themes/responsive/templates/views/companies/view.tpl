{hook name="companies:view"}

{assign var="obj_id" value=$company_data.company_id}
{assign var="obj_id_prefix" value="`$obj_prefix``$obj_id`"}
    {include file="common/company_data.tpl" company=$company_data show_name=true show_descr=true show_rating=true show_logo=true hide_links=true}
    <div class="ty-company-detail clearfix">

        <div id="block_company_{$company_data.company_id}" class="clearfix">
            <h1 class="ty-mainbox-title">{$company_data.company}</h1>

            <div class="ty-company-detail__top-links clearfix">
                {hook name="companies:top_links"}
                    <div class="ty-company-detail__view-products" id="company_products">
                        <a href="{"products.search?company_id=`$company_data.company_id`&search_performed=Y"|fn_url}">{__("view_vendor_products")}
                            ({$company_data.total_products} {__("items")})</a>
                    </div>
                {/hook}
            </div>
            <div class="ty-company-detail__info">
                <div class="ty-company-detail__logo">
                    {assign var="capture_name" value="logo_`$obj_id`"}
                    {$smarty.capture.$capture_name nofilter}
                </div>
                <div class="ty-company-detail__info-list ty-company-detail_info-first">
                    <h5 class="ty-company-detail__info-title">{__("contact_information")}</h5>
                    {if $company_data.email}
                        <div class="ty-company-detail__control-group">
                            <label class="ty-company-detail__control-lable">{__("email")}:</label>
                            <span><a href="mailto:{$company_data.email}">{$company_data.email}</a></span>
                        </div>
                    {/if}
                    {if $company_data.phone}
                        <div class="ty-company-detail__control-group">
                            <label class="ty-company-detail__control-lable">{__("phone")}:</label>
                            <span>{$company_data.phone}</span>
                        </div>
                    {/if}
                    {if $company_data.fax}
                        <div class="ty-company-detail__control-group">
                            <label class="ty-company-detail__control-lable">{__("fax")}:</label>
                            <span>{$company_data.fax}</span>
                        </div>
                    {/if}
                    {if $company_data.url}
                        <div class="ty-company-detail__control-group">
                            <label class="ty-company-detail__control-lable">{__("website")}:</label>
                            <span><a href="{$company_data.url}">{$company_data.url}</a></span>
                        </div>
                    {/if}
                </div>
                <div class="ty-company-detail__info-list">
                    <h5 class="ty-company-detail__info-title">{__("shipping_address")}</h5>

                    <div class="ty-company-detail__control-group">
                        <span>{$company_data.address}</span>
                    </div>
                    <div class="ty-company-detail__control-group">
                        <span>{$company_data.city}
                            , {$company_data.state|fn_get_state_name:$company_data.country} {$company_data.zipcode}</span>
                    </div>
                    <div class="ty-company-detail__control-group">
                        <span>{$company_data.country|fn_get_country_name}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="ty-company-detail__categories">
            <h3 class="ty-subheader">{__("categories")}</h3>

            <table class="ty-company-detail__table ty-table">
                <thead>
                    <tr>
                        <th>{__("name")}</th>
                        <th>{__("products_amount")}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$company_categories item="category"}
                        <tr>
                            <td>
                                <a href="{"products.search?search_performed=Y&cid=`$category.category_id`&company_id=`$company_data.company_id`"|fn_url}">{$category.category}</a>
                            </td>
                            <td style="width: 10%" class="ty-right">{$category.count}</td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>

        {capture name="tabsbox"}
            <div id="content_description"
                 class="{if $selected_section && $selected_section != "description"}hidden{/if}">
                {if $company_data.company_description}
                    <div class="ty-wysiwyg-content">
                        {$company_data.company_description nofilter}
                    </div>
                {/if}
            </div>
            {hook name="companies:tabs"}
            {/hook}

        {/capture}
    </div>
    {include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox active_tab=$smarty.request.selected_section}

{/hook}