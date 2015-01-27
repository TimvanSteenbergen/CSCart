{strip}
    {if $product.points_info.reward}
        ({if $mod_value > 0}+{else}-{/if}{$mod_value|abs}{if $mod_type != "A"}%{/if}&nbsp;{__("points_lower")})
    {/if}
{/strip}