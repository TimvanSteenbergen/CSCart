<div class="ty-product-coming-soon">
    {assign var="date" value=$avail_date|date_format:$settings.Appearance.date_format}
    {if $add_to_cart == "N"}{__("product_coming_soon", ["[avail_date]" => $date])}{else}{__("product_coming_soon_add", ["[avail_date]" => $date])}{/if}
</div>