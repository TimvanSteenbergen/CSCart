<div class="sidebar-row">
    <h6>{__("search")}</h6>
    <form action="{""|fn_url}" name="logs_form" method="get">
    <input type="hidden" name="object" value="{$smarty.request.object}">

    {capture name="simple_search"}
    {include file="common/period_selector.tpl" period=$search.period extra="" display="form" button="false"}
    {/capture}
    
    {capture name="advanced_search"}
    
    <div class="group form-horizontal">
        <div class="control-group">
            <label class="control-label">{__("user")}:</label>
            <div class="controls">
                <input type="text" name="q_user" size="30" value="{$search.q_user}">
            </div>
        </div>
        
        <div class="control-group">
            <label class="control-label">{__("type")}/{__("action")}:</label>
            <div class="controls">
                <select id="q_type" name="q_type" onchange="fn_logs_build_options();">
                    <option value=""{if !$search.q_type} selected="selected"{/if}>{__("all")}</option>
                    {foreach from=$log_types item="o"}
                        <option value="{$o.type}"{if $search.q_type == $o.type} selected="selected"{/if}>{$o.description}</option>
                    {/foreach}
                </select>
                &nbsp;&nbsp;
                <select id="q_action" class="hidden" name="q_action">
                </select>
            </div>
        </div>
    </div>
    
    {hook name="logs:search_form"}{/hook}
    
    {/capture}
    
    {include file="common/advanced_search.tpl" advanced_search=$smarty.capture.advanced_search simple_search=$smarty.capture.simple_search dispatch="logs.manage" view_type="logs"}
    
    <script type="text/javascript">
    var types = new Array();
    {foreach from=$log_types item="o"}
    types['{$o.type}'] = new Array();
    {foreach from=$o.actions item="v" key="k"}
    types['{$o.type}']['{$k}'] = '{$v}';
    {/foreach}
    {/foreach}
    
    Tygh.tr('all', '{__("all")|escape:"javascript"}');
    
    {literal}
    function fn_logs_build_options(current_action)
    {
        var elm_t = Tygh.$('#q_type');
        var elm_a = Tygh.$('#q_action');
    
        elm_a.html('<option value="">' + Tygh.tr('all') + '</option>');
    
        for (var action in types[elm_t.val()]) {
            elm_a.append('<option value="' + action + '"' + (current_action && current_action == action ? ' selected="selected"' : '') + '>' + types[elm_t.val()][action] + '</option>');
        }
    
        Tygh.$('#q_action').toggleBy((Tygh.$('option', elm_a).length == 1));
    }
    {/literal}
    
    Tygh.$(document).ready(function() {$ldelim}
        fn_logs_build_options('{$search.q_action}');
    {$rdelim});
    </script>
</form>
</div>