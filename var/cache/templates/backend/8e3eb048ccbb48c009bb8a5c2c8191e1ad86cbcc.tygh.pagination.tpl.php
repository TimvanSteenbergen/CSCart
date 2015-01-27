<?php /* Smarty version Smarty-3.1.18, created on 2014-11-24 17:22:20
         compiled from "/var/www/html/workspace/cscart/design/backend/templates/common/pagination.tpl" */ ?>
<?php /*%%SmartyHeaderCode:110478846254733f1c9329b4-81538503%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '8e3eb048ccbb48c009bb8a5c2c8191e1ad86cbcc' => 
    array (
      0 => '/var/www/html/workspace/cscart/design/backend/templates/common/pagination.tpl',
      1 => 1413383301,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '110478846254733f1c9329b4-81538503',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'div_id' => 0,
    'config' => 0,
    'search' => 0,
    'pagination_class' => 0,
    'id' => 0,
    'pagination' => 0,
    'save_current_page' => 0,
    'save_current_url' => 0,
    'disable_history' => 0,
    'history_class' => 0,
    'c_url' => 0,
    'pg' => 0,
    'step' => 0,
    'rnd' => 0,
    'pagination_meta' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_54733f1c9dbca5_97230297',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54733f1c9dbca5_97230297')) {function content_54733f1c9dbca5_97230297($_smarty_tpl) {?><?php if (!is_callable('smarty_function_math')) include '/var/www/html/workspace/cscart/app/lib/other/smarty/plugins/function.math.php';
?><?php
fn_preload_lang_vars(array('previous','next','total_items'));
?>
<?php $_smarty_tpl->tpl_vars["id"] = new Smarty_variable((($tmp = @$_smarty_tpl->tpl_vars['div_id']->value)===null||$tmp==='' ? "pagination_contents" : $tmp), null, 0);?>
<?php $_smarty_tpl->tpl_vars["c_url"] = new Smarty_variable(fn_query_remove($_smarty_tpl->tpl_vars['config']->value['current_url'],"page"), null, 0);?>
<?php $_smarty_tpl->tpl_vars["pagination"] = new Smarty_variable(fn_generate_pagination($_smarty_tpl->tpl_vars['search']->value), null, 0);?>

<?php if (Smarty::$_smarty_vars['capture']['pagination_open']=="Y") {?>
    <?php $_smarty_tpl->tpl_vars["pagination_meta"] = new Smarty_variable(" paginate-top", null, 0);?>
<?php }?>

<?php if (Smarty::$_smarty_vars['capture']['pagination_open']!="Y") {?>
<div class="cm-pagination-container<?php if ($_smarty_tpl->tpl_vars['pagination_class']->value) {?> <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['pagination_class']->value, ENT_QUOTES, 'UTF-8');?>
<?php }?>" id="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
">
<?php }?>

<?php if ($_smarty_tpl->tpl_vars['pagination']->value) {?>
    <?php if ($_smarty_tpl->tpl_vars['save_current_page']->value) {?>
        <input type="hidden" name="page" value="<?php echo htmlspecialchars((($tmp = @(($tmp = @$_smarty_tpl->tpl_vars['search']->value['page'])===null||$tmp==='' ? $_REQUEST['page'] : $tmp))===null||$tmp==='' ? 1 : $tmp), ENT_QUOTES, 'UTF-8');?>
" />
    <?php }?>

    <?php if ($_smarty_tpl->tpl_vars['save_current_url']->value) {?>
        <input type="hidden" name="redirect_url" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['config']->value['current_url'], ENT_QUOTES, 'UTF-8');?>
" />
    <?php }?>

    <?php if (!$_smarty_tpl->tpl_vars['disable_history']->value) {?>
        <?php $_smarty_tpl->tpl_vars["history_class"] = new Smarty_variable(" cm-history", null, 0);?>
    <?php } else { ?>
        <?php $_smarty_tpl->tpl_vars["history_class"] = new Smarty_variable(" cm-ajax-cache", null, 0);?>
    <?php }?>
    <div class="pagination-wrap clearfix">
    <?php if ($_smarty_tpl->tpl_vars['pagination']->value['total_pages']>1) {?>
    <div class="pagination pull-left">
        <ul>
        <?php if ($_smarty_tpl->tpl_vars['pagination']->value['current_page']!="full_list"&&$_smarty_tpl->tpl_vars['pagination']->value['total_pages']>1) {?>
            <li class="<?php if (!$_smarty_tpl->tpl_vars['pagination']->value['prev_page']) {?>disabled<?php }?><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['history_class']->value, ENT_QUOTES, 'UTF-8');?>
"><a data-ca-scroll=".cm-pagination-container" class="cm-ajax<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['history_class']->value, ENT_QUOTES, 'UTF-8');?>
" <?php if ($_smarty_tpl->tpl_vars['pagination']->value['prev_page']) {?>href="<?php echo htmlspecialchars(fn_url(((string)$_smarty_tpl->tpl_vars['c_url']->value)."&page=".((string)$_smarty_tpl->tpl_vars['pagination']->value['prev_page'])), ENT_QUOTES, 'UTF-8');?>
" data-ca-page="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['pagination']->value['prev_page'], ENT_QUOTES, 'UTF-8');?>
" data-ca-target-id="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
"<?php }?>>&laquo;&nbsp;<?php echo $_smarty_tpl->__("previous");?>
</a></li>

            <?php  $_smarty_tpl->tpl_vars["pg"] = new Smarty_Variable; $_smarty_tpl->tpl_vars["pg"]->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['pagination']->value['navi_pages']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars["pg"]->total= $_smarty_tpl->_count($_from);
 $_smarty_tpl->tpl_vars["pg"]->iteration=0;
 $_smarty_tpl->tpl_vars["pg"]->index=-1;
foreach ($_from as $_smarty_tpl->tpl_vars["pg"]->key => $_smarty_tpl->tpl_vars["pg"]->value) {
$_smarty_tpl->tpl_vars["pg"]->_loop = true;
 $_smarty_tpl->tpl_vars["pg"]->iteration++;
 $_smarty_tpl->tpl_vars["pg"]->index++;
 $_smarty_tpl->tpl_vars["pg"]->first = $_smarty_tpl->tpl_vars["pg"]->index === 0;
 $_smarty_tpl->tpl_vars["pg"]->last = $_smarty_tpl->tpl_vars["pg"]->iteration === $_smarty_tpl->tpl_vars["pg"]->total;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']["f_pg"]['first'] = $_smarty_tpl->tpl_vars["pg"]->first;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']["f_pg"]['last'] = $_smarty_tpl->tpl_vars["pg"]->last;
?>
            <li <?php if ($_smarty_tpl->tpl_vars['pg']->value==$_smarty_tpl->tpl_vars['pagination']->value['current_page']) {?>class="active" <?php }?>>
                <?php if ($_smarty_tpl->getVariable('smarty')->value['foreach']['f_pg']['first']&&$_smarty_tpl->tpl_vars['pg']->value>1) {?>
                <a data-ca-scroll=".cm-pagination-container" class="cm-ajax<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['history_class']->value, ENT_QUOTES, 'UTF-8');?>
" href="<?php echo htmlspecialchars(fn_url(((string)$_smarty_tpl->tpl_vars['c_url']->value)."&page=1`"), ENT_QUOTES, 'UTF-8');?>
" data-ca-page="1" data-ca-target-id="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
">1</a>
                <?php if ($_smarty_tpl->tpl_vars['pg']->value!=2) {?><a data-ca-scroll=".cm-pagination-container" class="<?php if ($_smarty_tpl->tpl_vars['pagination']->value['prev_range']) {?>cm-ajax<?php }?> prev-range<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['history_class']->value, ENT_QUOTES, 'UTF-8');?>
" <?php if ($_smarty_tpl->tpl_vars['pagination']->value['prev_range']) {?>href="<?php echo htmlspecialchars(fn_url(((string)$_smarty_tpl->tpl_vars['c_url']->value)."&page=".((string)$_smarty_tpl->tpl_vars['pagination']->value['prev_range'])), ENT_QUOTES, 'UTF-8');?>
" data-ca-page="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['pagination']->value['prev_range'], ENT_QUOTES, 'UTF-8');?>
" data-ca-target-id="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
"<?php }?>>&nbsp;...&nbsp;</a><?php }?>
                <?php }?>
                <?php if ($_smarty_tpl->tpl_vars['pg']->value!=$_smarty_tpl->tpl_vars['pagination']->value['current_page']) {?><a data-ca-scroll=".cm-pagination-container" class="cm-ajax<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['history_class']->value, ENT_QUOTES, 'UTF-8');?>
" href="<?php echo htmlspecialchars(fn_url(((string)$_smarty_tpl->tpl_vars['c_url']->value)."&page=".((string)$_smarty_tpl->tpl_vars['pg']->value)), ENT_QUOTES, 'UTF-8');?>
" data-ca-page="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['pg']->value, ENT_QUOTES, 'UTF-8');?>
" data-ca-target-id="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['pg']->value, ENT_QUOTES, 'UTF-8');?>
</a><?php } else { ?><a href="#"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['pg']->value, ENT_QUOTES, 'UTF-8');?>
</a><?php }?>
                <?php if ($_smarty_tpl->getVariable('smarty')->value['foreach']['f_pg']['last']&&$_smarty_tpl->tpl_vars['pg']->value<$_smarty_tpl->tpl_vars['pagination']->value['total_pages']) {?>
                <?php if ($_smarty_tpl->tpl_vars['pg']->value!=$_smarty_tpl->tpl_vars['pagination']->value['total_pages']-1) {?><a data-ca-scroll=".cm-pagination-container" class="<?php if ($_smarty_tpl->tpl_vars['pagination']->value['next_range']) {?>cm-ajax<?php }?> next-range<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['history_class']->value, ENT_QUOTES, 'UTF-8');?>
" <?php if ($_smarty_tpl->tpl_vars['pagination']->value['next_range']) {?>href="<?php echo htmlspecialchars(fn_url(((string)$_smarty_tpl->tpl_vars['c_url']->value)."&page=".((string)$_smarty_tpl->tpl_vars['pagination']->value['next_range'])), ENT_QUOTES, 'UTF-8');?>
" data-ca-page="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['pagination']->value['next_range'], ENT_QUOTES, 'UTF-8');?>
" data-ca-target-id="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
"<?php }?>>&nbsp;...&nbsp;</a><?php }?><a data-ca-scroll=".cm-pagination-container" class="cm-ajax<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['history_class']->value, ENT_QUOTES, 'UTF-8');?>
" href="<?php echo htmlspecialchars(fn_url(((string)$_smarty_tpl->tpl_vars['c_url']->value)."&page=".((string)$_smarty_tpl->tpl_vars['pagination']->value['total_pages'])), ENT_QUOTES, 'UTF-8');?>
" data-ca-page="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['pagination']->value['total_pages'], ENT_QUOTES, 'UTF-8');?>
" data-ca-target-id="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['pagination']->value['total_pages'], ENT_QUOTES, 'UTF-8');?>
</a>
                <?php }?>
            </li>
            <?php } ?>
            <li class="<?php if (!$_smarty_tpl->tpl_vars['pagination']->value['next_page']) {?>disabled<?php }?><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['history_class']->value, ENT_QUOTES, 'UTF-8');?>
"><a data-ca-scroll=".cm-pagination-container" class="<?php if ($_smarty_tpl->tpl_vars['pagination']->value['next_page']) {?>cm-ajax<?php }?><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['history_class']->value, ENT_QUOTES, 'UTF-8');?>
" <?php if ($_smarty_tpl->tpl_vars['pagination']->value['next_page']) {?>href="<?php echo htmlspecialchars(fn_url(((string)$_smarty_tpl->tpl_vars['c_url']->value)."&page=".((string)$_smarty_tpl->tpl_vars['pagination']->value['next_page'])), ENT_QUOTES, 'UTF-8');?>
" data-ca-page="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['pagination']->value['next_page'], ENT_QUOTES, 'UTF-8');?>
" data-ca-target-id="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
"<?php }?>><?php echo $_smarty_tpl->__("next");?>
&nbsp;&raquo;</a></li>
        <?php }?>
        </ul>
    </div>
        <?php if ($_smarty_tpl->tpl_vars['pagination']->value['total_items']) {?>
            <div class="pagination-desc pull-left">
            <div class="btn-toolbar">
            <span class="pagination-total-items">&nbsp;<?php echo $_smarty_tpl->__("total_items");?>
:&nbsp;<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['pagination']->value['total_items'], ENT_QUOTES, 'UTF-8');?>
&nbsp;/&nbsp;</span>
            <?php $_smarty_tpl->_capture_stack[0][] = array("pagination_list", null, null); ob_start(); ?>
                    <?php $_smarty_tpl->tpl_vars["range_url"] = new Smarty_variable(fn_query_remove($_smarty_tpl->tpl_vars['c_url']->value,"items_per_page"), null, 0);?>
                    <?php  $_smarty_tpl->tpl_vars["step"] = new Smarty_Variable; $_smarty_tpl->tpl_vars["step"]->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['pagination']->value['per_page_range']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars["step"]->key => $_smarty_tpl->tpl_vars["step"]->value) {
$_smarty_tpl->tpl_vars["step"]->_loop = true;
?>
                        <li><a data-ca-scroll=".cm-pagination-container" class="cm-ajax<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['history_class']->value, ENT_QUOTES, 'UTF-8');?>
" href="<?php echo htmlspecialchars(fn_url(((string)$_smarty_tpl->tpl_vars['c_url']->value)."&items_per_page=".((string)$_smarty_tpl->tpl_vars['step']->value)), ENT_QUOTES, 'UTF-8');?>
" data-ca-target-id="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['step']->value, ENT_QUOTES, 'UTF-8');?>
</a></li>
                    <?php } ?>
            <?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>
            <?php echo smarty_function_math(array('equation'=>"rand()",'assign'=>"rnd"),$_smarty_tpl);?>

            <?php echo $_smarty_tpl->getSubTemplate ("common/tools.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('prefix'=>"pagination_".((string)$_smarty_tpl->tpl_vars['rnd']->value),'hide_actions'=>true,'tools_list'=>Smarty::$_smarty_vars['capture']['pagination_list'],'link_text'=>$_smarty_tpl->tpl_vars['pagination']->value['items_per_page'],'override_meta'=>"btn-text",'skip_check_permissions'=>"true",'tool_meta'=>((string)$_smarty_tpl->tpl_vars['pagination_meta']->value)." ",'caret'=>true), 0);?>

            </div></div>
        <?php }?>
    <?php }?>
    </div>
<?php }?>


<?php if (Smarty::$_smarty_vars['capture']['pagination_open']=="Y") {?>
    <!--<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
--></div>
    <?php $_smarty_tpl->_capture_stack[0][] = array("pagination_open", null, null); ob_start(); ?>N<?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>
<?php } elseif (Smarty::$_smarty_vars['capture']['pagination_open']!="Y") {?>
    <?php $_smarty_tpl->_capture_stack[0][] = array("pagination_open", null, null); ob_start(); ?>Y<?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>
<?php }?>
<?php }} ?>
