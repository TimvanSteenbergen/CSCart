<?php /* Smarty version Smarty-3.1.18, created on 2014-10-28 14:21:58
         compiled from "/var/www/html/workspace/cscart/design/themes/responsive/templates/addons/bestsellers/hooks/index/styles.post.tpl" */ ?>
<?php /*%%SmartyHeaderCode:1607162910544f6e468f2a55-53776900%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '9f0359ef68dbe16317474d1a1703d808571e71e1' => 
    array (
      0 => '/var/www/html/workspace/cscart/design/themes/responsive/templates/addons/bestsellers/hooks/index/styles.post.tpl',
      1 => 1414411814,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '1607162910544f6e468f2a55-53776900',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'runtime' => 0,
    'auth' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_544f6e4690d7a9_52672301',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_544f6e4690d7a9_52672301')) {function content_544f6e4690d7a9_52672301($_smarty_tpl) {?><?php if (!is_callable('smarty_function_style')) include '/var/www/html/workspace/cscart/app/functions/smarty_plugins/function.style.php';
if (!is_callable('smarty_function_set_id')) include '/var/www/html/workspace/cscart/app/functions/smarty_plugins/function.set_id.php';
?><?php if ($_smarty_tpl->tpl_vars['runtime']->value['customization_mode']['design']=="Y"&&@constant('AREA')=="C") {?><?php $_smarty_tpl->_capture_stack[0][] = array("template_content", null, null); ob_start(); ?><?php echo smarty_function_style(array('src'=>"addons/bestsellers/styles.css"),$_smarty_tpl);?>
<?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?><?php if (trim(Smarty::$_smarty_vars['capture']['template_content'])) {?><?php if ($_smarty_tpl->tpl_vars['auth']->value['area']=="A") {?><span class="cm-template-box template-box" data-ca-te-template="addons/bestsellers/hooks/index/styles.post.tpl" id="<?php echo smarty_function_set_id(array('name'=>"addons/bestsellers/hooks/index/styles.post.tpl"),$_smarty_tpl);?>
"><div class="cm-template-icon icon-edit ty-icon-edit hidden"></div><?php echo Smarty::$_smarty_vars['capture']['template_content'];?>
<!--[/tpl_id]--></span><?php } else { ?><?php echo Smarty::$_smarty_vars['capture']['template_content'];?>
<?php }?><?php }?><?php } else { ?><?php echo smarty_function_style(array('src'=>"addons/bestsellers/styles.css"),$_smarty_tpl);?>
<?php }?><?php }} ?>
