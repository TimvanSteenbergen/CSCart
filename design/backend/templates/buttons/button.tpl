{if $but_role == "text"}
    {assign var="class" value=""}
    {else}
    {assign var="class" value="btn"}
{/if}

{if $but_name}{assign var="r" value=$but_name}{else}{assign var="r" value=$but_href}{/if}
{assign var="method" value=$method|default:"POST"}
{if $r|fn_check_view_permissions:$method}

{if $but_group}<div class="btn-group">{/if}

{if $but_role == "submit" || $but_role == "button_main" || $but_type || $but_role == "big"} {* submit button *}
    <input {if $but_id}id="{$but_id}"{/if} class="btn {if $but_meta}{$but_meta}{else} btn-primary{/if}" type="{$but_type|default:"submit"}"{if $but_name} name="{$but_name}"{/if}{if $but_onclick} onclick="{$but_onclick};{if !$allow_href} return false;{/if}"{/if} value="{$but_text}" {if $tabindex}tabindex="{$tabindex}"{/if} {if $but_external_click_id} data-ca-external-click-id="{$but_external_click_id}"{/if}{if $but_target_form} data-ca-target-form="{$but_target_form}"{/if}{if $but_target_id} data-ca-target-id="{$but_target_id}"{/if} {if $but_check_filter} data-ca-check-filter="{$but_check_filter}"{/if} {if $but_disabled}disabled="disabled"{/if}  />

{elseif $but_role && $but_role != "submit" && $but_role != "action" && $but_role != "submit-link" && $but_role != "advanced-search" && $but_role != "button"} {* TEXT STYLE *}
    <a {if $but_id}id="{$but_id}"{/if}{if $but_href} href="{$but_href|fn_url}"{/if}{if $but_onclick} onclick="{$but_onclick};{if !$allow_href} return false;{/if}"{/if}{if $but_target} target="{$but_target}"{/if}{if $but_external_click_id} data-ca-external-click-id="{$but_external_click_id}"{/if}{if $but_target_form} data-ca-target-form="{$but_target_form}"{/if}{if $but_target_id} data-ca-target-id="{$but_target_id}"{/if} class="{if $but_meta} {$but_meta}{/if}"{if $title} title="{$title}"{/if}>{if $but_icon}<i class="{$but_icon}"></i>{/if} {$but_text nofilter}</a>

{elseif $but_role == "action" || $but_role == "advanced-search" || $but_role == "submit-link"} {* BUTTON STYLE *}
    <a {if $but_id}id="{$but_id}"{/if}{if $but_href} href="{$but_href|fn_url}"{/if} {if $but_onclick}onclick="{$but_onclick};{if !$allow_href} return false;{/if}"{/if} {if $but_target}target="{$but_target}"{/if} {if $but_name} data-ca-dispatch="{$but_name}"{/if} {if $but_external_click_id} data-ca-external-click-id="{$but_external_click_id}"{/if}{if $but_target_form} data-ca-target-form="{$but_target_form}"{/if}{if $but_target_id} data-ca-target-id="{$but_target_id}"{/if} class="btn{if $but_role == "submit-link"} btn-primary cm-submit{/if}{if $but_meta} {$but_meta}{/if}">{if $but_icon}<i class="{$but_icon}"></i>{/if} {$but_text nofilter}</a>
    
{elseif $but_role == "button"}
    <input {if $but_id}id="{$but_id}"{/if} {if $but_meta}class="{$but_meta}"{/if} type="button" {if $but_onclick}onclick="{$but_onclick};{if !$allow_href} return false;{/if}"{/if} value="{$but_text}" {if $tabindex}tabindex="{$tabindex}"{/if} {if $but_external_click_id} data-ca-external-click-id="{$but_external_click_id}"{/if}{if $but_target_form} data-ca-target-form="{$but_target_form}"{/if}{if $but_target_id} data-ca-target-id="{$but_target_id}"{/if} />

{elseif $but_role == "icon"} {* LINK WITH ICON *}
    <a {if $but_id}id="{$but_id}"{/if}{if $but_href} href="{$but_href|fn_url}"{/if} {if $but_onclick}onclick="{$but_onclick};{if !$allow_href} return false;{/if}"{/if} {if $but_target}target="{$but_target}"{/if} {if $but_external_click_id} data-ca-external-click-id="{$but_external_click_id}"{/if}{if $but_target_form} data-ca-target-form="{$but_target_form}"{/if}{if $but_target_id} data-ca-target-id="{$but_target_id}"{/if} class="{if $but_meta} {$but_meta}{/if}">{$but_text nofilter}</a>

{elseif !$but_role || !$but_name} {* DEFAULT INPUT BUTTON *}
    <input {if $but_id}id="{$but_id}"{/if} class="btn {if $but_meta}{$but_meta}{/if}" type="{$but_type|default:"submit"}"{if $but_name} name="{$but_name}"{/if}{if $but_onclick} onclick="{$but_onclick};{if !$allow_href} return false;{/if}"{/if} value="{$but_text}" {if $tabindex}tabindex="{$tabindex}"{/if} {if $but_external_click_id} data-ca-external-click-id="{$but_external_click_id}"{/if}{if $but_target_form} data-ca-target-form="{$but_target_form}"{/if}{if $but_target_id} data-ca-target-id="{$but_target_id}"{/if} {if $but_disabled}disabled="disabled"{/if}  />
{/if}

{if $but_group}</div>{/if}
{/if}