{capture name="ajax_select_content"}

<a class="{if $type != 'list'}btn-text{/if} dropdown-toggle" data-toggle="dropdown">
    <span id="sw_{$id}_wrap_">{$text|truncate:40:"...":true}</span>
    <b class="caret"></b>
</a>

{if $label}<label>{$label}</label>{/if}

{if $js_action}
<script type="text/javascript">
(function(_, $) {
    $.ceEvent('on', 'ce.picker_js_action_{$id}', function() {
        {$js_action nofilter}
    });
}(Tygh, Tygh.$));
</script>
{/if}

<ul class="dropdown-menu {if $type == "opened"}dropdown-opened{/if}" id="{$id}_ajax_select_object">
    <li>
        <div id="{$id}_wrap_" class="search-shop cm-smart-position">
            <input type="text" value="{__("search")}..." class="span3 input-text cm-hint cm-ajax-content-input" data-ca-target-id="content_loader_{$id}" size="16">
        </div>
    </li>
    <li>
        <div class="ajax-popup-tools" id="scroller_{$id}">
            <ul class="cm-select-list" id="{$id}">
            {foreach from=$objects key="object_id" item="item"}
                {if $runtime.customization_mode.live_editor}
                    {assign var="name" value=$item.name}
                {else}
                    {assign var="name" value=$item.name|truncate:40:"...":true}
                {/if}
                <li><a data-ca-action="{$item.value}" title="{$item.name}">{$name}</a></li>
            {/foreach}
            <!--{$id}--></ul>
            <ul>
                <li id="content_loader_{$id}" class="cm-ajax-content-more" data-ca-target-url="{$data_url|fn_url}" data-ca-target-id="{$id}" data-ca-result-id="{$result_elm}">{__("loading")}</li>
            </ul>
        </div>
    </li>
    {$extra_content nofilter}
</ul>
{/capture}

{if $type == 'list'}
    <li class="dropdown vendor-submenu">{$smarty.capture.ajax_select_content nofilter}</li>
{else}
    <div class="btn-group">{$smarty.capture.ajax_select_content nofilter}</div>
{/if}