<?php /* Smarty version Smarty-3.1.18, created on 2014-10-28 14:22:00
         compiled from "/var/www/html/workspace/cscart/design/themes/responsive/templates/blocks/cart_content.tpl" */ ?>
<?php /*%%SmartyHeaderCode:64658415544f6e4832b1c0-36315550%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'cc8282397ef2737e88baba70147df6da971fe076' => 
    array (
      0 => '/var/www/html/workspace/cscart/design/themes/responsive/templates/blocks/cart_content.tpl',
      1 => 1414411814,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '64658415544f6e4832b1c0-36315550',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'runtime' => 0,
    'block' => 0,
    'config' => 0,
    'dropdown_id' => 0,
    '_cart_products' => 0,
    'p' => 0,
    'key' => 0,
    'force_items_deletion' => 0,
    'r_url' => 0,
    'settings' => 0,
    'auth' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_544f6e4842a160_58158376',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_544f6e4842a160_58158376')) {function content_544f6e4842a160_58158376($_smarty_tpl) {?><?php if (!is_callable('smarty_block_hook')) include '/var/www/html/workspace/cscart/app/functions/smarty_plugins/block.hook.php';
if (!is_callable('smarty_function_set_id')) include '/var/www/html/workspace/cscart/app/functions/smarty_plugins/function.set_id.php';
?><?php
fn_preload_lang_vars(array('items','for','cart_is_empty','cart_is_empty','view_cart','checkout','items','for','cart_is_empty','cart_is_empty','view_cart','checkout'));
?>
<?php if ($_smarty_tpl->tpl_vars['runtime']->value['customization_mode']['design']=="Y"&&@constant('AREA')=="C") {?><?php $_smarty_tpl->_capture_stack[0][] = array("template_content", null, null); ob_start(); ?><?php $_smarty_tpl->tpl_vars["dropdown_id"] = new Smarty_variable($_smarty_tpl->tpl_vars['block']->value['snapping_id'], null, 0);?>
<?php $_smarty_tpl->tpl_vars["r_url"] = new Smarty_variable(rawurlencode($_smarty_tpl->tpl_vars['config']->value['current_url']), null, 0);?>
<?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"checkout:cart_content")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"checkout:cart_content"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

    <div class="ty-dropdown-box" id="cart_status_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['dropdown_id']->value, ENT_QUOTES, 'UTF-8');?>
">
         <div id="sw_dropdown_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['dropdown_id']->value, ENT_QUOTES, 'UTF-8');?>
" class="ty-dropdown-box__title cm-combination">
        <a href="<?php echo htmlspecialchars(fn_url("checkout.cart"), ENT_QUOTES, 'UTF-8');?>
">
            <?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"checkout:dropdown_title")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"checkout:dropdown_title"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

                <?php if ($_SESSION['cart']['amount']) {?>
                    <i class="ty-minicart__icon ty-icon-basket filled"></i>
                    <span class="ty-minicart-title ty-hand"><?php echo htmlspecialchars($_SESSION['cart']['amount'], ENT_QUOTES, 'UTF-8');?>
&nbsp;<?php echo $_smarty_tpl->__("items");?>
 <?php echo $_smarty_tpl->__("for");?>
&nbsp;<?php echo $_smarty_tpl->getSubTemplate ("common/price.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('value'=>$_SESSION['cart']['display_subtotal']), 0);?>
</span>
                    <i class="ty-icon-down-micro"></i>
                <?php } else { ?>
                    <i class="ty-minicart__icon ty-icon-basket empty"></i>
                    <span class="ty-minicart-title empty-cart ty-hand"><?php echo $_smarty_tpl->__("cart_is_empty");?>
</span>
                    <i class="ty-icon-down-micro"></i>
                <?php }?>
            <?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"checkout:dropdown_title"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

        </a>
        </div>
        <div id="dropdown_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['dropdown_id']->value, ENT_QUOTES, 'UTF-8');?>
" class="cm-popup-box ty-dropdown-box__content hidden">
            <?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"checkout:minicart")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"checkout:minicart"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

                <div class="cm-cart-content <?php if ($_smarty_tpl->tpl_vars['block']->value['properties']['products_links_type']=="thumb") {?>cm-cart-content-thumb<?php }?> <?php if ($_smarty_tpl->tpl_vars['block']->value['properties']['display_delete_icons']=="Y") {?>cm-cart-content-delete<?php }?>">
                        <div class="ty-cart-items">
                            <?php if ($_SESSION['cart']['amount']) {?>
                                <ul class="ty-cart-items__list">
                                    <?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"index:cart_status")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"index:cart_status"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

                                        <?php $_smarty_tpl->tpl_vars["_cart_products"] = new Smarty_variable(array_reverse($_SESSION['cart']['products'],true), null, 0);?>
                                        <?php  $_smarty_tpl->tpl_vars["p"] = new Smarty_Variable; $_smarty_tpl->tpl_vars["p"]->_loop = false;
 $_smarty_tpl->tpl_vars["key"] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['_cart_products']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars["p"]->key => $_smarty_tpl->tpl_vars["p"]->value) {
$_smarty_tpl->tpl_vars["p"]->_loop = true;
 $_smarty_tpl->tpl_vars["key"]->value = $_smarty_tpl->tpl_vars["p"]->key;
?>
                                            <?php if (!$_smarty_tpl->tpl_vars['p']->value['extra']['parent']) {?>
                                                <li class="ty-cart-items__list-item">
                                                    <?php if ($_smarty_tpl->tpl_vars['block']->value['properties']['products_links_type']=="thumb") {?>
                                                        <div class="ty-cart-items__list-item-image">
                                                            <?php echo $_smarty_tpl->getSubTemplate ("common/image.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('image_width'=>"40",'image_height'=>"40",'images'=>$_smarty_tpl->tpl_vars['p']->value['main_pair'],'no_ids'=>true), 0);?>

                                                        </div>
                                                    <?php }?>
                                                    <div class="ty-cart-items__list-item-desc">
                                                        <a href="<?php echo htmlspecialchars(fn_url("products.view?product_id=".((string)$_smarty_tpl->tpl_vars['p']->value['product_id'])), ENT_QUOTES, 'UTF-8');?>
"><?php echo fn_get_product_name($_smarty_tpl->tpl_vars['p']->value['product_id']);?>
</a>
                                                    <p>
                                                        <span><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['p']->value['amount'], ENT_QUOTES, 'UTF-8');?>
</span><span>&nbsp;x&nbsp;</span><?php echo $_smarty_tpl->getSubTemplate ("common/price.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('value'=>$_smarty_tpl->tpl_vars['p']->value['display_price'],'span_id'=>"price_".((string)$_smarty_tpl->tpl_vars['key']->value)."_".((string)$_smarty_tpl->tpl_vars['dropdown_id']->value),'class'=>"none"), 0);?>

                                                    </p>
                                                    </div>
                                                    <?php if ($_smarty_tpl->tpl_vars['block']->value['properties']['display_delete_icons']=="Y") {?>
                                                        <div class="ty-cart-items__list-item-tools cm-cart-item-delete">
                                                            <?php if ((!$_smarty_tpl->tpl_vars['runtime']->value['checkout']||$_smarty_tpl->tpl_vars['force_items_deletion']->value)&&!$_smarty_tpl->tpl_vars['p']->value['extra']['exclude_from_calculate']) {?>
                                                                <?php echo $_smarty_tpl->getSubTemplate ("buttons/button.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('but_href'=>"checkout.delete.from_status?cart_id=".((string)$_smarty_tpl->tpl_vars['key']->value)."&redirect_url=".((string)$_smarty_tpl->tpl_vars['r_url']->value),'but_meta'=>"cm-ajax cm-ajax-full-render",'but_target_id'=>"cart_status*",'but_role'=>"delete",'but_name'=>"delete_cart_item"), 0);?>

                                                            <?php }?>
                                                        </div>
                                                    <?php }?>
                                                </li>
                                            <?php }?>
                                        <?php } ?>
                                    <?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"index:cart_status"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

                                </ul>
                            <?php } else { ?>
                                <div class="ty-cart-items__empty ty-center"><?php echo $_smarty_tpl->__("cart_is_empty");?>
</div>
                            <?php }?>
                        </div>

                        <?php if ($_smarty_tpl->tpl_vars['block']->value['properties']['display_bottom_buttons']=="Y") {?>
                        <div class="cm-cart-buttons ty-cart-content__buttons buttons-container<?php if ($_SESSION['cart']['amount']) {?> full-cart<?php } else { ?> hidden<?php }?>">
                            <div class="ty-float-left">
                                <a href="<?php echo htmlspecialchars(fn_url("checkout.cart"), ENT_QUOTES, 'UTF-8');?>
" rel="nofollow" class="ty-btn ty-btn__secondary"><?php echo $_smarty_tpl->__("view_cart");?>
</a>
                            </div>
                            <?php if ($_smarty_tpl->tpl_vars['settings']->value['General']['checkout_redirect']!="Y") {?>
                            <div class="ty-float-right">
                                <a href="<?php echo htmlspecialchars(fn_url("checkout.checkout"), ENT_QUOTES, 'UTF-8');?>
" rel="nofollow" class="ty-btn ty-btn__primary"><?php echo $_smarty_tpl->__("checkout");?>
</a>
                            </div>
                            <?php }?>
                        </div>
                        <?php }?>

                </div>
            <?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"checkout:minicart"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

        </div>
    <!--cart_status_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['dropdown_id']->value, ENT_QUOTES, 'UTF-8');?>
--></div>
<?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"checkout:cart_content"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

<?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?><?php if (trim(Smarty::$_smarty_vars['capture']['template_content'])) {?><?php if ($_smarty_tpl->tpl_vars['auth']->value['area']=="A") {?><span class="cm-template-box template-box" data-ca-te-template="blocks/cart_content.tpl" id="<?php echo smarty_function_set_id(array('name'=>"blocks/cart_content.tpl"),$_smarty_tpl);?>
"><div class="cm-template-icon icon-edit ty-icon-edit hidden"></div><?php echo Smarty::$_smarty_vars['capture']['template_content'];?>
<!--[/tpl_id]--></span><?php } else { ?><?php echo Smarty::$_smarty_vars['capture']['template_content'];?>
<?php }?><?php }?><?php } else { ?><?php $_smarty_tpl->tpl_vars["dropdown_id"] = new Smarty_variable($_smarty_tpl->tpl_vars['block']->value['snapping_id'], null, 0);?>
<?php $_smarty_tpl->tpl_vars["r_url"] = new Smarty_variable(rawurlencode($_smarty_tpl->tpl_vars['config']->value['current_url']), null, 0);?>
<?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"checkout:cart_content")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"checkout:cart_content"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

    <div class="ty-dropdown-box" id="cart_status_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['dropdown_id']->value, ENT_QUOTES, 'UTF-8');?>
">
         <div id="sw_dropdown_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['dropdown_id']->value, ENT_QUOTES, 'UTF-8');?>
" class="ty-dropdown-box__title cm-combination">
        <a href="<?php echo htmlspecialchars(fn_url("checkout.cart"), ENT_QUOTES, 'UTF-8');?>
">
            <?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"checkout:dropdown_title")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"checkout:dropdown_title"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

                <?php if ($_SESSION['cart']['amount']) {?>
                    <i class="ty-minicart__icon ty-icon-basket filled"></i>
                    <span class="ty-minicart-title ty-hand"><?php echo htmlspecialchars($_SESSION['cart']['amount'], ENT_QUOTES, 'UTF-8');?>
&nbsp;<?php echo $_smarty_tpl->__("items");?>
 <?php echo $_smarty_tpl->__("for");?>
&nbsp;<?php echo $_smarty_tpl->getSubTemplate ("common/price.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('value'=>$_SESSION['cart']['display_subtotal']), 0);?>
</span>
                    <i class="ty-icon-down-micro"></i>
                <?php } else { ?>
                    <i class="ty-minicart__icon ty-icon-basket empty"></i>
                    <span class="ty-minicart-title empty-cart ty-hand"><?php echo $_smarty_tpl->__("cart_is_empty");?>
</span>
                    <i class="ty-icon-down-micro"></i>
                <?php }?>
            <?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"checkout:dropdown_title"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

        </a>
        </div>
        <div id="dropdown_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['dropdown_id']->value, ENT_QUOTES, 'UTF-8');?>
" class="cm-popup-box ty-dropdown-box__content hidden">
            <?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"checkout:minicart")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"checkout:minicart"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

                <div class="cm-cart-content <?php if ($_smarty_tpl->tpl_vars['block']->value['properties']['products_links_type']=="thumb") {?>cm-cart-content-thumb<?php }?> <?php if ($_smarty_tpl->tpl_vars['block']->value['properties']['display_delete_icons']=="Y") {?>cm-cart-content-delete<?php }?>">
                        <div class="ty-cart-items">
                            <?php if ($_SESSION['cart']['amount']) {?>
                                <ul class="ty-cart-items__list">
                                    <?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"index:cart_status")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"index:cart_status"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

                                        <?php $_smarty_tpl->tpl_vars["_cart_products"] = new Smarty_variable(array_reverse($_SESSION['cart']['products'],true), null, 0);?>
                                        <?php  $_smarty_tpl->tpl_vars["p"] = new Smarty_Variable; $_smarty_tpl->tpl_vars["p"]->_loop = false;
 $_smarty_tpl->tpl_vars["key"] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['_cart_products']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars["p"]->key => $_smarty_tpl->tpl_vars["p"]->value) {
$_smarty_tpl->tpl_vars["p"]->_loop = true;
 $_smarty_tpl->tpl_vars["key"]->value = $_smarty_tpl->tpl_vars["p"]->key;
?>
                                            <?php if (!$_smarty_tpl->tpl_vars['p']->value['extra']['parent']) {?>
                                                <li class="ty-cart-items__list-item">
                                                    <?php if ($_smarty_tpl->tpl_vars['block']->value['properties']['products_links_type']=="thumb") {?>
                                                        <div class="ty-cart-items__list-item-image">
                                                            <?php echo $_smarty_tpl->getSubTemplate ("common/image.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('image_width'=>"40",'image_height'=>"40",'images'=>$_smarty_tpl->tpl_vars['p']->value['main_pair'],'no_ids'=>true), 0);?>

                                                        </div>
                                                    <?php }?>
                                                    <div class="ty-cart-items__list-item-desc">
                                                        <a href="<?php echo htmlspecialchars(fn_url("products.view?product_id=".((string)$_smarty_tpl->tpl_vars['p']->value['product_id'])), ENT_QUOTES, 'UTF-8');?>
"><?php echo fn_get_product_name($_smarty_tpl->tpl_vars['p']->value['product_id']);?>
</a>
                                                    <p>
                                                        <span><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['p']->value['amount'], ENT_QUOTES, 'UTF-8');?>
</span><span>&nbsp;x&nbsp;</span><?php echo $_smarty_tpl->getSubTemplate ("common/price.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('value'=>$_smarty_tpl->tpl_vars['p']->value['display_price'],'span_id'=>"price_".((string)$_smarty_tpl->tpl_vars['key']->value)."_".((string)$_smarty_tpl->tpl_vars['dropdown_id']->value),'class'=>"none"), 0);?>

                                                    </p>
                                                    </div>
                                                    <?php if ($_smarty_tpl->tpl_vars['block']->value['properties']['display_delete_icons']=="Y") {?>
                                                        <div class="ty-cart-items__list-item-tools cm-cart-item-delete">
                                                            <?php if ((!$_smarty_tpl->tpl_vars['runtime']->value['checkout']||$_smarty_tpl->tpl_vars['force_items_deletion']->value)&&!$_smarty_tpl->tpl_vars['p']->value['extra']['exclude_from_calculate']) {?>
                                                                <?php echo $_smarty_tpl->getSubTemplate ("buttons/button.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('but_href'=>"checkout.delete.from_status?cart_id=".((string)$_smarty_tpl->tpl_vars['key']->value)."&redirect_url=".((string)$_smarty_tpl->tpl_vars['r_url']->value),'but_meta'=>"cm-ajax cm-ajax-full-render",'but_target_id'=>"cart_status*",'but_role'=>"delete",'but_name'=>"delete_cart_item"), 0);?>

                                                            <?php }?>
                                                        </div>
                                                    <?php }?>
                                                </li>
                                            <?php }?>
                                        <?php } ?>
                                    <?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"index:cart_status"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

                                </ul>
                            <?php } else { ?>
                                <div class="ty-cart-items__empty ty-center"><?php echo $_smarty_tpl->__("cart_is_empty");?>
</div>
                            <?php }?>
                        </div>

                        <?php if ($_smarty_tpl->tpl_vars['block']->value['properties']['display_bottom_buttons']=="Y") {?>
                        <div class="cm-cart-buttons ty-cart-content__buttons buttons-container<?php if ($_SESSION['cart']['amount']) {?> full-cart<?php } else { ?> hidden<?php }?>">
                            <div class="ty-float-left">
                                <a href="<?php echo htmlspecialchars(fn_url("checkout.cart"), ENT_QUOTES, 'UTF-8');?>
" rel="nofollow" class="ty-btn ty-btn__secondary"><?php echo $_smarty_tpl->__("view_cart");?>
</a>
                            </div>
                            <?php if ($_smarty_tpl->tpl_vars['settings']->value['General']['checkout_redirect']!="Y") {?>
                            <div class="ty-float-right">
                                <a href="<?php echo htmlspecialchars(fn_url("checkout.checkout"), ENT_QUOTES, 'UTF-8');?>
" rel="nofollow" class="ty-btn ty-btn__primary"><?php echo $_smarty_tpl->__("checkout");?>
</a>
                            </div>
                            <?php }?>
                        </div>
                        <?php }?>

                </div>
            <?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"checkout:minicart"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

        </div>
    <!--cart_status_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['dropdown_id']->value, ENT_QUOTES, 'UTF-8');?>
--></div>
<?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"checkout:cart_content"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

<?php }?><?php }} ?>
