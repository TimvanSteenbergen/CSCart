<div class="sidebar-row">
    <h6>{__("search")}</h6>

    <form action="{""|fn_url}" name="addons_search_form" method="get" class="{$form_meta} addons-search-form">
        {$extra nofilter}
        
        <div class="sidebar-field ">
            <label for="elm_addon">{__("name")}</label>
            <input type="text" name="q" id="elm_addon" value="{$search.q}" size="30" />
            <i class="icon icon-remove hidden" id="elm_addon_clear" title="{__("remove")}"></i>
        </div>
        
        <div class="sidebar-field">
            <input class="btn" type="submit" name="dispatch[{$dispatch}]" value="{__("search")}">
        </div>
    </form>
</div>