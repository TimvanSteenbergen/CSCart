<?php /* Smarty version Smarty-3.1.18, created on 2014-10-28 14:22:03
         compiled from "/var/www/html/workspace/cscart/design/themes/responsive/templates/blocks/our_brands.tpl" */ ?>
<?php /*%%SmartyHeaderCode:1771623104544f6e4befcb70-04294734%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'f1c3e47e8ded0de3e15804333921e4213cfc7b3a' => 
    array (
      0 => '/var/www/html/workspace/cscart/design/themes/responsive/templates/blocks/our_brands.tpl',
      1 => 1414411814,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '1771623104544f6e4befcb70-04294734',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'runtime' => 0,
    'block' => 0,
    'delim_width' => 0,
    'image_h' => 0,
    'text_h' => 0,
    'item_qty' => 0,
    'brands' => 0,
    'brand' => 0,
    'object_img' => 0,
    'auth' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_544f6e4c047714_34439638',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_544f6e4c047714_34439638')) {function content_544f6e4c047714_34439638($_smarty_tpl) {?><?php if (!is_callable('smarty_function_math')) include '/var/www/html/workspace/cscart/app/lib/other/smarty/plugins/function.math.php';
if (!is_callable('smarty_function_set_id')) include '/var/www/html/workspace/cscart/app/functions/smarty_plugins/function.set_id.php';
?><?php if ($_smarty_tpl->tpl_vars['runtime']->value['customization_mode']['design']=="Y"&&@constant('AREA')=="C") {?><?php $_smarty_tpl->_capture_stack[0][] = array("template_content", null, null); ob_start(); ?><?php $_smarty_tpl->tpl_vars["obj_prefix"] = new Smarty_variable(((string)$_smarty_tpl->tpl_vars['block']->value['block_id'])."000", null, 0);?>

<?php $_smarty_tpl->tpl_vars["delim_width"] = new Smarty_variable("50", null, 0);?>
<?php echo smarty_function_math(array('equation'=>"delim_w + image_w",'assign'=>"item_width",'image_w'=>$_smarty_tpl->tpl_vars['block']->value['properties']['thumbnail_width'],'delim_w'=>$_smarty_tpl->tpl_vars['delim_width']->value),$_smarty_tpl);?>

<?php $_smarty_tpl->tpl_vars["item_qty"] = new Smarty_variable($_smarty_tpl->tpl_vars['block']->value['properties']['item_quantity'], null, 0);?>

    <div id="scroll_list_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['block']->value['block_id'], ENT_QUOTES, 'UTF-8');?>
" class="owl-carousel">
        <?php $_smarty_tpl->tpl_vars["image_h"] = new Smarty_variable($_smarty_tpl->tpl_vars['block']->value['properties']['thumbnail_width'], null, 0);?>
        <?php $_smarty_tpl->tpl_vars["text_h"] = new Smarty_variable("65", null, 0);?>

        <?php echo smarty_function_math(array('equation'=>"item_qty + image_h + text_h",'assign'=>"item_height",'image_h'=>$_smarty_tpl->tpl_vars['image_h']->value,'text_h'=>$_smarty_tpl->tpl_vars['text_h']->value,'item_qty'=>$_smarty_tpl->tpl_vars['item_qty']->value),$_smarty_tpl);?>


        <?php  $_smarty_tpl->tpl_vars["brand"] = new Smarty_Variable; $_smarty_tpl->tpl_vars["brand"]->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['brands']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars["brand"]->key => $_smarty_tpl->tpl_vars["brand"]->value) {
$_smarty_tpl->tpl_vars["brand"]->_loop = true;
?>
                <?php $_smarty_tpl->tpl_vars["obj_id"] = new Smarty_variable("scr_".((string)$_smarty_tpl->tpl_vars['block']->value['block_id'])."000".((string)$_smarty_tpl->tpl_vars['brand']->value['variant_id']), null, 0);?>
                <?php $_smarty_tpl->tpl_vars["object_img"] = new Smarty_variable($_smarty_tpl->getSubTemplate ("common/image.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('class'=>"ty-grayscale",'image_width'=>$_smarty_tpl->tpl_vars['block']->value['properties']['thumbnail_width'],'image_height'=>$_smarty_tpl->tpl_vars['block']->value['properties']['thumbnail_width'],'images'=>$_smarty_tpl->tpl_vars['brand']->value['image_pair'],'no_ids'=>true), 0));?>

                <div class="ty-center">
                    <a href="<?php echo htmlspecialchars(fn_url("product_features.view?variant_id=".((string)$_smarty_tpl->tpl_vars['brand']->value['variant_id'])), ENT_QUOTES, 'UTF-8');?>
"><?php echo $_smarty_tpl->tpl_vars['object_img']->value;?>
</a>
                </div>
        <?php } ?>
    </div>

<?php echo $_smarty_tpl->getSubTemplate ("common/scroller_init.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('items'=>$_smarty_tpl->tpl_vars['brands']->value), 0);?>
<?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?><?php if (trim(Smarty::$_smarty_vars['capture']['template_content'])) {?><?php if ($_smarty_tpl->tpl_vars['auth']->value['area']=="A") {?><span class="cm-template-box template-box" data-ca-te-template="blocks/our_brands.tpl" id="<?php echo smarty_function_set_id(array('name'=>"blocks/our_brands.tpl"),$_smarty_tpl);?>
"><div class="cm-template-icon icon-edit ty-icon-edit hidden"></div><?php echo Smarty::$_smarty_vars['capture']['template_content'];?>
<!--[/tpl_id]--></span><?php } else { ?><?php echo Smarty::$_smarty_vars['capture']['template_content'];?>
<?php }?><?php }?><?php } else { ?><?php $_smarty_tpl->tpl_vars["obj_prefix"] = new Smarty_variable(((string)$_smarty_tpl->tpl_vars['block']->value['block_id'])."000", null, 0);?>

<?php $_smarty_tpl->tpl_vars["delim_width"] = new Smarty_variable("50", null, 0);?>
<?php echo smarty_function_math(array('equation'=>"delim_w + image_w",'assign'=>"item_width",'image_w'=>$_smarty_tpl->tpl_vars['block']->value['properties']['thumbnail_width'],'delim_w'=>$_smarty_tpl->tpl_vars['delim_width']->value),$_smarty_tpl);?>

<?php $_smarty_tpl->tpl_vars["item_qty"] = new Smarty_variable($_smarty_tpl->tpl_vars['block']->value['properties']['item_quantity'], null, 0);?>

    <div id="scroll_list_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['block']->value['block_id'], ENT_QUOTES, 'UTF-8');?>
" class="owl-carousel">
        <?php $_smarty_tpl->tpl_vars["image_h"] = new Smarty_variable($_smarty_tpl->tpl_vars['block']->value['properties']['thumbnail_width'], null, 0);?>
        <?php $_smarty_tpl->tpl_vars["text_h"] = new Smarty_variable("65", null, 0);?>

        <?php echo smarty_function_math(array('equation'=>"item_qty + image_h + text_h",'assign'=>"item_height",'image_h'=>$_smarty_tpl->tpl_vars['image_h']->value,'text_h'=>$_smarty_tpl->tpl_vars['text_h']->value,'item_qty'=>$_smarty_tpl->tpl_vars['item_qty']->value),$_smarty_tpl);?>


        <?php  $_smarty_tpl->tpl_vars["brand"] = new Smarty_Variable; $_smarty_tpl->tpl_vars["brand"]->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['brands']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars["brand"]->key => $_smarty_tpl->tpl_vars["brand"]->value) {
$_smarty_tpl->tpl_vars["brand"]->_loop = true;
?>
                <?php $_smarty_tpl->tpl_vars["obj_id"] = new Smarty_variable("scr_".((string)$_smarty_tpl->tpl_vars['block']->value['block_id'])."000".((string)$_smarty_tpl->tpl_vars['brand']->value['variant_id']), null, 0);?>
                <?php $_smarty_tpl->tpl_vars["object_img"] = new Smarty_variable($_smarty_tpl->getSubTemplate ("common/image.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('class'=>"ty-grayscale",'image_width'=>$_smarty_tpl->tpl_vars['block']->value['properties']['thumbnail_width'],'image_height'=>$_smarty_tpl->tpl_vars['block']->value['properties']['thumbnail_width'],'images'=>$_smarty_tpl->tpl_vars['brand']->value['image_pair'],'no_ids'=>true), 0));?>

                <div class="ty-center">
                    <a href="<?php echo htmlspecialchars(fn_url("product_features.view?variant_id=".((string)$_smarty_tpl->tpl_vars['brand']->value['variant_id'])), ENT_QUOTES, 'UTF-8');?>
"><?php echo $_smarty_tpl->tpl_vars['object_img']->value;?>
</a>
                </div>
        <?php } ?>
    </div>

<?php echo $_smarty_tpl->getSubTemplate ("common/scroller_init.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('items'=>$_smarty_tpl->tpl_vars['brands']->value), 0);?>
<?php }?><?php }} ?>
