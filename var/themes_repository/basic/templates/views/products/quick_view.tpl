<div class="quick-view-wrap">
{capture name="val_hide_form"}{/capture}
{capture name="val_capture_options_vs_qty"}{/capture}
{capture name="val_capture_buttons"}{/capture}
{capture name="val_no_ajax"}{/capture}

{hook name="products:layout_content"}

{if $settings.Appearance.default_product_details_view == "default_template"}
{include file="views/products/components/quick_view_template.tpl" 

product=$product
show_sku=true
show_rating=true
show_old_price=true
show_price=true
show_list_discount=true 
show_clean_price=true
details_page=true
show_discount_label=true
show_product_amount=true
show_product_options=true
hide_form=$smarty.capture.val_hide_form
min_qty=true
show_edp=true
show_add_to_cart=true
show_list_buttons=true
but_role="action"
capture_buttons=$smarty.capture.val_capture_buttons
capture_options_vs_qty=$smarty.capture.val_capture_options_vs_qty
separate_buttons=true
show_list_buttons=true
but_role="action"
block_width=true
no_ajax=$smarty.capture.val_no_ajax
show_descr=true
quick_view=true}

{else}

{include file="views/products/components/quick_view_template_long.tpl" 

product=$product
show_sku=true
show_rating=true
show_old_price=true
show_price=true
show_list_discount=true 
show_clean_price=true
details_page=true
show_discount_label=true
show_product_amount=true
show_product_options=true
hide_form=$smarty.capture.val_hide_form
min_qty=true
show_edp=true
show_add_to_cart=true
show_list_buttons=true
but_role="action"
capture_buttons=$smarty.capture.val_capture_buttons
capture_options_vs_qty=$smarty.capture.val_capture_options_vs_qty
separate_buttons=true
show_list_buttons=true
but_role="big"
block_width=true
no_ajax=$smarty.capture.val_no_ajax
show_descr=true
quick_view=true}

{/if}

{/hook}
</div>