<p>{if $product.age_warning_message}{$product.age_warning_message}{else}{$age_warning_message}{/if}</p>

{capture name="mainbox_title"}{$product.product nofilter}{/capture}