<!DOCTYPE html>
<html xmlns:ng="http://angularjs.org" lang="{$smarty.const.CART_LANGUAGE|lower}">
<head>
{if $twg_settings.companyName}
    <title>{if $twg_settings.home_page_title}{$twg_settings.home_page_title} - {/if}{$twg_settings.companyName}</title>
{else}
    <title>{$twg_settings.home_page_title}</title>
{/if}
<meta name="description" content="">
<meta name="HandheldFriendly" content="True">
<meta name="MobileOptimized" content="320">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="cleartype" content="on">
<meta content="Twigmo" name="description" />
<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0">
<meta name="robots" content="noindex">

<base href="{$twg_settings.url.base}/"/>

<link rel="apple-touch-icon" href="{$urls.favicon}" />
<link rel="shortcut icon" href="{$urls.favicon}" />

{if $twg_state.theme_editor_mode}
    <meta http-equiv="cache-control" content="max-age=0" />
    <meta http-equiv="cache-control" content="no-cache" />
    <meta http-equiv="expires" content="0" />
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <meta http-equiv="pragma" content="no-cache" />
    <link rel="stylesheet" type="text/css" href="{$urls.preview_css}app.css?{$repo_revision}" data-theme="Y" />
    <link rel="stylesheet" type="text/css" href="{$urls.preview_css}custom.css?{$repo_revision}" data-theme="Y" />
{/if}

{literal}
<style>#boot_loader{position: absolute; top: 50%; left: 50%; margin: -50px 0 0 -50px;}#splash-screen{top:0px;left:0px;right:0px;bottom:0px;position:fixed;background-color:white;opacity:1; z-index:999}</style>
{/literal}

{if $addons.google_analytics.status == "A"}
    {include file=$google_template}
{/if}
</head>

