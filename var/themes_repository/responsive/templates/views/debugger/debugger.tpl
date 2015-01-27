{script src="js/lib/highlight/highlight.pack.js"}

{literal}
<script type="text/javascript">

(function($){

var codeArr={};

$(document).ready(function() {

    $(window).on('keydown', function(e) {
        codeArr[e.keyCode] = true;

        if (codeArr[17] && codeArr[18] && e.keyCode !== 17 && e.keyCode !== 18) {
            // show toolbar on ctrl+alt+d
            if (e.keyCode == 68) {
                $('.deb-content').toggle();
            }

            codeArr={};
        }
    });

    $(window).on('keyup', function(e) {
        delete(codeArr[e.keyCode]);
    });

    // show hide toolbar
    $('.deb-bug').on('click', function(){
        $('.deb-content').toggle();
        localStorage.removeItem('debugToolbarTab');
        localStorage.removeItem('debugToolbarTabContent');
        if($('.deb-content').is(':visible')){
            localStorage.setItem('debugToolbarTab', true);
        }       
    });

    $('.deb-menu li').on('click', function(e){
        var tab = $(this).find('a').data('tab-content-id');
        localStorage.setItem('debugToolbarTabContent', tab);

        $('.deb-menu li').removeClass('active');
        $(this).addClass('active');

        if($(tab).is(':visible')) {
            $(tab).css('display','none');
            $(this).removeClass('active');
            localStorage.removeItem('debugToolbarTabContent');
        } else {
            $('.deb-tab').hide();
            $(tab).css('display','block');
        }
        
    });

    // show if opened
    if(localStorage.getItem("debugToolbarTabContent") !== null){
        var viewTab = localStorage.getItem("debugToolbarTabContent");
        $('.deb-content').show();
        $('.deb-menu li a[data-tab-content-id="'+viewTab+'"]').click();
    }

    if(localStorage.getItem("debugToolbarTab") !== null){
        $('.deb-content').show();
    }

    $('.deb-close').on('click', function(){
        $('.deb-tab').hide();
        localStorage.removeItem('debugToolbarTabContent');
        $('.deb-menu li').removeClass('active');
    });


    // after ajax init
    $.ceEvent('on', 'ce.ajaxdone', function(){

        // code highlight
        $('pre code').each(function(i, e) {
            hljs.highlightBlock(e)
        });

        // template tree
        $('.tree li').each(function(){
            if($(this).children('ul').length > 0){
                $(this).addClass('parent');     
            }
        });
        $('.tree li.parent > a').click(function(){
            $(this).parent().toggleClass('active');
            $(this).parent().children('ul').slideToggle('fast');
        });

        // Sub tabs
        var defaultTab = $('.deb-sub-tab ul li.active a').data('sub-tab-id');
        $('#'+defaultTab).show();

        $('.deb-sub-tab ul li a').on('click', function(e){
            var subTab = $(this).data('sub-tab-id');
            $('.deb-sub-tab ul li').removeClass('active');
            $(this).parent().addClass('active');

            $('.deb-sub-tab-content').hide();
            $('#'+subTab).show();
        });

        // change tab on sql query click
        $('#DebugToolbarSubTabSQLListTable a').on('click', function(e){
            $('.deb-sub-tab li:last-child a').click();
        })

        // chenge value on submit
        $('#DebugToolbarSubTabSQLParseSubmit').on('click',function(){
            var value = $('#DebugToolbarSQLQueryValue').text();
            $('#DebugToolbarSQLQuery').val(value);
        })
    })
        
});

})(Tygh.$);

</script>

