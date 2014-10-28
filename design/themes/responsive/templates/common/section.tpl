{assign var="id" value=$section_title|md5|string_format:"s_%s"}
{math equation="rand()" assign="rnd"}
{if $smarty.cookies.$id || $collapse}
    {assign var="collapse" value=true}
{else}
    {assign var="collapse" value=false}
{/if}

<div class="ty-section{if $class} {$class}{/if}" id="ds_{$rnd}">
    <div  class="ty-section__title {if !$collapse}open{/if} cm-combination cm-save-state cm-ss-reverse" id="sw_{$id}">
        <span>{$section_title nofilter}</span>
        <span class="ty-section__switch ty-section_switch_on">{__("open_action")}<i class="ty-section__arrow ty-icon-down-open"></i></span>
        <span class="ty-section__switch ty-section_switch_off">{__("hide")}<i class="ty-section__arrow ty-icon-up-open"></i></span>
    </div>
    <div id="{$id}" class="{$section_body_class|default:"ty-section__body"} {if $collapse}hidden{/if}">{$section_content nofilter}</div>
</div>