<body class="device-{$twg_state.device} browser-{$twg_state.browser}">
    <div ng-include="'/core/customer/index.html'" ng-controller="AppCtrl"></div>


    <div id="splash-screen">
        <canvas id="boot_loader" width="100" height="100"></canvas>
    </div>

    <script type="text/javascript">
        //<![CDATA[
        (function() {ldelim}
            var repoRevision = '{$repo_revision}';
            var rootUrl = '{$urls.repo}';
            var requestUrl = '{$twg_settings.url.host}{$twg_settings.url.index}{$twg_settings.url.dispatch}';
            var cacheRequest = false;
            {literal}
            var filesToLoad=[{path:rootUrl+"vendor.js"+"?"+repoRevision,id:"vendor.js"},{path:rootUrl+"twigmo.js"+"?"+repoRevision,id:"twigmo.js",waitFor:"vendor.js"},{path:requestUrl+"&action=get_settings.js",dontCache:true},{path:rootUrl+"custom_js.twgjs"+"?"+repoRevision+"&as.js",id:"custom.js",waitFor:"twigmo.js"}]
            {/literal}

            {if $twg_state.theme_editor_mode}
            {literal}
            filesToLoad.push({path:rootUrl+"theme_editor.js"+"?"+repoRevision,waitFor:"twigmo.js"})
            {/literal}
            {else}
            {literal}
            filesToLoad.push({path:rootUrl+"app.css"+"?"+repoRevision,id:"app.css"});filesToLoad.push({path:rootUrl+"custom.css"+"?"+repoRevision,waitFor:"app.css"})
            {/literal}
            {/if}
            {if $twg_settings.cacheRequest}
            cacheRequest = {ldelim}{rdelim};
            {foreach from=$twg_settings.cacheRequest item=value key=key}
            cacheRequest['{$key}'] = '{$value}';
            {/foreach}
            {/if}
            {literal}

            if(cacheRequest){var url=requestUrl;for(var param in cacheRequest){url+="&"+param+"="+cacheRequest[param]}filesToLoad.push({path:url+"&get_cache.js",dontCache:true,id:"cache.js"})}var initProgressBar=function(e){var t=document.getElementById("boot_loader");var n=t.getContext("2d");var r=1;var i=0;n.strokeStyle="rgb(152,152,152)";n.fillStyle="white";n.lineWidth=40;var s=function(t){var n=t/e*2;if(t==e){n=1.4999}else if(n<.5){n+=1.5}else{n-=.5}return n*Math.PI};var o=function(){var e=.05;return{start:s(r-1)+e,end:s(r)-e}};var u=function(){var t=(new Date).getTime();if(i&&t-i<50&&r/e<.5){setTimeout(u,60);return}i=t;var s=o();n.beginPath();n.arc(50,50,20,s.start,s.end);n.stroke();n.closePath();n.beginPath();n.arc(50,50,30,0,2*Math.PI);n.fill();n.closePath();r++};u();return u};var updateProgress=initProgressBar(filesToLoad.length+2);var runApp=function(){if(typeof angular=="undefined"){window.setTimeout(runApp,100);return}
                angular.element(document).ready(function(){updateProgress();setTimeout(function(){var e=document.getElementById("splash-screen");e.parentNode.removeChild(e)},3);setTimeout(function(){angular.bootstrap(document,["app"])},1)})};var onScriptLoaded=function(e,t,n,r,i){if(n.indexOf("/app.css?")>0&&!i){r.data=r.data.replace(/url\("([^/][^/].*?)"/g,'url("'+rootUrl+'$1"')}if(n.indexOf("get_cache.js")>0){window.twgCachedData={request:cacheRequest};r.data="window.twgCachedData.data = "+r.data+";"}updateProgress();if(e==t){runApp()}}
            var BootUp=function(e,t){function v(){for(var t=0;t<e.length;t++){if(l){return}var n=e[t];a.push(n);M(n.path)}}function m(e){if(!e){return}if(e.error){p=e.error}if(e.loaded){d=e.loaded}if(e.threads){i=e.threads}if(e.debug){c=e.debug}if(e.fresh){h=e.fresh}}function g(){m(t);s=e.length;try{if(n&&localStorage.getItem("cache")){u=JSON.parse(localStorage.getItem("cache"))}}catch(r){localStorage.removeItem("cache")}v()}function y(e){for(var t=0;t<a.length;t++){if(a[t].path==e){return a[t]}}return null}function b(e){var t=y(e);return t?y(e).data:null}function w(e){if(!u){return}for(var t=0;t<u.objects.length;t++){if(u.objects[t].path===e){return u.objects[t]}}return null}function E(){var e=[];var t=false;for(var n=0;n<a.length;n++){if(a[n].isExecuted){e.push(a[n].id)}}for(var n=0;n<a.length;n++){if(!a[n].data||a[n].isExecuted){continue}if(a[n].waitFor&&e.indexOf(a[n].waitFor)==-1){continue}x(a[n]);a[n].isExecuted=true;e.push(a[n].id);t=true}
                if(t){E()}}function S(){E();if(o!==s){return}T()}function x(e){var t=e.path.indexOf(".js")===-1?false:true;var n=document.createElement(t?"script":"style");n.type="text/"+(t?"javascript":"css");if(t){n.text=e.data}else{if(n.styleSheet){n.styleSheet.cssText=e.data}else{n.appendChild(document.createTextNode(e.data))}}var r=document.head||document.getElementsByTagName("head")[0];r.appendChild(n)}function T(){if(!n){return}var e={objects:a};for(var t=0;t<e.objects.length;t++){delete e.objects[t].callback;if(e.objects[t].dontCache){e.objects.splice(t--,1)}}try{localStorage.cache=JSON.stringify(e)}catch(r){_("Couldn't cache objects this time")}}function N(e){e.cached=true;var t=y(e.path);t.data=e.data;callback=t.callback;_("from cache",e.path);o++;if(d){d.call(this,o,s,e.path,t,true)}S()}function C(e,t){var n=y(e);n.data=t.responseText;o++;r--;if(n.callback){n.callback.call(this,e)}if(d){d.call(this,o,s,e,n)}S();O()}function k(e,t){_("FAILED TO LOAD A FILE",t);
                if(p){p.call(this)}l=true}function L(){return window.XMLHttpRequest?new XMLHttpRequest:new ActiveXObject("Microsoft.XMLHTTP")}function A(e){f.push(e)}function O(){if(f.length>0){var e=f.pop();M(e)}}function M(e){if(r>=i){A(e);return}if(l){return}var t=w(e);if(t){N(t);return}r++;var n=L();n.onreadystatechange=function(){if(l){return}if(n.readyState==4&&(n.status==200||n.status==204)){C(e,n)}else if(n.readyState==4&&n.status>400&&n.status<600){k(n,e)}};n.open("GET",e,true);n.send(null)}function _(){if(c&&console){console.log.apply(console,arguments)}}var n="localStorage"in window;var r=0;var i=8;var s=0;var o=0;var u=null;var a=[];var f=[];var l=false;var c=false;var h=false;var p=null;var d=null;g();return{getFile:b}}
            new BootUp(filesToLoad, {loaded: onScriptLoaded});
            {/literal}
        })();
        //]]>
    </script>

    {if $twg_settings.geolocation == 'Y'}
        <script type="text/javascript">
            //<![CDATA[
            {literal}
            var twgMapsCallback = function() {if (typeof(twg) != 'undefined' && twg.geo && twg.func && angular.element(document).injector()) {twg.func.publish('geo:apiLoaded');}};
            {/literal}
            //]]>
        </script>
        <script async type="text/javascript" src="//maps.google.com/maps/api/js?sensor=false&?v=3.7&language={$smarty.const.CART_LANGUAGE}&callback=twgMapsCallback"></script>
    {/if}
</body>
</html>
