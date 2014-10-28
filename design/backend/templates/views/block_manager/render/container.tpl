<div id="container_{$container.container_id}" class="container container_{$container.width} {if $container.default != 1 && $container.position != 'CONTENT' && !$dynamic_object}container-lock{/if} {if $container.status == "D"}container-off{/if} cm-sortable-container" {if $container.status != "A"}data-ca-status="disabled"{else}data-ca-status="active"{/if}>
    {if $container.default != 1 && $container.position == 'TOP_PANEL' &&    !$dynamic_object}<p>{__("top_container_not_used")}</p>{/if}
    {if $container.default != 1 && $container.position == 'HEADER' &&    !$dynamic_object}<p>{__("header_container_not_used")}</p>{/if}
    {if $container.default != 1 && $container.position == 'FOOTER' &&  !$dynamic_object}<p>{__("footer_container_not_used")}</p>{/if}

    {if $container.default == 1 || $container.position == 'CONTENT' || $dynamic_object}
        {$content nofilter}
    {/if}
    
    <div class="clearfix"></div>
    <div class="grid-control-menu bm-control-menu">
        {if $container.default == 1 || $container.position == 'CONTENT' && !$dynamic_object}
            <div class="grid-control-menu-actions">
                <div class="btn-group action">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="icon-plus cm-tooltip" data-ce-tooltip-position="top" title="{__("insert_grid")}"></span></a>
                    <ul class="dropdown-menu droptop">
                        <li><a href="#" class="cm-action bm-action-add-grid">{__("insert_grid")}</a></li>
                    </ul>
                </div>
                <div class="cm-tooltip cm-action exicon-cog bm-action-properties action" data-ce-tooltip-position="top" title="{__("container_options")}"></div>
                <div class="cm-action bm-action-switch cm-tooltip exicon-off action" data-ce-tooltip-position="top" title="{__("enable_or_disable_container")}"></div>
            </div>
        {/if}

        <h4 class="grid-control-title">{__($container.position)}</h4>
    </div>
<!--container_{$container.container_id}--></div>

<hr />