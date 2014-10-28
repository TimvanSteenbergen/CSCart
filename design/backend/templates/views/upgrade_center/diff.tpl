{capture name="mainbox"}

<pre class="diff-container">{$diff nofilter}</pre>

{/capture}
{include file="common/mainbox.tpl" title="{__("diff")}: `$smarty.request.file`" content=$smarty.capture.mainbox}