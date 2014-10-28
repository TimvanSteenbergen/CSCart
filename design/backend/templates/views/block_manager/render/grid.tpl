<div id="grid_{$grid.grid_id}" class="{$default_class|default:"grid"} grid_{$grid.width}{if $grid.offset} prefix_{$grid.offset}{/if}{if $grid.alpha} alpha{/if}{if $grid.omega} omega{/if}{if $grid.content_align == "RIGHT"} bm-right-align{elseif $grid.content_align == "LEFT"} bm-left-align{else} bm-full-width{/if} {if $grid.status == "D"}grid-off{/if}" {if $grid.status != "A"}data-ca-status="disabled"{else}data-ca-status="active"{/if}>
    {$content nofilter}
        <div class="bm-full-menu grid-control-menu bm-control-menu {if $grid.width <= 2}hidden{/if}">
            {if $container.default == 1 || $container.position == 'CONTENT' && !$dynamic_object || $show_menu}
                {* We need extra "hidden" div's for tooltips *}
                <div class="grid-control-menu-actions">
                    <div class="btn-group action">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class='icon-plus cm-tooltip' data-ce-tooltip-position="top" title="{__("add_grid_block")}"></span></a>
                        <ul class="dropdown-menu droptop">
                            <li><a href="" class="cm-action bm-action-add-grid">{__("insert_grid")}</a></li>
                            <li><a href="" class="cm-action bm-action-add-block">{__("insert_block")}</a></li>
                        </ul>
                    </div>
                    <div class="cm-action bm-action-properties cm-tooltip exicon-cog action" data-ce-tooltip-position="top" title="{__("grid_options")}"></div>
                    <div class="cm-action bm-action-switch cm-tooltip exicon-off action" data-ce-tooltip-position="top" title="{__("enable_or_disable_grid")}"></div>
                    <div class="cm-action bm-action-delete cm-tooltip pull-right exicon-trash extra action" data-ce-tooltip-position="top" title="{__("delete_grid")}"></div>
                </div>
            {/if}

            {if $layout_data.layout_width != "fixed"}
                {if $parent_grid.width > 0}
                    {$fluid_width = fn_get_grid_fluid_width($layout_data.width, $parent_grid.width, $grid.width)}
                {else}
                    {$fluid_width = $grid.width}
                {/if}
            {/if}
            <h4 class="grid-control-title {if $grid.width <= 2}hidden{/if}">
                {__("grid")}&nbsp;{$grid.width|default:"0"}
                {if $layout_data.layout_width != "fixed" && $fluid_width > 0}
                    <small class="muted">(span {$fluid_width})</small>
                {/if}
            </h4>
        </div>
        {if $container.default == 1 || $container.position == 'CONTENT' && !$dynamic_object || $show_menu}
        <div class="bm-compact-menu {if $grid.width > 2}hidden{/if} grid-control-menu bm-control-menu">
            <div class="action-showmenu">
                <div class="btn-group action">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class='icon-align-justify cm-tooltip' title="{__("add_grid_block")}"></span></a>
                        <ul class="dropdown-menu droptop">
                            <li><a class="cm-action bm-action-add-grid hand">{__("insert_grid")}</a></li>
                            <li><a class="cm-action bm-action-add-block hand">{__("insert_block")}</a></li>
                            <li><a class="cm-action bm-action-properties hand">{__("grid_options")}</a></li>
                            <li><a class="cm-action bm-action-delete hand">{__("delete_grid")}</a></li>
                            <li><a class="cm-action bm-action-switch hand">{__("on_off")}</a></li>
                        </ul>
                </div>

            </div>
        </div>
        {/if}
<!--grid_{$grid.grid_id}--></div>

{if $grid.clear}
    <div class="clearfix"></div>
{/if}
