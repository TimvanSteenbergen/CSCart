<label for="cb_product_type_c"><input type="checkbox" value="Y" {if $search.product_type_c == "Y" || $check_all}checked="checked"{/if} name="product_type_c" id="cb_product_type_c" onclick="if (!this.checked) document.getElementById('cb_product_type_w').checked = true;"/>
{__("cart")}</label>

<label for="cb_product_type_w"><input type="checkbox" value="Y" {if $search.product_type_w == "Y" || $check_all}checked="checked"{/if} name="product_type_w" id="cb_product_type_w" onclick="if (!this.checked) document.getElementById('cb_product_type_c').checked = true;"  />
{__("wishlist")}</label>