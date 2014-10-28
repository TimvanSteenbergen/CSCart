{math equation="rand()" assign="rnd"}
<div class="clear" id="ds_{$rnd}">
    <div class="section-border">
        {$section_content nofilter}
        {if $section_state}
            <p align="right">
                <a href="{$config.current_url|fn_link_attach:"close_section=`$key`"|fn_url}" class="underlined">{__("close")}</a>
            </p>
        {/if}
    </div>
</div>