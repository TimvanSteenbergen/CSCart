{hook name="suppliers:view"}

{include file="common/company_data.tpl" company=$supplier show_name=true show_descr=true show_rating=true show_logo=true hide_links=true}

<div class="company-page clearfix">

    <div id="block_company_{$supplier.supplier_id}">
        <h1 class="mainbox-title"><span>{$supplier.name}</span></h1>
        <div class="company-page-top-links clearfix">
            {hook name="suppliers:top_links"}
            <div id="company_products">
                <a href="{"products.search?supplier_id=`$supplier.supplier_id`&search_performed=Y"|fn_url}">{__("view_supplier_products")} ({$supplier.products|count} {__("items")})</a>
            </div>
            {/hook}
        </div>
        <div class="company-page-info">
            <div class="info-list">
                <h5>{__("contact_information")}</h5>
                {if $supplier.email}
                <div id="supplier_email">
                    <label>{__("email")}:</label>
                    <span><a href="mailto:{$supplier.email}">{$supplier.email}</a></span>
                </div>
                {/if}
                {if $supplier.phone}
                <div id="supplier_phone">
                    <label>{__("phone")}:</label>
                    <span>{$supplier.phone}</span>
                </div>
                {/if}
                {if $supplier.fax}
                <div id="supplier_phone">
                    <label>{__("fax")}:</label>
                    <span>{$supplier.fax}</span>
                </div>
                {/if}
                {if $supplier.url}
                <div id="supplier_website">
                    <label>{__("website")}:</label>
                    <span><a href="{$supplier.url}">{$supplier.url}</a></span>
                </div>
                {/if}
            </div>
            <div class="info-list">
                <h5>{__("shipping_address")}</h5>
                <div>
                    <span>{$supplier.address}</span>
                </div>
                <div>
                    <span>{$supplier.city}, {$supplier.state|fn_get_state_name:$supplier.country} {$supplier.zipcode}</span>
                </div>
                <div>
                    <span>{$supplier.country|fn_get_country_name}</span>
                </div>
            </div>
        </div>

    </div>

</div>

{capture name="tabsbox"}

{hook name="suppliers:tabs"}
{/hook}    

{/capture}
{include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox active_tab=$smarty.request.selected_section}

{/hook}