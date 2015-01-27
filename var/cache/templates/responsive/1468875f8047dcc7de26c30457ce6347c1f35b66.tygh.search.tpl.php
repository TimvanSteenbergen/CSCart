<?php /* Smarty version Smarty-3.1.18, created on 2014-10-28 14:22:00
         compiled from "/var/www/html/workspace/cscart/design/themes/responsive/templates/common/search.tpl" */ ?>
<?php /*%%SmartyHeaderCode:802356904544f6e4826e562-53026744%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '1468875f8047dcc7de26c30457ce6347c1f35b66' => 
    array (
      0 => '/var/www/html/workspace/cscart/design/themes/responsive/templates/common/search.tpl',
      1 => 1414411814,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '802356904544f6e4826e562-53026744',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'runtime' => 0,
    'settings' => 0,
    'search' => 0,
    'search_title' => 0,
    'search_input_id' => 0,
    'auth' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_544f6e482d6eb2_39943814',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_544f6e482d6eb2_39943814')) {function content_544f6e482d6eb2_39943814($_smarty_tpl) {?><?php if (!is_callable('smarty_block_hook')) include '/var/www/html/workspace/cscart/app/functions/smarty_plugins/block.hook.php';
if (!is_callable('smarty_function_math')) include '/var/www/html/workspace/cscart/app/lib/other/smarty/plugins/function.math.php';
if (!is_callable('smarty_function_set_id')) include '/var/www/html/workspace/cscart/app/functions/smarty_plugins/function.set_id.php';
?><?php
fn_preload_lang_vars(array('search','search_products','search','search','search','search_products','search','search'));
?>
<?php if ($_smarty_tpl->tpl_vars['runtime']->value['customization_mode']['design']=="Y"&&@constant('AREA')=="C") {?><?php $_smarty_tpl->_capture_stack[0][] = array("template_content", null, null); ob_start(); ?><div class="ty-search-block">
    <form action="<?php echo htmlspecialchars(fn_url(''), ENT_QUOTES, 'UTF-8');?>
" name="search_form" method="get">
        <input type="hidden" name="subcats" value="Y" />
        <input type="hidden" name="status" value="A" />
        <input type="hidden" name="pshort" value="Y" />
        <input type="hidden" name="pfull" value="Y" />
        <input type="hidden" name="pname" value="Y" />
        <input type="hidden" name="pkeywords" value="Y" />
        <input type="hidden" name="search_performed" value="Y" />

        <?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"search:additional_fields")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"search:additional_fields"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>
<?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"search:additional_fields"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>


        <?php if ($_smarty_tpl->tpl_vars['settings']->value['General']['search_objects']) {?><?php $_smarty_tpl->tpl_vars["search_title"] = new Smarty_variable($_smarty_tpl->__("search"), null, 0);?><?php } else { ?><?php $_smarty_tpl->tpl_vars["search_title"] = new Smarty_variable($_smarty_tpl->__("search_products"), null, 0);?><?php }?><input type="text" name="q" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['search']->value['q'], ENT_QUOTES, 'UTF-8');?>
" id="search_input<?php if (Smarty::$_smarty_vars['capture']['search_input_id']) {?>_<?php echo htmlspecialchars(Smarty::$_smarty_vars['capture']['search_input_id'], ENT_QUOTES, 'UTF-8');?>
<?php }?>" title="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['search_title']->value, ENT_QUOTES, 'UTF-8');?>
" class="ty-search-block__input cm-hint" /><?php if ($_smarty_tpl->tpl_vars['settings']->value['General']['search_objects']) {?><?php echo $_smarty_tpl->getSubTemplate ("buttons/magnifier.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('but_name'=>"search.results",'alt'=>__("search")), 0);?>
<?php } else { ?><?php echo $_smarty_tpl->getSubTemplate ("buttons/magnifier.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('but_name'=>"products.search",'alt'=>__("search")), 0);?>
<?php }?>

        <?php $_smarty_tpl->_capture_stack[0][] = array("search_input_id", null, null); ob_start(); ?>
            <?php echo smarty_function_math(array('equation'=>"x + y",'x'=>(($tmp = @Smarty::$_smarty_vars['capture']['search_input_id'])===null||$tmp==='' ? 1 : $tmp),'y'=>1,'assign'=>"search_input_id"),$_smarty_tpl);?>

            <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['search_input_id']->value, ENT_QUOTES, 'UTF-8');?>

        <?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>
    </form>
</div>
<?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?><?php if (trim(Smarty::$_smarty_vars['capture']['template_content'])) {?><?php if ($_smarty_tpl->tpl_vars['auth']->value['area']=="A") {?><span class="cm-template-box template-box" data-ca-te-template="common/search.tpl" id="<?php echo smarty_function_set_id(array('name'=>"common/search.tpl"),$_smarty_tpl);?>
"><div class="cm-template-icon icon-edit ty-icon-edit hidden"></div><?php echo Smarty::$_smarty_vars['capture']['template_content'];?>
<!--[/tpl_id]--></span><?php } else { ?><?php echo Smarty::$_smarty_vars['capture']['template_content'];?>
<?php }?><?php }?><?php } else { ?><div class="ty-search-block">
    <form action="<?php echo htmlspecialchars(fn_url(''), ENT_QUOTES, 'UTF-8');?>
" name="search_form" method="get">
        <input type="hidden" name="subcats" value="Y" />
        <input type="hidden" name="status" value="A" />
        <input type="hidden" name="pshort" value="Y" />
        <input type="hidden" name="pfull" value="Y" />
        <input type="hidden" name="pname" value="Y" />
        <input type="hidden" name="pkeywords" value="Y" />
        <input type="hidden" name="search_performed" value="Y" />

        <?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"search:additional_fields")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"search:additional_fields"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>
<?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"search:additional_fields"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>


        <?php if ($_smarty_tpl->tpl_vars['settings']->value['General']['search_objects']) {?><?php $_smarty_tpl->tpl_vars["search_title"] = new Smarty_variable($_smarty_tpl->__("search"), null, 0);?><?php } else { ?><?php $_smarty_tpl->tpl_vars["search_title"] = new Smarty_variable($_smarty_tpl->__("search_products"), null, 0);?><?php }?><input type="text" name="q" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['search']->value['q'], ENT_QUOTES, 'UTF-8');?>
" id="search_input<?php if (Smarty::$_smarty_vars['capture']['search_input_id']) {?>_<?php echo htmlspecialchars(Smarty::$_smarty_vars['capture']['search_input_id'], ENT_QUOTES, 'UTF-8');?>
<?php }?>" title="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['search_title']->value, ENT_QUOTES, 'UTF-8');?>
" class="ty-search-block__input cm-hint" /><?php if ($_smarty_tpl->tpl_vars['settings']->value['General']['search_objects']) {?><?php echo $_smarty_tpl->getSubTemplate ("buttons/magnifier.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('but_name'=>"search.results",'alt'=>__("search")), 0);?>
<?php } else { ?><?php echo $_smarty_tpl->getSubTemplate ("buttons/magnifier.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('but_name'=>"products.search",'alt'=>__("search")), 0);?>
<?php }?>

        <?php $_smarty_tpl->_capture_stack[0][] = array("search_input_id", null, null); ob_start(); ?>
            <?php echo smarty_function_math(array('equation'=>"x + y",'x'=>(($tmp = @Smarty::$_smarty_vars['capture']['search_input_id'])===null||$tmp==='' ? 1 : $tmp),'y'=>1,'assign'=>"search_input_id"),$_smarty_tpl);?>

            <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['search_input_id']->value, ENT_QUOTES, 'UTF-8');?>

        <?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>
    </form>
</div>
<?php }?><?php }} ?>
