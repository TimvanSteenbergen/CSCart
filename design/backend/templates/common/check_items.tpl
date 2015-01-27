{assign var="check_data" value=""}
{if $check_target}
    {assign var="check_data" value="data-ca-target=\"`$check_target`\""}
{/if}
{capture name="check_items_checkbox"}
{if $style == "links"}
    <a {if $check_link}href="{$check_link}"{/if} class="cm-check-items cm-on underlined" {$check_data nofilter}>{__("select_all")}</a> | <a {if $check_link}href="{$check_link}"{/if} class="cm-check-items cm-off underlined" {$check_data nofilter}>{__("unselect_all")}</a>
{else}
    <input type="checkbox" name="check_all" value="Y" title="{__("check_uncheck_all")}" class="{if $check_statuses}pull-left{/if} cm-check-items {$class}" {if $check_onclick}onclick="{$check_onclick}"{/if} {$check_data nofilter} {if $check_disabled}disabled="disabled"{/if} />
{/if}
{/capture}
{if $check_statuses}
        <div class="btn-group btn-checkbox cm-check-items">
            <a href="" data-toggle="dropdown" class="btn dropdown-toggle">
                <span class="caret pull-right"></span>
            </a>
            {$smarty.capture.check_items_checkbox nofilter}
            <ul class="dropdown-menu">
                <li><a class="cm-on" {$check_data nofilter}>{__("check_all")}</a></li>
                <li><a class="cm-off" {$check_data nofilter}>{__("check_none")}</a></li>
                {foreach $check_statuses as $status => $title}
                <li><a {$check_data nofilter} data-ca-status="{$status|lower}">{$title}</a></li>
                {/foreach}
            </ul>
        </div>
{else}
    {$smarty.capture.check_items_checkbox nofilter}
{/if}
