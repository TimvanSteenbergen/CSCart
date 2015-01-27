{if $products}
    <li>{btn type="list" class="cm-process-items cm-ajax cm-comet" text=__("export_to_ebay") dispatch="dispatch[ebay.export]" form="manage_products_form"}</li>
{/if}