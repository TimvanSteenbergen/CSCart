<?php /* Smarty version Smarty-3.1.18, created on 2014-10-28 14:21:58
         compiled from "/var/www/html/workspace/cscart/design/themes/responsive/templates/index.tpl" */ ?>
<?php /*%%SmartyHeaderCode:1810982856544f6e465758a0-90387334%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '5ee4230163048138b5b78da860ebe9187f2401bf' => 
    array (
      0 => '/var/www/html/workspace/cscart/design/themes/responsive/templates/index.tpl',
      1 => 1414411814,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '1810982856544f6e465758a0-90387334',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'page_title' => 0,
    'breadcrumbs' => 0,
    'i' => 0,
    'skip_page_title' => 0,
    'location_data' => 0,
    'logos' => 0,
    'runtime' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_544f6e4662d7e5_48246142',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_544f6e4662d7e5_48246142')) {function content_544f6e4662d7e5_48246142($_smarty_tpl) {?><?php if (!is_callable('smarty_block_hook')) include '/var/www/html/workspace/cscart/app/functions/smarty_plugins/block.hook.php';
if (!is_callable('smarty_function_render_location')) include '/var/www/html/workspace/cscart/app/functions/smarty_plugins/function.render_location.php';
?><?php
fn_preload_lang_vars(array('on_site_template_editing','on_site_live_editing'));
?>

<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(@constant('CART_LANGUAGE'), ENT_QUOTES, 'UTF-8');?>
">
<head>
<?php $_smarty_tpl->_capture_stack[0][] = array("title", null, null); ob_start(); ?>
<?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"index:title")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"index:title"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

<?php if ($_smarty_tpl->tpl_vars['page_title']->value) {?>
    <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['page_title']->value, ENT_QUOTES, 'UTF-8');?>

<?php } else { ?>
    <?php  $_smarty_tpl->tpl_vars['i'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['i']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['breadcrumbs']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['i']->total= $_smarty_tpl->_count($_from);
 $_smarty_tpl->tpl_vars['i']->iteration=0;
 $_smarty_tpl->tpl_vars['i']->index=-1;
foreach ($_from as $_smarty_tpl->tpl_vars['i']->key => $_smarty_tpl->tpl_vars['i']->value) {
$_smarty_tpl->tpl_vars['i']->_loop = true;
 $_smarty_tpl->tpl_vars['i']->iteration++;
 $_smarty_tpl->tpl_vars['i']->index++;
 $_smarty_tpl->tpl_vars['i']->first = $_smarty_tpl->tpl_vars['i']->index === 0;
 $_smarty_tpl->tpl_vars['i']->last = $_smarty_tpl->tpl_vars['i']->iteration === $_smarty_tpl->tpl_vars['i']->total;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']["bkt"]['first'] = $_smarty_tpl->tpl_vars['i']->first;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']["bkt"]['last'] = $_smarty_tpl->tpl_vars['i']->last;
?>
        <?php if (!$_smarty_tpl->getVariable('smarty')->value['foreach']['bkt']['first']) {?><?php echo htmlspecialchars(preg_replace('!<[^>]*?>!', ' ', $_smarty_tpl->tpl_vars['i']->value['title']), ENT_QUOTES, 'UTF-8');?>
<?php if (!$_smarty_tpl->getVariable('smarty')->value['foreach']['bkt']['last']) {?> :: <?php }?><?php }?>
    <?php } ?>
    <?php if (!$_smarty_tpl->tpl_vars['skip_page_title']->value&&$_smarty_tpl->tpl_vars['location_data']->value['title']) {?><?php if (count($_smarty_tpl->tpl_vars['breadcrumbs']->value)>1) {?> - <?php }?><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['location_data']->value['title'], ENT_QUOTES, 'UTF-8');?>
<?php }?>
<?php }?>
<?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"index:title"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

<?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>
<title><?php echo trim(preg_replace('!\s+!u', ' ',Smarty::$_smarty_vars['capture']['title']));?>
</title>
<?php echo $_smarty_tpl->getSubTemplate ("meta.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

<link href="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['logos']->value['favicon']['image']['image_path'], ENT_QUOTES, 'UTF-8');?>
" rel="shortcut icon" />
<?php echo $_smarty_tpl->getSubTemplate ("common/styles.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('include_dropdown'=>true), 0);?>

<?php echo $_smarty_tpl->getSubTemplate ("common/scripts.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

</head>

<body>
<?php if ($_smarty_tpl->tpl_vars['runtime']->value['customization_mode']['design']) {?>
    <?php echo $_smarty_tpl->getSubTemplate ("common/toolbar.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('title'=>__("on_site_template_editing"),'href'=>"customization.disable_mode?type=design"), 0);?>

<?php }?>
<?php if ($_smarty_tpl->tpl_vars['runtime']->value['customization_mode']['live_editor']) {?>
    <?php echo $_smarty_tpl->getSubTemplate ("common/toolbar.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('title'=>__("on_site_live_editing"),'href'=>"customization.disable_mode?type=live_editor"), 0);?>

<?php }?>
<?php if (defined("THEMES_PANEL")) {?>
    <?php echo $_smarty_tpl->getSubTemplate ("demo_theme_selector.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

<?php }?>

<div class="ty-tygh <?php if ($_smarty_tpl->tpl_vars['runtime']->value['customization_mode']['theme_editor']) {?>te-mode<?php }?> <?php if ($_smarty_tpl->tpl_vars['runtime']->value['customization_mode']['live_editor']||$_smarty_tpl->tpl_vars['runtime']->value['customization_mode']['design']) {?>ty-top-panel-padding<?php }?>" id="tygh_container">

<?php echo $_smarty_tpl->getSubTemplate ("common/loading_box.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

<?php echo $_smarty_tpl->getSubTemplate ("common/notification.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>


<div class="ty-helper-container" id="tygh_main_container">
    <?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"index:content")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"index:content"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

        <?php echo smarty_function_render_location(array(),$_smarty_tpl);?>

    <?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"index:content"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

<!--tygh_main_container--></div>


<?php if ($_smarty_tpl->tpl_vars['runtime']->value['customization_mode']['design']) {?>
    <?php echo $_smarty_tpl->getSubTemplate ("common/template_editor.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

<?php }?>
<?php if ($_smarty_tpl->tpl_vars['runtime']->value['customization_mode']['theme_editor']) {?>
    <?php echo $_smarty_tpl->getSubTemplate ("common/theme_editor.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

<?php }?>
<?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"index:footer")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"index:footer"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>
<?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"index:footer"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

<!--tygh_container--></div>
</body>

</html>
<?php }} ?>
