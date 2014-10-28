<div class="tools-container">
    {if $label}<label>{$label}</label>{/if}

    <a class="select-link vendor">
        <span id="sw_{$id}_wrap_" class="select-vendor cm-combination">{$text}</span>
        <i class="icon-down-micro"></i>
    </a>

    <div id="{$id}_wrap_" class="popup-tools cm-popup-box cm-smart-position hidden">
        <input type="text" value="{__("search")}..." class="input-text cm-hint cm-ajax-content-input" data-ca-target-id="content_loader_{$id}" size="16" />
        <div class="ajax-popup-tools" id="scroller_{$id}">
            <ul class="cm-select-list select-list" id="{$id}">
                <li class="hidden">&nbsp;</li><!-- hidden li element for successfully html validation -->
                {foreach from=$objects key="object_id" item="item"}
                    <li><a data-ca-action="{$item.value}">{$item.name}</a></li>
                {/foreach}
            <!--{$id}--></ul>
            <ul>
                <li id="content_loader_{$id}" class="cm-ajax-content-more small-description" data-ca-target-url="{$data_url|fn_url}" data-ca-target-id="{$id}" data-ca-result-id="{$result_elm}">{__("loading")}</li>
            </ul>
        </div>
    </div>
</div>