<style type="text/css">
        pre code {
          display: block; padding: 0.5em;
        }

        pre .comment,
        pre .annotation,
        pre .template_comment,
        pre .diff .header,
        pre .chunk,
        pre .apache .cbracket {
          color: rgb(0, 128, 0);
        }

        pre .keyword,
        pre .id,
        pre .built_in,
        pre .smalltalk .class,
        pre .winutils,
        pre .bash .variable,
        pre .tex .command,
        pre .request,
        pre .status,
        pre .nginx .title,
        pre .xml .tag,
        pre .xml .tag .value {
          color: rgb(0, 0, 255);
        }

        pre .string,
        pre .title,
        pre .parent,
        pre .tag .value,
        pre .rules .value,
        pre .rules .value .number,
        pre .ruby .symbol,
        pre .ruby .symbol .string,
        pre .aggregate,
        pre .template_tag,
        pre .django .variable,
        pre .addition,
        pre .flow,
        pre .stream,
        pre .apache .tag,
        pre .date,
        pre .tex .formula {
          color: rgb(163, 21, 21);
        }

        pre .ruby .string,
        pre .decorator,
        pre .filter .argument,
        pre .localvars,
        pre .array,
        pre .attr_selector,
        pre .pseudo,
        pre .pi,
        pre .doctype,
        pre .deletion,
        pre .envvar,
        pre .shebang,
        pre .preprocessor,
        pre .userType,
        pre .apache .sqbracket,
        pre .nginx .built_in,
        pre .tex .special,
        pre .prompt {
          color: rgb(43, 145, 175);
        }

        pre .phpdoc,
        pre .javadoc,
        pre .xmlDocTag {
          color: rgb(128, 128, 128);
        }

        pre .vhdl .typename { font-weight: bold; }
        pre .vhdl .string { color: #666666; }
        pre .vhdl .literal { color: rgb(163, 21, 21); }
        pre .vhdl .attribute { color: #00B0E8; }

        pre .xml .attribute { color: rgb(255, 0, 0); }


        #DebugToolbar {
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        }
        #DebugToolbar pre, code {
            padding: 0px !important;
            font-size: 14px !important;
            margin: 0px !important;
            background: white;
            border: 0px !important;
            border-radius: 0px !important;
            font-family: monospace !important;
            white-space: pre-wrap;
            line-height: 18px;
        }
        #DebugToolbar ul, #DebugToolbar li {
            padding: 0px;
            margin: 0px;
        }
        #DebugToolbar a {
            text-decoration: none;
            color: 
        }
        #DebugToolbar .deb-bug {
            width: 32px;
            height: 32px;
            position: fixed;
            top: 24px;
            right: 22px;
            z-index: 99999;
            cursor: pointer;
        }
        #DebugToolbar .deb-x {
            color: white;
            position: absolute;
            top: 58px;
            left: -24px;
            display: block;
            padding: 5px 7px;
            height: 18px;
            -webkit-border-radius: 4px 0 0 4px;
            -moz-border-radius: 4px 0 0 4px;
            border-radius: 4px 0 0 4px;
            background: #4d4d4d;
            text-decoration: none;
            visibility: hidden;
            opacity: 0;
        }
        #DebugToolbar .deb-logo {
            width: 86px;
            height: 19px;
            display: block;
            position: absolute;
            top: 25px;
            right: 94px;
            z-index: 99999;
        }
        #DebugToolbar .deb-content {
            display: none;
        }
        #DebugToolbar .deb-panel {
            background: #111111;
            width: 200px;
            position: fixed;
            right: 0px;
            top: 0px;
            bottom: 0px;
            z-index: 99998;
            color: white;
            min-height: 630px;
        }
        #DebugToolbar .deb-panel:hover .deb-x {
            visibility: visible;
            opacity: 1;
            -webkit-transition: all 0.2s ease;
            -moz-transition: all 0.2s ease;
            -ms-transition: all 0.2s ease;
            -o-transition: all 0.2s ease;
            transition: all 0.2s ease;
        }
        #DebugToolbar .deb-panel .deb-menu {
            margin-top: 85px;
            border-top: 1px solid #464545;
        }
        #DebugToolbar .deb-panel .deb-menu .active a {
            background: #4b4b4b;
        }
        #DebugToolbar .deb-panel .deb-menu li {
            list-style-type: none;
        }
        #DebugToolbar .deb-panel .deb-menu li a {
            color: white;
            display: block;
            font-size: 16px;
            padding: 15px 20px;
        }
        #DebugToolbar .deb-panel .deb-menu li a:hover {
            background: #323232;
        }
        #DebugToolbar .deb-panel ul li a small {
            display: block;
            font-size: 11px;
            color: #999999;
        }
        #DebugToolbar .deb-panel .deb-down-wrap {
            position: absolute;
            right: 0px;
            bottom: 20px;
        }
        #DebugToolbar .deb-panel .deb-down-content {
            padding: 0px 16px;
            margin-bottom: 15px;
        }
        #DebugToolbar .deb-panel .deb-down-help-text {
            color: #999;
            font-size: 12px;
            line-height: 16px;
        }
        #DebugToolbar .deb-panel .deb-resource-usage {
            border-top: 1px solid #464545;
            font-size: 12px;
            padding: 10px 15px 0px 15px;
            width: 170px;
        }
        #DebugToolbar .deb-panel .deb-resource-usage li {
            list-style-type: none;
            padding-bottom: 2px;
            color: #999999;
        }
        #DebugToolbar .deb-panel .deb-resource-usage li small {
            color: white;
        }
        #DebugToolbar .deb-panel .deb-resource-usage .deb-title {
            font-size: 16px;
            padding-bottom: 20px;
            color: white;
        }
        #DebugToolbar .deb-tab {
            z-index: 99997;
            background-color: #eeeeee;
            position: fixed;
            top: 0px;
            bottom: 0px;
            left: 0px;
            right: 200px;
            padding: 0px;
            display: none;
            overflow: auto;
        }
        #DebugToolbar .deb-tab-title {
            background-color: #ffffcc;
            padding: 10px 20px;
            position: relative;
            box-shadow: 0px 0px 10px #797979;
            z-index: 20;
        }
        #DebugToolbar .deb-tab-title h1 {
            font-size: 22px;
            padding: 0px;
            margin: 0px;
            line-height: 22px;
        }
        #DebugToolbar .deb-tab-content {
            padding: 25px 20px;
        }
        #DebugToolbar .deb-sub-tab {
            margin-bottom: 20px;
            border-bottom: 1px solid #dddddd;
        }
        #DebugToolbar .deb-sub-tab-content {
            display: none;
        }
        #DebugToolbar .deb-sub-tab > ul li {
            display: inline-block;
            margin-bottom: -1px;
        }
        #DebugToolbar .deb-sub-tab > ul li.active a {
            background: #dddddd !important;
            color: #333333;
            border-bottom: 1px solid #aeaeae !important;
        }
        #DebugToolbar .deb-sub-tab > ul li:hover a {
            background: #e5e5e5;
            border-bottom: 1px solid #c5c5c5;
        }
        #DebugToolbar .deb-sub-tab > ul li a {
            display: block;
            padding: 8px 15px;
            border-bottom: 1px solid #dddddd;
            -webkit-border-top-left-radius: 2px;
            -webkit-border-top-right-radius: 2px;
            -moz-border-radius-topleft: 2px;
            -moz-border-radius-topright: 2px;
            border-top-left-radius: 2px;
            border-top-right-radius: 2px;
        }
        #DebugToolbar .deb-close {
            font-size: 20px;
            position: absolute;
            top: 11px;
            right: 25px;
            color: black;
        }
        #DebugToolbar .deb-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        #DebugToolbar .deb-table caption {
            text-align: left;
            font-size: 18px;
            padding-bottom: 15px;
        }
        #DebugToolbar .deb-table th, #DebugToolbar .deb-table td {
            margin: 0px;
            padding: 0px;
            outline: 0px;
            text-align: left;
            border: 1px solid #cccccc;
            padding: 7px 10px;
            color: #424242;
            font-size: 13px;
            background-color: #ffffff;
        }
        #DebugToolbar .deb-table tr:hover td {
            background-color: #ffffeb;
        }
        #DebugToolbar .deb-table tr:hover td code, #DebugToolbar .deb-table tr:hover td pre {
            background: transparent;
        }
        #DebugToolbar .deb-table td a {
            color: #333333;
        }
        #DebugToolbar .deb-table th {
            background-color: #f5f5f5;
        }
        #DebugToolbar .deb-font-gray {
            color: gray;
        }
        #DebugToolbar .deb-table .deb-light-red, #DebugToolbar .deb-table .deb-light-red pre, #DebugToolbar .deb-table .deb-light-red pre code {
            background-color : #ffc8c8;
        }
        #DebugToolbar .deb-table .deb-light2-red, #DebugToolbar .deb-table .deb-light2-red pre, #DebugToolbar .deb-table .deb-light-red pre code {
            background-color: #fcdede;
        }
        #DebugToolbar .deb-table .deb-light3-red {
            background-color: #ffeeee;
        }
        #DebugToolbar .deb-variables {
            height: 100%;
            background: #333333;
            position: fixed;
            padding: 0px 0px 4px 0px;
            right: 217px;
            top: 0px;
        }
        #DebugToolbar .deb-variables a {
            display: block;
            padding: 2px 18px;
            color: #999999;
        }
        #DebugToolbar .deb-variables h4 {
            color: white;
            padding: 5px 18px;
        }
        #DebugToolbar .deb-variables a:hover {
            color: #999999;
            background: #4b4b4b;
        }
        #DebugToolbar #DebugToolbarTabTemplates .deb-table {
            width: 88%;
        }
        #DebugToolbar .tree {
            border-color: #BFC0C2 #BFC0C2 #B6B7BA;
            border-style: solid;
            border-width: 1px;
            display: inline-block;
            margin: 0 0 20px;
            width: 88%;
            background-color: white;
        }
        #DebugToolbar .tree ul {
            list-style: none outside none;
        }
        #DebugToolbar .tree ul li {
            padding: 4px 10px;
        }
        #DebugToolbar .tree ul > li:hover {
            background-color: #f7f7f7;
        }
        #DebugToolbar .tree li a {
            line-height: 25px;
        }
        #DebugToolbar .tree > ul > li  a {
            color: #3B4C56;
        }
        #DebugToolbar .tree > ul > li > a {
            display: block;
            font-weight: normal;
            position: relative;
            text-decoration: none;
        }
        #DebugToolbar .tree li.parent > a {
            padding: 0 0 0 17px;
            font-weight: bold;
        }
        #DebugToolbar .tree li.parent > a:before {
            background-image: url("design/backend/media/images/debugger/plus_minus_icons.png");
            background-position: 14px center;
            content: "";
            display: block;
            height: 21px;
            left: 0;
            position: absolute;
            top: 2px;
            vertical-align: middle;
            width: 14px;
        }
        #DebugToolbar .tree ul li.active > a:before {
            background-position: 0 center;
        }
        #DebugToolbar .tree ul li ul {
            display: none;
            margin: 0 0 0 12px;
            overflow: hidden;
            padding: 0 0 0 25px;
        }
        #DebugToolbar .tree ul li ul li {
            position: relative;
        }
        #DebugToolbar .tree ul li ul li:before {
            content: "";
            left: -20px;
            position: absolute;
            top: 17px;
            width: 15px;
        }
        #DebugToolbar h1 {
            font-size: 18px;
        }
        #DebugToolbar textarea {
            width: 99%;
        }
        #DebugToolbar [type="checkbox"] {
            margin: 0px;
        }
        #DebugToolbar #DebugToolbarSubTabSQLList {
            display: block;
        }

    </style>
{/literal}
<div id="DebugToolbar">
    <img src="{$images_dir}/debugger/bug.png" class="deb-bug" />
    <div class="deb-content">
    <div class="deb-panel">
        <a href="#" class="deb-logo"><img src="{$images_dir}/debugger/logo.png"></a>
        {if $smarty.const.DEBUG_MODE !== true}
            {$current_url = $config.current_url|fn_query_remove:$config.debugger_token}
            {$current_url = $current_url|escape:url}
            <a href="{"debugger.quit?redirect_url=`$current_url`"|fn_url}" id="DebugToolbarQuit" class="deb-x">&#10006;</a>
        {/if}
        <ul class="deb-menu">
            <li><a class="cm-ajax cm-ajax-cache" href="{"debugger.server?debugger_hash=`$debugger_hash`"|fn_url}" data-ca-target-id="DebugToolbarTabServerContent" data-tab-content-id="#DebugToolbarTabServer">Server<small>{$smarty.const.PRODUCT_NAME}: version <b>{$smarty.const.PRODUCT_VERSION} {$smarty.const.PRODUCT_EDITION} {if $smarty.const.PRODUCT_STATUS != ''} ({$smarty.const.PRODUCT_STATUS}){/if} {if $smarty.const.PRODUCT_BUILD != ''} {$smarty.const.PRODUCT_BUILD}{/if}</b></small></a></li>
            <li><a class="cm-ajax cm-ajax-cache" href="{"debugger.request?debugger_hash=`$debugger_hash`"|fn_url}" data-ca-target-id="DebugToolbarTabRequestContent" data-tab-content-id="#DebugToolbarTabRequest">Request</a></li>
            <li><a class="cm-ajax cm-ajax-cache" href="{"debugger.config?debugger_hash=`$debugger_hash`"|fn_url}" data-ca-target-id="DebugToolbarTabConfigContent" data-tab-content-id="#DebugToolbarTabConfig">Config</a></li>
            <li><a class="cm-ajax cm-ajax-cache" href="{"debugger.sql?debugger_hash=`$debugger_hash`"|fn_url}" data-ca-target-id="DebugToolbarTabSQLContent" data-tab-content-id="#DebugToolbarTabSQL">SQL<small>{$totals.count_queries} queries {$totals.time_queries|number_format:"4"} s</small></a></li>
            <li><a class="cm-ajax cm-ajax-cache" href="{"debugger.cache_queries?debugger_hash=`$debugger_hash`"|fn_url}" data-ca-target-id="DebugToolbarTabCacheQueriesContent" data-tab-content-id="#DebugToolbarTabCacheQueries">Cache queries<small>{$totals.count_cache_queries} queries {$totals.time_cache_queries|number_format:"4"} s</small></a></li>
            <li><a class="cm-ajax cm-ajax-cache" href="{"debugger.logging?debugger_hash=`$debugger_hash`"|fn_url}" data-ca-target-id="DebugToolbarTabLoggingContent" data-tab-content-id="#DebugToolbarTabLogging">Logging</a></li>
            <li><a class="cm-ajax cm-ajax-cache" href="{"debugger.templates?debugger_hash=`$debugger_hash`"|fn_url}" data-ca-target-id="DebugToolbarTabTemplatesContent" data-tab-content-id="#DebugToolbarTabTemplates" >Templates<small>{$totals.count_tpls} included templates</small></a></li>
        </ul>
        <div class="deb-down-wrap">
        <div class="deb-down-content">
            <p class="deb-down-help-text">
            Ctrl+Alt+D - show/hide toolbar
        </p>
        </div>
        <ul class="deb-resource-usage">
            <li>Page generating time <small>{$totals.time_page|number_format:"4"} s</small></li>
            <li>Memory usage <small>{$totals.memory_page|number_format:"2":".":" "} KB</small></li>
        </ul>
        </div>
    </div>
    <!--Sever tab-->
    <div class="deb-tab" id="DebugToolbarTabServer">
        <div class="deb-tab-title">
            <h1>Server</h1>
            <a href="#" class="deb-close">&#10006;</a>
        </div>
        <div class="deb-tab-content" id="DebugToolbarTabServerContent">
        </div>
    </div>

    <!--Request tab-->
    <div class="deb-tab" id="DebugToolbarTabRequest">
        <div class="deb-tab-title">
            <h1>Request</h1>
            <a href="#" class="deb-close">&#10006;</a>
        </div>
        <div class="deb-tab-content" id="DebugToolbarTabRequestContent">
        </div>
    </div>

    <!--Config tab-->
    <div class="deb-tab" id="DebugToolbarTabConfig">
        <div class="deb-tab-title">
            <h1>Config</h1>
            <a href="#" class="deb-close">&#10006;</a>
        </div>
        <div class="deb-tab-content" id="DebugToolbarTabConfigContent">
        </div>
    </div>

    <!--SQL tab-->
    <div class="deb-tab" id="DebugToolbarTabSQL">
        <div class="deb-tab-title">
            <h1>SQL</h1>
            <a href="#" class="deb-close">&#10006;</a>
        </div>
        <div class="deb-tab-content" id="DebugToolbarTabSQLContent">
        </div>
    </div>

    <!--Cache queries tab-->
    <div class="deb-tab" id="DebugToolbarTabCacheQueries">
        <div class="deb-tab-title">
            <h1>Cache queries</h1>
            <a href="#" class="deb-close">&#10006;</a>
        </div>
        <div class="deb-tab-content" id="DebugToolbarTabCacheQueriesContent">
        </div>
    </div>    

    <!--Logging tab-->
    <div class="deb-tab" id="DebugToolbarTabLogging">
        <div class="deb-tab-title">
            <h1>Logging</h1>
            <a href="#" class="deb-close">&#10006;</a>
        </div>
        <div class="deb-tab-content" id="DebugToolbarTabLoggingContent">
        </div>
    </div>

    <!--Templates tab-->
    <div class="deb-tab" id="DebugToolbarTabTemplates">
        <div class="deb-tab-title">
            <h1>Templates</h1>
            <a href="#" class="deb-close">&#10006;</a>
        </div>
        <div class="deb-tab-content" id="DebugToolbarTabTemplatesContent">
        </div>
    </div>
    </div>
</div>