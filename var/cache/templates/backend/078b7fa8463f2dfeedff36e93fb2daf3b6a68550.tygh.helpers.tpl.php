<?php /* Smarty version Smarty-3.1.18, created on 2014-10-27 16:10:19
         compiled from "/var/www/html/workspace/cscart/design/backend/templates/buttons/helpers.tpl" */ ?>
<?php /*%%SmartyHeaderCode:1932556460544e362b12a6e1-16175586%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '078b7fa8463f2dfeedff36e93fb2daf3b6a68550' => 
    array (
      0 => '/var/www/html/workspace/cscart/design/backend/templates/buttons/helpers.tpl',
      1 => 1413383301,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '1932556460544e362b12a6e1-16175586',
  'function' => 
  array (
    'btn' => 
    array (
      'parameter' => 
      array (
        'text' => '',
        'href' => '',
        'title' => '',
        'onclick' => '',
        'target' => '',
        'class' => '',
        'data' => 
        array (
        ),
        'form' => '',
      ),
      'compiled' => '',
    ),
    'dropdown' => 
    array (
      'parameter' => 
      array (
        'text' => '',
        'title' => '',
        'class' => '',
        'content' => '',
        'icon' => '',
        'no_caret' => false,
        'placement' => 'left',
      ),
      'compiled' => '',
    ),
  ),
  'variables' => 
  array (
    'href' => 0,
    'dispatch' => 0,
    'type' => 0,
    'target' => 0,
    'id' => 0,
    'class' => 0,
    'title' => 0,
    'data' => 0,
    'data_value' => 0,
    'data_name' => 0,
    'onclick' => 0,
    'icon' => 0,
    'icon_first' => 0,
    'text' => 0,
    'process' => 0,
    'form' => 0,
    'click' => 0,
    'target_id' => 0,
    'tag_level' => 0,
    'only_delete' => 0,
    'hide_add' => 0,
    'on_add' => 0,
    'item_id' => 0,
    'hide_clone' => 0,
    'content' => 0,
    'placement' => 0,
    'no_caret' => 0,
  ),
  'has_nocache_code' => 0,
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_544e362b1d3908_51018333',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_544e362b1d3908_51018333')) {function content_544e362b1d3908_51018333($_smarty_tpl) {?><?php
fn_preload_lang_vars(array('delete_selected','delete','tools'));
?>

<?php if (!is_callable('smarty_function_script')) include '/var/www/html/workspace/cscart/app/functions/smarty_plugins/function.script.php';
?><?php if (!function_exists('smarty_template_function_btn')) {
    function smarty_template_function_btn($_smarty_tpl,$params) {
    $saved_tpl_vars = $_smarty_tpl->tpl_vars;
    foreach ($_smarty_tpl->smarty->template_functions['btn']['parameter'] as $key => $value) {$_smarty_tpl->tpl_vars[$key] = new Smarty_variable($value);};
    foreach ($params as $key => $value) {$_smarty_tpl->tpl_vars[$key] = new Smarty_variable($value);}?>
    <?php if (fn_check_view_permissions($_smarty_tpl->tpl_vars['href']->value)&&fn_check_view_permissions($_smarty_tpl->tpl_vars['dispatch']->value)) {?>
    
    <?php if ($_smarty_tpl->tpl_vars['type']->value=="text") {?>
        <a <?php if ($_smarty_tpl->tpl_vars['target']->value) {?>target="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['target']->value, ENT_QUOTES, 'UTF-8');?>
"<?php }?> <?php if ($_smarty_tpl->tpl_vars['href']->value) {?>href="<?php echo htmlspecialchars(fn_url($_smarty_tpl->tpl_vars['href']->value), ENT_QUOTES, 'UTF-8');?>
"<?php }?> <?php if ($_smarty_tpl->tpl_vars['id']->value) {?>id="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
"<?php }?> <?php if ($_smarty_tpl->tpl_vars['class']->value) {?>class="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['class']->value, ENT_QUOTES, 'UTF-8');?>
"<?php }?> <?php if ($_smarty_tpl->tpl_vars['title']->value) {?>title="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['title']->value, ENT_QUOTES, 'UTF-8');?>
"<?php }?>
        <?php if ($_smarty_tpl->tpl_vars['data']->value) {?>
            <?php  $_smarty_tpl->tpl_vars['data_value'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['data_value']->_loop = false;
 $_smarty_tpl->tpl_vars['data_name'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['data']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['data_value']->key => $_smarty_tpl->tpl_vars['data_value']->value) {
$_smarty_tpl->tpl_vars['data_value']->_loop = true;
 $_smarty_tpl->tpl_vars['data_name']->value = $_smarty_tpl->tpl_vars['data_value']->key;
?>
                <?php if ($_smarty_tpl->tpl_vars['data_value']->value) {?>
                    <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['data_name']->value, ENT_QUOTES, 'UTF-8');?>
="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['data_value']->value, ENT_QUOTES, 'UTF-8');?>
"
                <?php }?>
            <?php } ?>
        <?php }?>
        <?php if ($_smarty_tpl->tpl_vars['onclick']->value) {?>onclick="<?php echo $_smarty_tpl->tpl_vars['onclick']->value;?>
; return false;"<?php }?>
        >
        <?php if ($_smarty_tpl->tpl_vars['icon']->value&&$_smarty_tpl->tpl_vars['icon_first']->value) {?><i class="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['icon']->value, ENT_QUOTES, 'UTF-8');?>
"></i><?php }?>
        <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['text']->value, ENT_QUOTES, 'UTF-8');?>

        <?php if ($_smarty_tpl->tpl_vars['icon']->value&&!$_smarty_tpl->tpl_vars['icon_first']->value) {?><i class="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['icon']->value, ENT_QUOTES, 'UTF-8');?>
"></i><?php }?></a>
    <?php }?>

    
    <?php if ($_smarty_tpl->tpl_vars['type']->value=="list") {?>
        <?php if (!$_smarty_tpl->tpl_vars['href']->value&&!$_smarty_tpl->tpl_vars['process']->value) {?>
            <?php $_smarty_tpl->tpl_vars['class'] = new Smarty_variable("cm-process-items cm-submit ".((string)$_smarty_tpl->tpl_vars['class']->value), null, 0);?>
        <?php }?>
        <?php $_smarty_tpl->createLocalArrayVariable('data', null, 0);
$_smarty_tpl->tpl_vars['data']->value['data-ca-target-form'] = $_smarty_tpl->tpl_vars['form']->value;?>
        <?php $_smarty_tpl->createLocalArrayVariable('data', null, 0);
$_smarty_tpl->tpl_vars['data']->value['data-ca-dispatch'] = $_smarty_tpl->tpl_vars['dispatch']->value;?>
        <?php smarty_template_function_btn($_smarty_tpl,array('type'=>"text",'target'=>$_smarty_tpl->tpl_vars['target']->value,'href'=>$_smarty_tpl->tpl_vars['href']->value,'data'=>$_smarty_tpl->tpl_vars['data']->value,'class'=>$_smarty_tpl->tpl_vars['class']->value,'onclick'=>$_smarty_tpl->tpl_vars['onclick']->value,'text'=>$_smarty_tpl->tpl_vars['text']->value));?>

    <?php }?>

    
    <?php if ($_smarty_tpl->tpl_vars['type']->value=="delete_selected") {?>
        <?php if ($_smarty_tpl->tpl_vars['icon']->value) {?>
            <?php $_smarty_tpl->tpl_vars['class'] = new Smarty_variable("btn", null, 0);?>
            <?php $_smarty_tpl->tpl_vars['text'] = new Smarty_variable(" ", null, 0);?>
        <?php }?>
        <?php $_smarty_tpl->createLocalArrayVariable('data', null, 0);
$_smarty_tpl->tpl_vars['data']->value['data-ca-target-form'] = $_smarty_tpl->tpl_vars['form']->value;?>
        <?php $_smarty_tpl->createLocalArrayVariable('data', null, 0);
$_smarty_tpl->tpl_vars['data']->value['data-ca-dispatch'] = $_smarty_tpl->tpl_vars['dispatch']->value;?>
        <?php smarty_template_function_btn($_smarty_tpl,array('type'=>"text",'target'=>$_smarty_tpl->tpl_vars['target']->value,'href'=>$_smarty_tpl->tpl_vars['href']->value,'data'=>$_smarty_tpl->tpl_vars['data']->value,'class'=>"cm-process-items cm-submit cm-confirm ".((string)$_smarty_tpl->tpl_vars['class']->value),'click'=>$_smarty_tpl->tpl_vars['click']->value,'text'=>(($tmp = @$_smarty_tpl->tpl_vars['text']->value)===null||$tmp==='' ? $_smarty_tpl->__("delete_selected") : $tmp)));?>

    <?php }?>

    
    <?php if ($_smarty_tpl->tpl_vars['type']->value=="delete") {?>
        <?php $_smarty_tpl->createLocalArrayVariable('data', null, 0);
$_smarty_tpl->tpl_vars['data']->value['data-ca-target-form'] = $_smarty_tpl->tpl_vars['form']->value;?>
        <?php $_smarty_tpl->createLocalArrayVariable('data', null, 0);
$_smarty_tpl->tpl_vars['data']->value['data-ca-dispatch'] = $_smarty_tpl->tpl_vars['dispatch']->value;?>
        <?php smarty_template_function_btn($_smarty_tpl,array('type'=>"text",'target'=>$_smarty_tpl->tpl_vars['target']->value,'href'=>$_smarty_tpl->tpl_vars['href']->value,'data'=>$_smarty_tpl->tpl_vars['data']->value,'class'=>((string)$_smarty_tpl->tpl_vars['class']->value),'click'=>$_smarty_tpl->tpl_vars['click']->value,'text'=>(($tmp = @$_smarty_tpl->tpl_vars['text']->value)===null||$tmp==='' ? $_smarty_tpl->__("delete") : $tmp)));?>

    <?php }?>

    
    <?php if ($_smarty_tpl->tpl_vars['type']->value=="dialog") {?>
        <?php smarty_template_function_btn($_smarty_tpl,array('type'=>"text",'text'=>$_smarty_tpl->tpl_vars['text']->value,'class'=>"cm-dialog-opener ".((string)$_smarty_tpl->tpl_vars['class']->value),'href'=>$_smarty_tpl->tpl_vars['href']->value,'id'=>$_smarty_tpl->tpl_vars['id']->value,'title'=>$_smarty_tpl->tpl_vars['title']->value,'data'=>array('data-ca-target-id'=>$_smarty_tpl->tpl_vars['target_id']->value,'data-ca-target-form'=>$_smarty_tpl->tpl_vars['form']->value)));?>

    <?php }?>

    
    <?php if ($_smarty_tpl->tpl_vars['type']->value=="multiple") {?>
        <?php echo smarty_function_script(array('src'=>"js/tygh/node_cloning.js"),$_smarty_tpl);?>


        <?php $_smarty_tpl->tpl_vars["tag_level"] = new Smarty_variable((($tmp = @$_smarty_tpl->tpl_vars['tag_level']->value)===null||$tmp==='' ? "1" : $tmp), null, 0);?>
        <?php if ($_smarty_tpl->tpl_vars['only_delete']->value!="Y") {?><?php if (!$_smarty_tpl->tpl_vars['hide_add']->value) {?><li><?php smarty_template_function_btn($_smarty_tpl,array('type'=>"text",'onclick'=>"Tygh."."$"."('#box_' + this.id).cloneNode(".((string)$_smarty_tpl->tpl_vars['tag_level']->value)."); ".((string)$_smarty_tpl->tpl_vars['on_add']->value),'id'=>$_smarty_tpl->tpl_vars['item_id']->value));?>
</li><?php }?><?php if (!$_smarty_tpl->tpl_vars['hide_clone']->value) {?><li><?php smarty_template_function_btn($_smarty_tpl,array('type'=>"text",'onclick'=>"Tygh."."$"."('#box_' + this.id).cloneNode(".((string)$_smarty_tpl->tpl_vars['tag_level']->value).", true);",'id'=>$_smarty_tpl->tpl_vars['item_id']->value));?>
</li><?php }?><?php }?><li><?php smarty_template_function_btn($_smarty_tpl,array('type'=>"text",'only_delete'=>$_smarty_tpl->tpl_vars['only_delete']->value,'class'=>"cm-delete-row"));?>
</li>
    <?php }?>

    
    <?php if ($_smarty_tpl->tpl_vars['type']->value=="add") {?>
        <?php smarty_template_function_btn($_smarty_tpl,array('type'=>"text",'title'=>$_smarty_tpl->tpl_vars['title']->value,'class'=>"cm-tooltip btn",'icon'=>"icon-plus",'href'=>$_smarty_tpl->tpl_vars['href']->value));?>

    <?php }?>

    
    <?php if ($_smarty_tpl->tpl_vars['type']->value=="text_add") {?>
        <?php smarty_template_function_btn($_smarty_tpl,array('type'=>"text",'text'=>$_smarty_tpl->tpl_vars['text']->value,'class'=>"btn btn-primary",'icon'=>"icon-plus icon-white",'icon_first'=>true,'href'=>$_smarty_tpl->tpl_vars['href']->value));?>

    <?php }?>

    <?php }?>
<?php $_smarty_tpl->tpl_vars = $saved_tpl_vars;
foreach (Smarty::$global_tpl_vars as $key => $value) if(!isset($_smarty_tpl->tpl_vars[$key])) $_smarty_tpl->tpl_vars[$key] = $value;}}?>



<?php if (!is_callable('smarty_modifier_replace')) include '/var/www/html/workspace/cscart/app/lib/other/smarty/plugins/modifier.replace.php';
?><?php if (!function_exists('smarty_template_function_dropdown')) {
    function smarty_template_function_dropdown($_smarty_tpl,$params) {
    $saved_tpl_vars = $_smarty_tpl->tpl_vars;
    foreach ($_smarty_tpl->smarty->template_functions['dropdown']['parameter'] as $key => $value) {$_smarty_tpl->tpl_vars[$key] = new Smarty_variable($value);};
    foreach ($params as $key => $value) {$_smarty_tpl->tpl_vars[$key] = new Smarty_variable($value);}?>
    <?php if (trim(smarty_modifier_replace(strip_tags($_smarty_tpl->tpl_vars['content']->value),"&nbsp;",''))!='') {?>
        <div class="btn-group<?php if ($_smarty_tpl->tpl_vars['placement']->value=="left") {?> dropleft<?php }?> <?php echo htmlspecialchars($_smarty_tpl->tpl_vars['class']->value, ENT_QUOTES, 'UTF-8');?>
" <?php if ($_smarty_tpl->tpl_vars['id']->value) {?>id="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
"<?php }?>>
            <a class="btn dropdown-toggle" data-toggle="dropdown" <?php if ($_smarty_tpl->tpl_vars['title']->value) {?>title="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['title']->value, ENT_QUOTES, 'UTF-8');?>
"<?php }?>>
                <i class="<?php echo htmlspecialchars((($tmp = @$_smarty_tpl->tpl_vars['icon']->value)===null||$tmp==='' ? "icon-cog" : $tmp), ENT_QUOTES, 'UTF-8');?>
"></i>
                <?php if ($_smarty_tpl->tpl_vars['text']->value) {?>
                    <?php echo (($tmp = @$_smarty_tpl->tpl_vars['text']->value)===null||$tmp==='' ? $_smarty_tpl->__("tools") : $tmp);?>

                <?php }?>
                <?php if (!$_smarty_tpl->tpl_vars['no_caret']->value) {?>
                    <span class="caret"></span>
                <?php }?>
            </a>
            <ul class="dropdown-menu">
                <?php echo $_smarty_tpl->tpl_vars['content']->value;?>

            </ul>
        </div>
    <?php }?>
<?php $_smarty_tpl->tpl_vars = $saved_tpl_vars;
foreach (Smarty::$global_tpl_vars as $key => $value) if(!isset($_smarty_tpl->tpl_vars[$key])) $_smarty_tpl->tpl_vars[$key] = $value;}}?>

<?php }} ?>
