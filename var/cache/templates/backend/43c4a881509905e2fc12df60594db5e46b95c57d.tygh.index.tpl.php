<?php /* Smarty version Smarty-3.1.18, created on 2014-10-27 16:10:18
         compiled from "/var/www/html/workspace/cscart/design/backend/templates/index.tpl" */ ?>
<?php /*%%SmartyHeaderCode:903680029544e362a5f1a07-19544732%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '43c4a881509905e2fc12df60594db5e46b95c57d' => 
    array (
      0 => '/var/www/html/workspace/cscart/design/backend/templates/index.tpl',
      1 => 1413383301,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '903680029544e362a5f1a07-19544732',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'page_title' => 0,
    'navigation' => 0,
    'images_dir' => 0,
    'content_tpl' => 0,
    'auth' => 0,
    'view_mode' => 0,
    'content' => 0,
    'stats' => 0,
    'runtime' => 0,
    'show_sm_dialog' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_544e362a685a58_74082327',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_544e362a685a58_74082327')) {function content_544e362a685a58_74082327($_smarty_tpl) {?><?php if (!is_callable('smarty_block_hook')) include '/var/www/html/workspace/cscart/app/functions/smarty_plugins/block.hook.php';
?><?php
fn_preload_lang_vars(array('admin_panel'));
?>
<!DOCTYPE html>
<html lang="en">

<head>
<title><?php if ($_smarty_tpl->tpl_vars['page_title']->value) {?><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['page_title']->value, ENT_QUOTES, 'UTF-8');?>
<?php } else { ?><?php if ($_smarty_tpl->tpl_vars['navigation']->value['selected_tab']) {?><?php echo $_smarty_tpl->__($_smarty_tpl->tpl_vars['navigation']->value['selected_tab']);?>
<?php if ($_smarty_tpl->tpl_vars['navigation']->value['subsection']) {?> :: <?php echo $_smarty_tpl->__($_smarty_tpl->tpl_vars['navigation']->value['subsection']);?>
<?php }?> - <?php }?><?php echo $_smarty_tpl->__("admin_panel");?>
<?php }?></title>
<meta http-equiv="X-UA-Compatible" content="chrome=1">
<meta name="viewport" content="width=1240,maximum-scale=1.0" />
<link href="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['images_dir']->value, ENT_QUOTES, 'UTF-8');?>
/favicon.ico" rel="shortcut icon">
<?php echo $_smarty_tpl->getSubTemplate ("common/styles.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

<?php echo $_smarty_tpl->getSubTemplate ("common/scripts.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

</head>
<?php echo $_smarty_tpl->getSubTemplate ("buttons/helpers.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

<!--[if lte IE 8 ]><body class="ie8"><![endif]-->
<!--[if lte IE 9 ]><body class="ie9"><![endif]-->
<!--[if !IE]><!--><body><!--<![endif]-->     
    <?php echo $_smarty_tpl->getSubTemplate ("common/loading_box.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

    <?php if (defined("THEMES_PANEL")) {?>
        <?php echo $_smarty_tpl->getSubTemplate ("demo_theme_selector.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

    <?php }?>
    <?php echo $_smarty_tpl->getSubTemplate ("common/notification.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

    <?php $_smarty_tpl->tpl_vars["content"] = new Smarty_variable($_smarty_tpl->getSubTemplate ($_smarty_tpl->tpl_vars['content_tpl']->value, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0));?>


    <div id="main_column<?php if (!$_smarty_tpl->tpl_vars['auth']->value['user_id']||$_smarty_tpl->tpl_vars['view_mode']->value=='simple') {?>_login<?php }?>" class="main-wrap">
    <?php if ($_smarty_tpl->tpl_vars['view_mode']->value!="simple") {?>
        <div class="admin-content">

            <div id="header" class="header">
                <?php echo $_smarty_tpl->getSubTemplate ("menu.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>

            <!--header--></div>

            <div class="admin-content-wrap">
                <?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"index:main_content")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"index:main_content"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>
<?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"index:main_content"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

                <?php echo $_smarty_tpl->tpl_vars['content']->value;?>

                <?php echo (($tmp = @$_smarty_tpl->tpl_vars['stats']->value)===null||$tmp==='' ? '' : $tmp);?>

            </div>

        </div>
        <?php } else { ?>
        <?php echo $_smarty_tpl->tpl_vars['content']->value;?>

    <?php }?>
    
    <!--main_column<?php if (!$_smarty_tpl->tpl_vars['auth']->value['user_id']||$_smarty_tpl->tpl_vars['view_mode']->value=='simple') {?>_login<?php }?>--></div>
    
    <?php echo $_smarty_tpl->getSubTemplate ("common/comet.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array(), 0);?>


    <?php if (fn_check_meta_redirect($_REQUEST['meta_redirect_url'])) {?>
        <meta http-equiv="refresh" content="1;url=<?php echo htmlspecialchars(fn_url(fn_check_meta_redirect($_REQUEST['meta_redirect_url'])), ENT_QUOTES, 'UTF-8');?>
" />
    <?php }?>

    <?php if ($_smarty_tpl->tpl_vars['auth']->value['user_id']&&(!$_smarty_tpl->tpl_vars['runtime']->value['company_id']||$_smarty_tpl->tpl_vars['runtime']->value['simple_ultimate'])) {?>
        <?php echo $_smarty_tpl->getSubTemplate ("views/settings/store_mode.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('show'=>$_smarty_tpl->tpl_vars['show_sm_dialog']->value), 0);?>

    <?php }?>

    <?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"index:after_content")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"index:after_content"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>
<?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"index:after_content"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>


</body>
</html>
<?php }} ?>
