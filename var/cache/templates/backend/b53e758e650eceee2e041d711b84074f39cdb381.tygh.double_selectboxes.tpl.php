<?php /* Smarty version Smarty-3.1.18, created on 2014-11-24 17:22:27
         compiled from "/var/www/html/workspace/cscart/design/backend/templates/common/double_selectboxes.tpl" */ ?>
<?php /*%%SmartyHeaderCode:49974321454733f23ba0b77-72187461%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'b53e758e650eceee2e041d711b84074f39cdb381' => 
    array (
      0 => '/var/www/html/workspace/cscart/design/backend/templates/common/double_selectboxes.tpl',
      1 => 1413383301,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '49974321454733f23ba0b77-72187461',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'first_name' => 0,
    'second_name' => 0,
    'title' => 0,
    'class_name' => 0,
    'first_id' => 0,
    'required' => 0,
    'first_data' => 0,
    'key' => 0,
    'value' => 0,
    'sortable' => 0,
    'second_id' => 0,
    'second_data' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_54733f23bc3681_37959495',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54733f23bc3681_37959495')) {function content_54733f23bc3681_37959495($_smarty_tpl) {?><?php if (!is_callable('smarty_block_notes')) include '/var/www/html/workspace/cscart/app/functions/smarty_plugins/block.notes.php';
?><?php
fn_preload_lang_vars(array('multiple_selectbox_notice'));
?>
<?php $_smarty_tpl->tpl_vars["first_id"] = new Smarty_variable(md5($_smarty_tpl->tpl_vars['first_name']->value), null, 0);?>
<?php $_smarty_tpl->tpl_vars["second_id"] = new Smarty_variable(md5($_smarty_tpl->tpl_vars['second_name']->value), null, 0);?>

<?php $_smarty_tpl->smarty->_tag_stack[] = array('notes', array('unique'=>true)); $_block_repeat=true; echo smarty_block_notes(array('unique'=>true), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

    <?php echo $_smarty_tpl->__("multiple_selectbox_notice");?>

<?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_notes(array('unique'=>true), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>


<?php echo $_smarty_tpl->getSubTemplate ("common/subheader.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('title'=>$_smarty_tpl->tpl_vars['title']->value), 0);?>

<table class="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['class_name']->value, ENT_QUOTES, 'UTF-8');?>
" width="100%">
<tr>
    <td width="48%">
        <label for="id_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['first_id']->value, ENT_QUOTES, 'UTF-8');?>
" class="<?php if ($_smarty_tpl->tpl_vars['required']->value) {?>cm-required<?php }?> cm-all hidden"></label>
        <select name="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['first_name']->value, ENT_QUOTES, 'UTF-8');?>
[]" id="id_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['first_id']->value, ENT_QUOTES, 'UTF-8');?>
" size="10" value="" multiple="multiple" class="input-full">
            <?php  $_smarty_tpl->tpl_vars['value'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['value']->_loop = false;
 $_smarty_tpl->tpl_vars['key'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['first_data']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['value']->key => $_smarty_tpl->tpl_vars['value']->value) {
$_smarty_tpl->tpl_vars['value']->_loop = true;
 $_smarty_tpl->tpl_vars['key']->value = $_smarty_tpl->tpl_vars['value']->key;
?>
                <option value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['key']->value, ENT_QUOTES, 'UTF-8');?>
"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['value']->value, ENT_QUOTES, 'UTF-8');?>
</option>
            <?php } ?>
        </select>

        <?php if ($_smarty_tpl->tpl_vars['sortable']->value) {?>
        <p>
            <span onclick="Tygh.$('#id_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['first_id']->value, ENT_QUOTES, 'UTF-8');?>
').swapOptions('up');" class="icon-chevron-up hand" ></span>
            <span onclick="Tygh.$('#id_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['first_id']->value, ENT_QUOTES, 'UTF-8');?>
').swapOptions('down');" class="icon-chevron-down hand" ></span>
        </p>
        <?php }?>
    </td>
    <td class="center chevron-icons" width="4%">
            <span onclick="Tygh.$('#id_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['second_id']->value, ENT_QUOTES, 'UTF-8');?>
').moveOptions('#id_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['first_id']->value, ENT_QUOTES, 'UTF-8');?>
');" class="icon-chevron-left hand clear"></span><br/>
            <span onclick="Tygh.$('#id_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['first_id']->value, ENT_QUOTES, 'UTF-8');?>
').moveOptions('#id_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['second_id']->value, ENT_QUOTES, 'UTF-8');?>
');" class="icon-chevron-right hand"></span>
    </td>
    <td width="48%" valign="top">
        <p><select name="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['second_name']->value, ENT_QUOTES, 'UTF-8');?>
" id="id_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['second_id']->value, ENT_QUOTES, 'UTF-8');?>
" size="10" value="" multiple="multiple" class="input-full">
            <?php  $_smarty_tpl->tpl_vars['value'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['value']->_loop = false;
 $_smarty_tpl->tpl_vars['key'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['second_data']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['value']->key => $_smarty_tpl->tpl_vars['value']->value) {
$_smarty_tpl->tpl_vars['value']->_loop = true;
 $_smarty_tpl->tpl_vars['key']->value = $_smarty_tpl->tpl_vars['value']->key;
?>
                <option value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['key']->value, ENT_QUOTES, 'UTF-8');?>
"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['value']->value, ENT_QUOTES, 'UTF-8');?>
</option>
            <?php } ?>
        </select></p>
    </td>
</tr>
</table><?php }} ?>
