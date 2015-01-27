<?php /* Smarty version Smarty-3.1.18, created on 2014-11-24 17:22:57
         compiled from "/var/www/html/workspace/cscart/design/backend/templates/views/product_options/update.tpl" */ ?>
<?php /*%%SmartyHeaderCode:117607957854733f410ad2a1-37402697%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'e03897e228d6836ccac102f94627da4fa32e4285' => 
    array (
      0 => '/var/www/html/workspace/cscart/design/backend/templates/views/product_options/update.tpl',
      1 => 1413383305,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '117607957854733f410ad2a1-37402697',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'option_data' => 0,
    'runtime' => 0,
    'shared_product' => 0,
    'id' => 0,
    'allow_save' => 0,
    'cm_no_hide_input' => 0,
    'company_id' => 0,
    'product_company_id' => 0,
    'object' => 0,
    'disable_company_picker' => 0,
    'zero_company_id_name_lang_var' => 0,
    'num' => 0,
    'vr' => 0,
    'primary_currency' => 0,
    'currencies' => 0,
    'show_update_for_all' => 0,
    'settings' => 0,
    'hide_first_button' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_54733f41245434_52404884',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54733f41245434_52404884')) {function content_54733f41245434_52404884($_smarty_tpl) {?><?php if (!is_callable('smarty_block_hook')) include '/var/www/html/workspace/cscart/app/functions/smarty_plugins/block.hook.php';
if (!is_callable('smarty_function_math')) include '/var/www/html/workspace/cscart/app/lib/other/smarty/plugins/function.math.php';
?><?php
fn_preload_lang_vars(array('general','variants','name','position','inventory','tt_views_product_options_update_inventory','type','description','comment','comment_hint','required','missing_variants_handling','display_message','hide_option_completely','regexp','tt_views_product_options_update_regexp','regexp_hint','inner_hint','tt_views_product_options_update_inner_hint','incorrect_filling_message','tt_views_product_options_update_incorrect_filling_message','allowed_extensions','allowed_extensions_hint','max_uploading_file_size','max_uploading_file_size_hint','multiupload','position_short','name','modifier','type','weight_modifier','type','status','expand_collapse_list','expand_collapse_list','expand_collapse_list','expand_collapse_list','expand_collapse_list','expand_collapse_list','expand_collapse_list','expand_collapse_list','extra','icon','expand_collapse_list','expand_collapse_list','expand_collapse_list','expand_collapse_list','extra','icon'));
?>
<?php if ($_smarty_tpl->tpl_vars['option_data']->value['option_id']) {?>
    <?php $_smarty_tpl->tpl_vars["id"] = new Smarty_variable($_smarty_tpl->tpl_vars['option_data']->value['option_id'], null, 0);?>
<?php } else { ?>
    <?php $_smarty_tpl->tpl_vars["id"] = new Smarty_variable(0, null, 0);?>
<?php }?>

<?php if (fn_allowed_for("ULTIMATE")) {?>
    <?php if (!$_smarty_tpl->tpl_vars['runtime']->value['company_id']&&$_smarty_tpl->tpl_vars['shared_product']->value=="Y") {?>
        <?php $_smarty_tpl->tpl_vars["show_update_for_all"] = new Smarty_variable(true, null, 0);?>
    <?php }?>

    <?php if ($_smarty_tpl->tpl_vars['runtime']->value['company_id']&&$_smarty_tpl->tpl_vars['shared_product']->value=="Y") {?>
        <?php $_smarty_tpl->tpl_vars["cm_no_hide_input"] = new Smarty_variable("cm-no-hide-input", null, 0);?>
    <?php }?>
<?php }?>

<?php $_smarty_tpl->tpl_vars["allow_save"] = new Smarty_variable(fn_allow_save_object($_smarty_tpl->tpl_vars['option_data']->value,"product_options"), null, 0);?>

<div id="content_group_product_option_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
">

<form action="<?php echo htmlspecialchars(fn_url(''), ENT_QUOTES, 'UTF-8');?>
" method="post" name="option_form_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
" class="form-horizontal form-edit form-highlight cm-disable-empty-files <?php if (!$_smarty_tpl->tpl_vars['allow_save']->value) {?>cm-hide-inputs<?php }?>" enctype="multipart/form-data">
<input type="hidden" name="option_id" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
" class="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['cm_no_hide_input']->value, ENT_QUOTES, 'UTF-8');?>
" />

<?php if (fn_allowed_for("MULTIVENDOR")) {?>
    <?php if (!$_smarty_tpl->tpl_vars['allow_save']->value) {?>
        <?php $_smarty_tpl->tpl_vars["disable_company_picker"] = new Smarty_variable(true, null, 0);?>
    <?php }?>
<?php }?>

<?php if ($_REQUEST['product_id']) {?>
    <input class="cm-no-hide-input" type="hidden" name="option_data[product_id]" value="<?php echo htmlspecialchars($_REQUEST['product_id'], ENT_QUOTES, 'UTF-8');?>
" />
    <?php if (fn_allowed_for("ULTIMATE")) {?>
        <?php $_smarty_tpl->tpl_vars["disable_company_picker"] = new Smarty_variable(true, null, 0);?>
        <?php if (!$_smarty_tpl->tpl_vars['company_id']->value) {?>
            <?php $_smarty_tpl->tpl_vars["company_id"] = new Smarty_variable($_smarty_tpl->tpl_vars['product_company_id']->value, null, 0);?>
        <?php }?>
    <?php }?>
<?php }?>

<div class="tabs cm-j-tabs">
    <ul class="nav nav-tabs">
        <li id="tab_option_details_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
" class="cm-js active"><a><?php echo $_smarty_tpl->__("general");?>
</a></li>
        <?php if ($_smarty_tpl->tpl_vars['option_data']->value['option_type']=="S"||$_smarty_tpl->tpl_vars['option_data']->value['option_type']=="R"||$_smarty_tpl->tpl_vars['option_data']->value['option_type']=="C"||!$_smarty_tpl->tpl_vars['option_data']->value) {?>
            <li id="tab_option_variants_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
" class="cm-js"><a><?php echo $_smarty_tpl->__("variants");?>
</a></li>
        <?php }?>
    </ul>
</div>
<div class="cm-tabs-content" id="tabs_content_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
">
    <div id="content_tab_option_details_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
">
    <fieldset>
        <div class="control-group">
            <input class="cm-no-hide-input" type="hidden" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['object']->value, ENT_QUOTES, 'UTF-8');?>
" name="object">
            <label for="elm_option_name_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
" class="control-label cm-required"><?php echo $_smarty_tpl->__("name");?>
</label>
            <div class="controls">
            <input class="span9" type="text" name="option_data[option_name]" id="elm_option_name_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['option_data']->value['option_name'], ENT_QUOTES, 'UTF-8');?>
"/>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="elm_position_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
"><?php echo $_smarty_tpl->__("position");?>
</label>
            <div class="controls">
            <input type="text" name="option_data[position]" id="elm_position_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['option_data']->value['position'], ENT_QUOTES, 'UTF-8');?>
" size="3" class="input-small" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="elm_inventory_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
"><?php echo $_smarty_tpl->__("inventory");?>
<?php echo $_smarty_tpl->getSubTemplate ("common/tooltip.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('tooltip'=>__("tt_views_product_options_update_inventory")), 0);?>
</label>
            <input type="hidden" name="option_data[inventory]" value="N" />
            <div class="controls">
            <?php if (strpos("SRC",$_smarty_tpl->tpl_vars['option_data']->value['option_type'])!==false) {?>
            <label class="checkbox">
                <input type="checkbox" name="option_data[inventory]" id="elm_inventory_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
" value="Y" <?php if ($_smarty_tpl->tpl_vars['option_data']->value['inventory']=="Y") {?>checked="checked"<?php }?>/>
            </label>
            <?php } else { ?>
                <p>-</p>
            <?php }?>
            </div>
        </div>

        <?php if (fn_allowed_for("MULTIVENDOR")) {?>
            <?php $_smarty_tpl->tpl_vars["zero_company_id_name_lang_var"] = new Smarty_variable("none", null, 0);?>
        <?php }?>
        <?php echo $_smarty_tpl->getSubTemplate ("views/companies/components/company_field.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('name'=>"option_data[company_id]",'id'=>"elm_option_data_".((string)$_smarty_tpl->tpl_vars['id']->value),'disable_company_picker'=>$_smarty_tpl->tpl_vars['disable_company_picker']->value,'selected'=>(($tmp = @$_smarty_tpl->tpl_vars['option_data']->value['company_id'])===null||$tmp==='' ? $_smarty_tpl->tpl_vars['company_id']->value : $tmp),'zero_company_id_name_lang_var'=>$_smarty_tpl->tpl_vars['zero_company_id_name_lang_var']->value), 0);?>


        <?php if (fn_allowed_for("ULTIMATE")&&$_smarty_tpl->tpl_vars['runtime']->value['company_id']&&$_smarty_tpl->tpl_vars['shared_product']->value=="Y") {?>
            <input type="hidden" name="option_data[option_type]" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['option_data']->value['option_type'], ENT_QUOTES, 'UTF-8');?>
" class="cm-no-hide-input" />
        <?php }?>
        <div class="control-group">
            <label class="control-label" for="elm_option_type_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
"><?php echo $_smarty_tpl->__("type");?>
</label>
            <div class="controls">
            <?php echo $_smarty_tpl->getSubTemplate ("views/product_options/components/option_types.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('name'=>"option_data[option_type]",'value'=>$_smarty_tpl->tpl_vars['option_data']->value['option_type'],'display'=>"select",'tag_id'=>"elm_option_type_".((string)$_smarty_tpl->tpl_vars['id']->value),'check'=>true), 0);?>

            </div>
        </div>
        
        <div class="control-group">
            <label class="control-label" for="elm_option_description_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
"><?php echo $_smarty_tpl->__("description");?>
</label>
            <div class="controls">
            <textarea id="elm_option_description_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
" name="option_data[description]" cols="55" rows="8" class="cm-wysiwyg span9"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['option_data']->value['description'], ENT_QUOTES, 'UTF-8');?>
</textarea>
            </div>
        </div>
        
        <div class="control-group">
            <label class="control-label" for="elm_option_comment_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
"><?php echo $_smarty_tpl->__("comment");?>
</label>
            <div class="controls">
            <input type="text" name="option_data[comment]" id="elm_option_comment_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['option_data']->value['comment'], ENT_QUOTES, 'UTF-8');?>
" class="span9" />
            <p class="description"><?php echo $_smarty_tpl->__("comment_hint");?>
</p>
            </div>
        </div>
        
        <div class="control-group">
            <label class="control-label" for="elm_option_file_required_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
"><?php echo $_smarty_tpl->__("required");?>
</label>
            <div class="controls">
            <label class="checkbox">
            <input type="hidden" name="option_data[required]" value="N" /><input type="checkbox" id="elm_option_file_required_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
" name="option_data[required]" value="Y" <?php if ($_smarty_tpl->tpl_vars['option_data']->value['required']=="Y") {?>checked="checked"<?php }?>  />
            </label>
            </div>
        </div>
        
        <?php if (!$_smarty_tpl->tpl_vars['option_data']->value['option_type']||strpos("SRC",$_smarty_tpl->tpl_vars['option_data']->value['option_type'])!==false) {?>
            <div class="control-group">
                <label class="control-label" for="elm_option_missing_variants_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
"><?php echo $_smarty_tpl->__("missing_variants_handling");?>
</label>
                <div class="controls">
                <?php if (strpos("SRC",$_smarty_tpl->tpl_vars['option_data']->value['option_type'])!==false) {?>
                    <select id="elm_option_missing_variants_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
" name="option_data[missing_variants_handling]">
                        <option value="M" <?php if ($_smarty_tpl->tpl_vars['option_data']->value['missing_variants_handling']=="M") {?>selected="selected"<?php }?>><?php echo $_smarty_tpl->__("display_message");?>
</option>
                        <option value="H" <?php if ($_smarty_tpl->tpl_vars['option_data']->value['missing_variants_handling']=="H") {?>selected="selected"<?php }?>><?php echo $_smarty_tpl->__("hide_option_completely");?>
</option>
                    </select>
                <?php } else { ?>
                <p>-</p>
                <?php }?>
                </div>
            </div>
        <?php }?>
        
        <div id="extra_options_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
" <?php if ($_smarty_tpl->tpl_vars['option_data']->value['option_type']!="I"&&$_smarty_tpl->tpl_vars['option_data']->value['option_type']!="T") {?>class="hidden"<?php }?>>
            <div class="control-group">
                <label class="control-label" for="elm_option_regexp_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
"><?php echo $_smarty_tpl->__("regexp");?>
<?php echo $_smarty_tpl->getSubTemplate ("common/tooltip.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('tooltip'=>__("tt_views_product_options_update_regexp")), 0);?>
</label>
                <div class="controls">
                <input type="text" name="option_data[regexp]" id="elm_option_regexp_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
" value="<?php echo $_smarty_tpl->tpl_vars['option_data']->value['regexp'];?>
" class="span9" />
                <p class="description"><?php echo $_smarty_tpl->__("regexp_hint");?>
</p>
                </div>
            </div>
            
            <div class="control-group">
                <label class="control-label" for="elm_option_inner_hint_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
"><?php echo $_smarty_tpl->__("inner_hint");?>
<?php echo $_smarty_tpl->getSubTemplate ("common/tooltip.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('tooltip'=>__("tt_views_product_options_update_inner_hint")), 0);?>
</label>
                <div class="controls">
                <input type="text" name="option_data[inner_hint]" id="elm_option_inner_hint_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['option_data']->value['inner_hint'], ENT_QUOTES, 'UTF-8');?>
" class="span9" />
                </div>
            </div>
            
            <div class="control-group">
                <label class="control-label" for="elm_option_incorrect_message_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
"><?php echo $_smarty_tpl->__("incorrect_filling_message");?>
<?php echo $_smarty_tpl->getSubTemplate ("common/tooltip.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('tooltip'=>__("tt_views_product_options_update_incorrect_filling_message")), 0);?>
</label>
                <div class="controls">
                <input type="text" name="option_data[incorrect_message]" id="elm_option_incorrect_message_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['option_data']->value['incorrect_message'], ENT_QUOTES, 'UTF-8');?>
" class="span9" />
            </div>
            </div>
        </div>
        
        <div id="file_options_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
" <?php if ($_smarty_tpl->tpl_vars['option_data']->value['option_type']!="F") {?>class="hidden"<?php }?>>
            <div class="control-group">
                <label class="control-label" for="elm_option_allowed_extensions_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
"><?php echo $_smarty_tpl->__("allowed_extensions");?>
</label>
                <div class="controls">
                <input type="text" name="option_data[allowed_extensions]" id="elm_option_allowed_extensions_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['option_data']->value['allowed_extensions'], ENT_QUOTES, 'UTF-8');?>
" class="span9" />
                <p class="description"><?php echo $_smarty_tpl->__("allowed_extensions_hint");?>
</p>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="elm_option_max_uploading_file_size_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
"><?php echo $_smarty_tpl->__("max_uploading_file_size");?>
</label>
                <div class="controls">
                <input type="text" name="option_data[max_file_size]" id="elm_option_max_uploading_file_size_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['option_data']->value['max_file_size'], ENT_QUOTES, 'UTF-8');?>
" class="span9" />
                <p class="description"><?php echo $_smarty_tpl->__("max_uploading_file_size_hint");?>
</p>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="elm_option_multiupload_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
"><?php echo $_smarty_tpl->__("multiupload");?>
</label>
                <div class="controls">
                <label class="checkbox">
                <input type="hidden" name="option_data[multiupload]" value="N" /><input type="checkbox" id="elm_option_multiupload_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
" name="option_data[multiupload]" value="Y" <?php if ($_smarty_tpl->tpl_vars['option_data']->value['multiupload']=="Y") {?>checked="checked"<?php }?>/>
                </label>
                </div>
            </div>
        </div>
        
        <?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"product_options:properties")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"product_options:properties"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

        <?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"product_options:properties"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

    </fieldset>
    <!--content_tab_option_details_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
--></div>

     <div class="hidden" id="content_tab_option_variants_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
">
     <fieldset>
        <table class="table table-middle">
        <thead>
        <tr class="first-sibling">
            <th class="cm-non-cb<?php if ($_smarty_tpl->tpl_vars['option_data']->value['option_type']=="C") {?> hidden<?php }?>"><?php echo $_smarty_tpl->__("position_short");?>
</th>
            <th class="cm-non-cb<?php if ($_smarty_tpl->tpl_vars['option_data']->value['option_type']=="C") {?> hidden<?php }?>"><?php echo $_smarty_tpl->__("name");?>
</th>
            <th><?php echo $_smarty_tpl->__("modifier");?>
&nbsp;/&nbsp;<?php echo $_smarty_tpl->__("type");?>
</th>
            <th><?php echo $_smarty_tpl->__("weight_modifier");?>
&nbsp;/&nbsp;<?php echo $_smarty_tpl->__("type");?>
</th>
            <th class="cm-non-cb<?php if ($_smarty_tpl->tpl_vars['option_data']->value['option_type']=="C") {?> hidden<?php }?>"><?php echo $_smarty_tpl->__("status");?>
</th>
            <th>
                <div id="on_st_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
" alt="<?php echo $_smarty_tpl->__("expand_collapse_list");?>
" title="<?php echo $_smarty_tpl->__("expand_collapse_list");?>
" class="hand cm-combinations-options-<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
 exicon-expand"></div><div id="off_st_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
" alt="<?php echo $_smarty_tpl->__("expand_collapse_list");?>
" title="<?php echo $_smarty_tpl->__("expand_collapse_list");?>
" class="hand hidden cm-combinations-options-<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
 exicon-collapse"></div>
            </th>
            <th class="cm-non-cb<?php if ($_smarty_tpl->tpl_vars['option_data']->value['option_type']=="C") {?> hidden<?php }?>">&nbsp;</th>
        </tr>
        </thead>
        <?php  $_smarty_tpl->tpl_vars["vr"] = new Smarty_Variable; $_smarty_tpl->tpl_vars["vr"]->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['option_data']->value['variants']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']["fe_v"]['iteration']=0;
foreach ($_from as $_smarty_tpl->tpl_vars["vr"]->key => $_smarty_tpl->tpl_vars["vr"]->value) {
$_smarty_tpl->tpl_vars["vr"]->_loop = true;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']["fe_v"]['iteration']++;
?>
        <?php $_smarty_tpl->tpl_vars["num"] = new Smarty_variable($_smarty_tpl->getVariable('smarty')->value['foreach']['fe_v']['iteration'], null, 0);?>
        <tbody class="hover cm-row-item" id="option_variants_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['num']->value, ENT_QUOTES, 'UTF-8');?>
">
        <tr>
            <td class="cm-non-cb<?php if ($_smarty_tpl->tpl_vars['option_data']->value['option_type']=="C") {?> hidden<?php }?>">
                <input type="text" name="option_data[variants][<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['num']->value, ENT_QUOTES, 'UTF-8');?>
][position]" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['vr']->value['position'], ENT_QUOTES, 'UTF-8');?>
" size="3" class="input-micro" /></td>
            <td class="cm-non-cb<?php if ($_smarty_tpl->tpl_vars['option_data']->value['option_type']=="C") {?> hidden<?php }?>">
                <input type="text" name="option_data[variants][<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['num']->value, ENT_QUOTES, 'UTF-8');?>
][variant_name]" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['vr']->value['variant_name'], ENT_QUOTES, 'UTF-8');?>
" class="input-medium" /></td>
            <td class="nowrap <?php if ($_smarty_tpl->tpl_vars['runtime']->value['company_id']&&$_smarty_tpl->tpl_vars['shared_product']->value=="Y") {?> cm-no-hide-input<?php }?>">
                <input type="text" name="option_data[variants][<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['num']->value, ENT_QUOTES, 'UTF-8');?>
][modifier]" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['vr']->value['modifier'], ENT_QUOTES, 'UTF-8');?>
" size="5" class="input-mini" />&nbsp;/&nbsp;<select class="input-mini" name="option_data[variants][<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['num']->value, ENT_QUOTES, 'UTF-8');?>
][modifier_type]">
                    <option value="A" <?php if ($_smarty_tpl->tpl_vars['vr']->value['modifier_type']=="A") {?>selected="selected"<?php }?>><?php echo $_smarty_tpl->tpl_vars['currencies']->value[$_smarty_tpl->tpl_vars['primary_currency']->value]['symbol'];?>
</option>
                    <option value="P" <?php if ($_smarty_tpl->tpl_vars['vr']->value['modifier_type']=="P") {?>selected="selected"<?php }?>>%</option>
                </select>
                <?php echo $_smarty_tpl->getSubTemplate ("buttons/update_for_all.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('display'=>$_smarty_tpl->tpl_vars['show_update_for_all']->value,'object_id'=>$_smarty_tpl->tpl_vars['vr']->value['variant_id'],'name'=>"update_all_vendors[".((string)$_smarty_tpl->tpl_vars['num']->value)."]"), 0);?>

            </td>
            <td class="nowrap">
                <input type="text" name="option_data[variants][<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['num']->value, ENT_QUOTES, 'UTF-8');?>
][weight_modifier]" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['vr']->value['weight_modifier'], ENT_QUOTES, 'UTF-8');?>
" size="5" class="input-mini" />&nbsp;/&nbsp;<select class="input-mini" name="option_data[variants][<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['num']->value, ENT_QUOTES, 'UTF-8');?>
][weight_modifier_type]">
                    <option value="A" <?php if ($_smarty_tpl->tpl_vars['vr']->value['weight_modifier_type']=="A") {?>selected="selected"<?php }?>><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['settings']->value['General']['weight_symbol'], ENT_QUOTES, 'UTF-8');?>
</option>
                    <option value="P" <?php if ($_smarty_tpl->tpl_vars['vr']->value['weight_modifier_type']=="P") {?>selected="selected"<?php }?>>%</option>
                </select>
            </td>
            <td class="cm-non-cb<?php if ($_smarty_tpl->tpl_vars['option_data']->value['option_type']=="C") {?> hidden<?php }?>">
                <?php echo $_smarty_tpl->getSubTemplate ("common/select_status.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('input_name'=>"option_data[variants][".((string)$_smarty_tpl->tpl_vars['num']->value)."][status]",'display'=>"select",'obj'=>$_smarty_tpl->tpl_vars['vr']->value,'meta'=>"input-small"), 0);?>
</td>
            <td class="nowrap">
                <span id="on_extra_option_variants_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['num']->value, ENT_QUOTES, 'UTF-8');?>
" alt="<?php echo $_smarty_tpl->__("expand_collapse_list");?>
" title="<?php echo $_smarty_tpl->__("expand_collapse_list");?>
" class="hand cm-combination-options-<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
"><span class="exicon-expand"></span></span>
                <span id="off_extra_option_variants_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['num']->value, ENT_QUOTES, 'UTF-8');?>
" alt="<?php echo $_smarty_tpl->__("expand_collapse_list");?>
" title="<?php echo $_smarty_tpl->__("expand_collapse_list");?>
" class="hand hidden cm-combination-options-<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
"><span class="exicon-collapse"></span> </span>
                <a id="sw_extra_option_variants_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['num']->value, ENT_QUOTES, 'UTF-8');?>
" class="cm-combination-options-<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
"><?php echo $_smarty_tpl->__("extra");?>
</a>
                <input type="hidden" name="option_data[variants][<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['num']->value, ENT_QUOTES, 'UTF-8');?>
][variant_id]" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['vr']->value['variant_id'], ENT_QUOTES, 'UTF-8');?>
" class="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['cm_no_hide_input']->value, ENT_QUOTES, 'UTF-8');?>
" />
             </td>
             <td class="right cm-non-cb<?php if ($_smarty_tpl->tpl_vars['option_data']->value['option_type']=="C") {?> hidden<?php }?>">
                <?php echo $_smarty_tpl->getSubTemplate ("buttons/multiple_buttons.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('item_id'=>"option_variants_".((string)$_smarty_tpl->tpl_vars['id']->value)."_".((string)$_smarty_tpl->tpl_vars['num']->value),'tag_level'=>"3",'only_delete'=>"Y"), 0);?>

            </td>
        </tr>
        <tr id="extra_option_variants_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['num']->value, ENT_QUOTES, 'UTF-8');?>
" class="cm-ex-op hidden">
            <td colspan="7">
                <?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"product_options:edit_product_options")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"product_options:edit_product_options"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

                <div class="control-group cm-non-cb<?php if ($_smarty_tpl->tpl_vars['option_data']->value['option_type']=="C") {?> hidden<?php }?>">
                    <label class="control-label"><?php echo $_smarty_tpl->__("icon");?>
</label>
                    <div class="controls">
                        <?php echo $_smarty_tpl->getSubTemplate ("common/attach_images.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('image_name'=>"variant_image",'image_key'=>$_smarty_tpl->tpl_vars['num']->value,'hide_titles'=>true,'no_detailed'=>true,'image_object_type'=>"variant_image",'image_type'=>"V",'image_pair'=>$_smarty_tpl->tpl_vars['vr']->value['image_pair'],'prefix'=>$_smarty_tpl->tpl_vars['id']->value), 0);?>

                    </div>
                </div>
                <?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"product_options:edit_product_options"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>


            </td>
        </tr>
        </tbody>
        <?php } ?>

        <?php echo smarty_function_math(array('equation'=>"x + 1",'assign'=>"num",'x'=>(($tmp = @$_smarty_tpl->tpl_vars['num']->value)===null||$tmp==='' ? 0 : $tmp)),$_smarty_tpl);?>
<?php $_smarty_tpl->tpl_vars["vr"] = new Smarty_variable('', null, 0);?>
        <tbody class="hover cm-row-item <?php if ($_smarty_tpl->tpl_vars['option_data']->value['option_type']=="C") {?>hidden<?php }?>" id="box_add_variant_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
">
        <tr>
            <td class="cm-non-cb<?php if ($_smarty_tpl->tpl_vars['option_data']->value['option_type']=="C") {?> hidden<?php }?>">
                <input type="text" name="option_data[variants][<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['num']->value, ENT_QUOTES, 'UTF-8');?>
][position]" value="" size="3" class="input-micro" /></td>
            <td class="cm-non-cb<?php if ($_smarty_tpl->tpl_vars['option_data']->value['option_type']=="C") {?> hidden<?php }?>">
                <input type="text" name="option_data[variants][<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['num']->value, ENT_QUOTES, 'UTF-8');?>
][variant_name]" value="" class="input-medium" /></td>
            <td>
                <input type="text" name="option_data[variants][<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['num']->value, ENT_QUOTES, 'UTF-8');?>
][modifier]" value="" size="5" class="input-mini" />&nbsp;/
                <select class="input-mini" name="option_data[variants][<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['num']->value, ENT_QUOTES, 'UTF-8');?>
][modifier_type]">
                    <option value="A"><?php echo $_smarty_tpl->tpl_vars['currencies']->value[$_smarty_tpl->tpl_vars['primary_currency']->value]['symbol'];?>
</option>
                    <option value="P">%</option>
                </select>
            </td>
            <td>
                <input type="text" name="option_data[variants][<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['num']->value, ENT_QUOTES, 'UTF-8');?>
][weight_modifier]" value="" size="5" class="input-mini" />&nbsp;/&nbsp;<select class='input-mini' name="option_data[variants][<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['num']->value, ENT_QUOTES, 'UTF-8');?>
][weight_modifier_type]">
                    <option value="A"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['settings']->value['General']['weight_symbol'], ENT_QUOTES, 'UTF-8');?>
</option>
                    <option value="P">%</option>
                </select>
            </td>
            <td class="cm-non-cb<?php if ($_smarty_tpl->tpl_vars['option_data']->value['option_type']=="C") {?> hidden<?php }?>">
                <?php echo $_smarty_tpl->getSubTemplate ("common/select_status.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('input_name'=>"option_data[variants][".((string)$_smarty_tpl->tpl_vars['num']->value)."][status]",'display'=>"select",'meta'=>"input-small"), 0);?>
</td>
            <td>
                <span id="on_extra_option_variants_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['num']->value, ENT_QUOTES, 'UTF-8');?>
" alt="<?php echo $_smarty_tpl->__("expand_collapse_list");?>
" title="<?php echo $_smarty_tpl->__("expand_collapse_list");?>
" class="hand cm-combination-options-<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
"><span class="exicon-expand"></span></span>
                <span id="off_extra_option_variants_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['num']->value, ENT_QUOTES, 'UTF-8');?>
" alt="<?php echo $_smarty_tpl->__("expand_collapse_list");?>
" title="<?php echo $_smarty_tpl->__("expand_collapse_list");?>
" class="hand hidden cm-combination-options-<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
"><span class="exicon-collapse"></span></span>
                <a id="sw_extra_option_variants_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['num']->value, ENT_QUOTES, 'UTF-8');?>
" class="cm-combination-options-<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
"><?php echo $_smarty_tpl->__("extra");?>
</a>
            </td>
            <td class="right cm-non-cb<?php if ($_smarty_tpl->tpl_vars['option_data']->value['option_type']=="C") {?> hidden<?php }?>">
                <?php echo $_smarty_tpl->getSubTemplate ("buttons/multiple_buttons.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('item_id'=>"add_variant_".((string)$_smarty_tpl->tpl_vars['id']->value),'tag_level'=>"2"), 0);?>

            </td>
        </tr>
        <tr id="extra_option_variants_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['num']->value, ENT_QUOTES, 'UTF-8');?>
" class="cm-ex-op hidden">
            <td colspan="7">
                <?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"product_options:edit_product_options")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"product_options:edit_product_options"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

                <div class="control-group cm-non-cb">
                    <label class="control-label"><?php echo $_smarty_tpl->__("icon");?>
</label>
                    <div class="controls">
                        <?php echo $_smarty_tpl->getSubTemplate ("common/attach_images.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('image_name'=>"variant_image",'image_key'=>$_smarty_tpl->tpl_vars['num']->value,'hide_titles'=>true,'no_detailed'=>true,'image_object_type'=>"variant_image",'image_type'=>"V",'prefix'=>$_smarty_tpl->tpl_vars['id']->value), 0);?>

                    </div>
                </div>
                <?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"product_options:edit_product_options"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

            </td>
        </tr>
        </tbody>
        </table>
    </fieldset>
    <!--content_tab_option_variants_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
--></div>
</div>

<div class="buttons-container">
    <?php if ($_smarty_tpl->tpl_vars['id']->value) {?>
        <?php if (!$_smarty_tpl->tpl_vars['allow_save']->value&&$_smarty_tpl->tpl_vars['shared_product']->value!="Y") {?>
            <?php $_smarty_tpl->tpl_vars["hide_first_button"] = new Smarty_variable(true, null, 0);?>
        <?php }?>
    <?php }?>
    <?php echo $_smarty_tpl->getSubTemplate ("buttons/save_cancel.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('but_name'=>"dispatch[product_options.update]",'cancel_action'=>"close",'extra'=>'','hide_first_button'=>$_smarty_tpl->tpl_vars['hide_first_button']->value,'save'=>$_smarty_tpl->tpl_vars['id']->value), 0);?>

</div>

</form>

<!--content_group_product_option_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['id']->value, ENT_QUOTES, 'UTF-8');?>
--></div>
<?php }} ?>
