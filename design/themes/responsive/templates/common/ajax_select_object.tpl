<div class="select-vendor">
    {if $label}
        <label class="ty-control-group__title select-vendor__title">{$label}</label>
    {/if}

    <a class="ty-select-block__a">
        <span id="sw_{$id}_wrap_" class="ty-select-block__a-item cm-combination">{$text}</span>
        <i class="ty-select-block__arrow ty-icon-down-micro"></i>
    </a>

    <div id="{$id}_wrap_" class="ty-select-block cm-popup-box cm-smart-position hidden">
        <input type="text" value="{__("search")}..." class="ty-select-block__input cm-hint cm-ajax-content-input" data-ca-target-id="content_loader_{$id}" size="16" />
        <div id="scroller_{$id}">
            <ul class="cm-select-list ty-select-block__list" id="{$id}">
                <li class="ty-select-block__list-item hidden">&nbsp;</li><!-- hidden li element for successfully html validation -->
                {foreach from=$objects key="object_id" item="item"}
                    <li><a class="ty-select-block__list-a" data-ca-action="{$item.value}">{$item.name}</a></li>
                {/foreach}
            <!--{$id}--></ul>
            <ul>
                <li id="content_loader_{$id}" class="cm-ajax-content-more small-description" data-ca-target-url="{$data_url|fn_url}" data-ca-target-id="{$id}" data-ca-result-id="{$result_elm}">{__("loading")}</li>
            </ul>
        </div>
    </div>
</div>