<?php /* Smarty version Smarty-3.1.18, created on 2014-11-24 17:22:57
         compiled from "/var/www/html/workspace/cscart/design/backend/templates/common/attach_images.tpl" */ ?>
<?php /*%%SmartyHeaderCode:119079228354733f41438ef7-22764670%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'e18f1ba2e3a86acebd398d51adec2f3a345cba64' => 
    array (
      0 => '/var/www/html/workspace/cscart/design/backend/templates/common/attach_images.tpl',
      1 => 1413383301,
      2 => 'tygh',
    ),
  ),
  'nocache_hash' => '119079228354733f41438ef7-22764670',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'original_image' => 0,
    'image_key' => 0,
    'image_object_id' => 0,
    'image_name' => 0,
    'image_object_type' => 0,
    'image_type' => 0,
    'image_pair' => 0,
    '_plug' => 0,
    'image_suffix' => 0,
    'name' => 0,
    'suffix' => 0,
    'key' => 0,
    'pair' => 0,
    'type' => 0,
    'object_id' => 0,
    'no_thumbnail' => 0,
    'hide_titles' => 0,
    'detailed_title' => 0,
    'detailed_text' => 0,
    'delete_pair' => 0,
    'object_type' => 0,
    'icon_title' => 0,
    'icon_text' => 0,
    'runtime' => 0,
    'hide_images' => 0,
    'hide_alt' => 0,
    'no_detailed' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.18',
  'unifunc' => 'content_54733f414e83d5_99618062',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54733f414e83d5_99618062')) {function content_54733f414e83d5_99618062($_smarty_tpl) {?><?php if (!is_callable('smarty_block_hook')) include '/var/www/html/workspace/cscart/app/functions/smarty_plugins/block.hook.php';
?><?php
fn_preload_lang_vars(array('text_thumbnail_manual_loading','popup_larger_image','delete_image_pair','thumbnail','remove','no_image','alt_text','remove','no_image','alt_text'));
?>


<?php if (!defined("SMARTY_ATTACH_IMAGES_LOADED")) {?>
<?php $_smarty_tpl->tpl_vars["tmp"] = new Smarty_variable(define("SMARTY_ATTACH_IMAGES_LOADED",true), null, 0);?>
<script type="text/javascript">
(function(_, $) {
    $.ceEvent('on', 'ce.delete_image', function(r, p) {
        if (r.deleted == true) {
            $('#' + p.result_ids).closest('a').replaceWith('<div class="no-image"><i class="glyph-image" title="' + _.tr('no_image') + '"></i></div>');
            $('a[data-ca-target-id=' + p.result_ids + ']').hide();
        }
    });

    $.ceEvent('on', 'ce.delete_image_pair', function(r, p) {
        if (r.deleted == true) {
            $('#' + p.result_ids).remove();
        }        
    });    
}(Tygh, Tygh.$));    
</script>
<?php }?>

<?php if (!$_smarty_tpl->tpl_vars['original_image']->value) {?>
    <?php $_smarty_tpl->tpl_vars["thumbnail_width"] = new Smarty_variable("85", null, 0);?>
<?php }?>
<?php $_smarty_tpl->tpl_vars["_plug"] = new Smarty_variable(explode(".",''), null, 0);?>
<?php $_smarty_tpl->tpl_vars["key"] = new Smarty_variable((($tmp = @$_smarty_tpl->tpl_vars['image_key']->value)===null||$tmp==='' ? "0" : $tmp), null, 0);?>
<?php $_smarty_tpl->tpl_vars["object_id"] = new Smarty_variable((($tmp = @$_smarty_tpl->tpl_vars['image_object_id']->value)===null||$tmp==='' ? "0" : $tmp), null, 0);?>
<?php $_smarty_tpl->tpl_vars["name"] = new Smarty_variable((($tmp = @$_smarty_tpl->tpl_vars['image_name']->value)===null||$tmp==='' ? '' : $tmp), null, 0);?>
<?php $_smarty_tpl->tpl_vars["object_type"] = new Smarty_variable((($tmp = @$_smarty_tpl->tpl_vars['image_object_type']->value)===null||$tmp==='' ? '' : $tmp), null, 0);?>
<?php $_smarty_tpl->tpl_vars["type"] = new Smarty_variable((($tmp = @$_smarty_tpl->tpl_vars['image_type']->value)===null||$tmp==='' ? "M" : $tmp), null, 0);?>
<?php $_smarty_tpl->tpl_vars["pair"] = new Smarty_variable((($tmp = @$_smarty_tpl->tpl_vars['image_pair']->value)===null||$tmp==='' ? $_smarty_tpl->tpl_vars['_plug']->value : $tmp), null, 0);?>
<?php $_smarty_tpl->tpl_vars["suffix"] = new Smarty_variable((($tmp = @$_smarty_tpl->tpl_vars['image_suffix']->value)===null||$tmp==='' ? '' : $tmp), null, 0);?>

<input type="hidden" name="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['name']->value, ENT_QUOTES, 'UTF-8');?>
_image_data<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['suffix']->value, ENT_QUOTES, 'UTF-8');?>
[<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['key']->value, ENT_QUOTES, 'UTF-8');?>
][pair_id]" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['pair']->value['pair_id'], ENT_QUOTES, 'UTF-8');?>
" class="cm-image-field" />
<input type="hidden" name="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['name']->value, ENT_QUOTES, 'UTF-8');?>
_image_data<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['suffix']->value, ENT_QUOTES, 'UTF-8');?>
[<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['key']->value, ENT_QUOTES, 'UTF-8');?>
][type]" value="<?php echo htmlspecialchars((($tmp = @$_smarty_tpl->tpl_vars['type']->value)===null||$tmp==='' ? "M" : $tmp), ENT_QUOTES, 'UTF-8');?>
" class="cm-image-field" />
<input type="hidden" name="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['name']->value, ENT_QUOTES, 'UTF-8');?>
_image_data<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['suffix']->value, ENT_QUOTES, 'UTF-8');?>
[<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['key']->value, ENT_QUOTES, 'UTF-8');?>
][object_id]" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['object_id']->value, ENT_QUOTES, 'UTF-8');?>
" class="cm-image-field" />

<div id="box_attach_images_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['name']->value, ENT_QUOTES, 'UTF-8');?>
_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['key']->value, ENT_QUOTES, 'UTF-8');?>
" class="attach-images">
    <?php if ($_smarty_tpl->tpl_vars['no_thumbnail']->value&&!$_smarty_tpl->tpl_vars['pair']->value['icon']) {?>
        <span class="desc"><?php echo $_smarty_tpl->__("text_thumbnail_manual_loading",array("[id]"=>"sw_load_thumbnail_".((string)$_smarty_tpl->tpl_vars['name']->value).((string)$_smarty_tpl->tpl_vars['suffix']->value).((string)$_smarty_tpl->tpl_vars['key']->value),"[class]"=>"cm-combination"));?>
</span>
    <?php }?>

    <?php if (!$_smarty_tpl->tpl_vars['hide_titles']->value) {?>
        <?php if (!'hide_desc') {?>
        <div class="desc">
            <span><?php echo htmlspecialchars((($tmp = @$_smarty_tpl->tpl_vars['detailed_title']->value)===null||$tmp==='' ? $_smarty_tpl->__("popup_larger_image") : $tmp), ENT_QUOTES, 'UTF-8');?>

            <?php if ($_smarty_tpl->tpl_vars['detailed_text']->value) {?><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['detailed_text']->value, ENT_QUOTES, 'UTF-8');?>
<?php }?>
            :</span>
        </div>
        <?php }?>
    <?php }?>

    <?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"attach_images:thumbnail")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"attach_images:thumbnail"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

    <div class="upload-box clearfix <?php if ($_smarty_tpl->tpl_vars['no_thumbnail']->value&&!$_smarty_tpl->tpl_vars['pair']->value['icon']) {?>hidden<?php }?>" id="load_thumbnail_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['name']->value, ENT_QUOTES, 'UTF-8');?>
<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['suffix']->value, ENT_QUOTES, 'UTF-8');?>
<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['key']->value, ENT_QUOTES, 'UTF-8');?>
">
    <?php if ($_smarty_tpl->tpl_vars['delete_pair']->value&&$_smarty_tpl->tpl_vars['pair']->value['pair_id']) {?>
        <div class="float-right">
            <a data-ca-target-id="box_attach_images_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['name']->value, ENT_QUOTES, 'UTF-8');?>
_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['key']->value, ENT_QUOTES, 'UTF-8');?>
" href="<?php echo htmlspecialchars(fn_url("image.delete_image_pair?pair_id=".((string)$_smarty_tpl->tpl_vars['pair']->value['pair_id'])."&object_type=".((string)$_smarty_tpl->tpl_vars['object_type']->value)), ENT_QUOTES, 'UTF-8');?>
" class="cm-confirm cm-ajax cm-tooltip pull-right" data-ca-event="ce.delete_image_pair" title="<?php echo $_smarty_tpl->__("delete_image_pair");?>
"><i class="icon-remove"></i></a>
        </div>
    <?php }?>
        <?php if (!$_smarty_tpl->tpl_vars['hide_titles']->value) {?>
            <h5>
                <span><?php echo htmlspecialchars((($tmp = @$_smarty_tpl->tpl_vars['icon_title']->value)===null||$tmp==='' ? $_smarty_tpl->__("thumbnail") : $tmp), ENT_QUOTES, 'UTF-8');?>
</span>
                <?php if ($_smarty_tpl->tpl_vars['icon_text']->value) {?><span class="small-note"><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['icon_text']->value, ENT_QUOTES, 'UTF-8');?>
</span><?php }?>
            </h5>
        <?php }?>
        
        <div class="pull-left image-wrap">
            <?php if ($_smarty_tpl->tpl_vars['pair']->value['image_id']) {?>
            <?php if (!(fn_allowed_for("MULTIVENDOR")&&$_smarty_tpl->tpl_vars['runtime']->value['company_id']&&$_smarty_tpl->tpl_vars['object_type']->value=="category")) {?>
                <a data-ca-target-id="image_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['pair']->value['image_id'], ENT_QUOTES, 'UTF-8');?>
" href="<?php echo htmlspecialchars(fn_url("image.delete_image?pair_id=".((string)$_smarty_tpl->tpl_vars['pair']->value['pair_id'])."&image_id=".((string)$_smarty_tpl->tpl_vars['pair']->value['image_id'])."&object_type=".((string)$_smarty_tpl->tpl_vars['object_type']->value)), ENT_QUOTES, 'UTF-8');?>
" class="image-delete cm-confirm cm-ajax delete cm-delete-image-link cm-tooltip" data-ca-event="ce.delete_image" title="<?php echo $_smarty_tpl->__("remove");?>
"><i class="icon-remove-sign"></i></a>
            <?php }?>
            <?php }?>
            <?php if (!$_smarty_tpl->tpl_vars['hide_images']->value) {?>
                <div class="image">
                    <?php if ($_smarty_tpl->tpl_vars['pair']->value['image_id']) {?>
                        <?php echo $_smarty_tpl->getSubTemplate ("common/image.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('image'=>$_smarty_tpl->tpl_vars['pair']->value['icon'],'image_id'=>$_smarty_tpl->tpl_vars['pair']->value['image_id'],'image_width'=>85), 0);?>

                    <?php } else { ?>
                        <div class="no-image"><i class="glyph-image" title="<?php echo $_smarty_tpl->__("no_image");?>
"></i></div>
                    <?php }?>
                </div>
            <?php }?>
            <?php if (!$_smarty_tpl->tpl_vars['hide_alt']->value) {?>
                <div class="image-alt clear">
                    <div class="input-prepend">
                    <span class="add-on cm-tooltip cm-hide-with-inputs" title="<?php echo $_smarty_tpl->__("alt_text");?>
"><i class="icon-comment"></i></span>
                    
                    <input type="text" id="alt_icon_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['name']->value, ENT_QUOTES, 'UTF-8');?>
_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['key']->value, ENT_QUOTES, 'UTF-8');?>
" name="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['name']->value, ENT_QUOTES, 'UTF-8');?>
_image_data<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['suffix']->value, ENT_QUOTES, 'UTF-8');?>
[<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['key']->value, ENT_QUOTES, 'UTF-8');?>
][image_alt]" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['pair']->value['icon']['alt'], ENT_QUOTES, 'UTF-8');?>
" />
                    </div>
                </div>
            <?php }?>
        </div>
        <div class="image-upload cm-hide-with-inputs">
            <?php echo $_smarty_tpl->getSubTemplate ("common/fileuploader.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('var_name'=>((string)$_smarty_tpl->tpl_vars['name']->value)."_image_icon".((string)$_smarty_tpl->tpl_vars['suffix']->value)."[".((string)$_smarty_tpl->tpl_vars['key']->value)."]",'is_image'=>true), 0);?>

            <?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"attach_images:options_for_icon")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"attach_images:options_for_icon"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

            <?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"attach_images:options_for_icon"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

        </div>
    </div>
    <?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"attach_images:thumbnail"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

    <?php if (!$_smarty_tpl->tpl_vars['no_detailed']->value) {?>
    <?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"attach_images:detailed")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"attach_images:detailed"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

    <?php if ($_smarty_tpl->tpl_vars['detailed_title']->value) {?>
        <h5><span><?php echo htmlspecialchars($_smarty_tpl->tpl_vars['detailed_title']->value, ENT_QUOTES, 'UTF-8');?>
</span></h5>
    <?php }?>
    <div class="upload-box clearfix">
        <div class="pull-left image-wrap">
            <?php if (!$_smarty_tpl->tpl_vars['hide_images']->value) {?>
                <?php if ($_smarty_tpl->tpl_vars['pair']->value['detailed_id']) {?>
                    <?php if (!(fn_allowed_for("MULTIVENDOR")&&$_smarty_tpl->tpl_vars['runtime']->value['company_id']&&$_smarty_tpl->tpl_vars['object_type']->value=="category")) {?>
                        <a data-ca-target-id="image_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['pair']->value['detailed_id'], ENT_QUOTES, 'UTF-8');?>
" href="<?php echo htmlspecialchars(fn_url("image.delete_image?pair_id=".((string)$_smarty_tpl->tpl_vars['pair']->value['pair_id'])."&image_id=".((string)$_smarty_tpl->tpl_vars['pair']->value['detailed_id'])."&object_type=detailed"), ENT_QUOTES, 'UTF-8');?>
" class="image-delete cm-confirm cm-tooltip cm-ajax delete cm-delete-image-link" data-ca-event="ce.delete_image" title="<?php echo $_smarty_tpl->__("remove");?>
"><i class="icon-remove-sign"></i></a>
                    <?php }?>
                <?php }?>
                <div class="image">
                    <?php if ($_smarty_tpl->tpl_vars['pair']->value['detailed_id']) {?>
                        <?php echo $_smarty_tpl->getSubTemplate ("common/image.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('image'=>$_smarty_tpl->tpl_vars['pair']->value['detailed'],'image_id'=>$_smarty_tpl->tpl_vars['pair']->value['detailed_id'],'image_width'=>85), 0);?>

                    <?php } else { ?>
                        <div class="no-image"><i class="glyph-image" title="<?php echo $_smarty_tpl->__("no_image");?>
"></i></div>
                    <?php }?>
                </div>
            <?php }?>
            
            <?php if (!$_smarty_tpl->tpl_vars['hide_alt']->value) {?>
                <div class="image-alt">
                    <div class="input-prepend">
                        
                        <span class="add-on cm-tooltip cm-hide-with-inputs" title="<?php echo $_smarty_tpl->__("alt_text");?>
"><i class="icon-comment"></i></span>
                        <input type="text" id="alt_det_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['name']->value, ENT_QUOTES, 'UTF-8');?>
_<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['key']->value, ENT_QUOTES, 'UTF-8');?>
" name="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['name']->value, ENT_QUOTES, 'UTF-8');?>
_image_data<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['suffix']->value, ENT_QUOTES, 'UTF-8');?>
[<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['key']->value, ENT_QUOTES, 'UTF-8');?>
][detailed_alt]" value="<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['pair']->value['detailed']['alt'], ENT_QUOTES, 'UTF-8');?>
" />
                    </div>
                </div>
            <?php }?>
        </div>

        <div class="image-upload cm-hide-with-inputs">
            <?php echo $_smarty_tpl->getSubTemplate ("common/fileuploader.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, null, array('var_name'=>((string)$_smarty_tpl->tpl_vars['name']->value)."_image_detailed".((string)$_smarty_tpl->tpl_vars['suffix']->value)."[".((string)$_smarty_tpl->tpl_vars['key']->value)."]"), 0);?>

            <?php $_smarty_tpl->smarty->_tag_stack[] = array('hook', array('name'=>"attach_images:options_for_detailed")); $_block_repeat=true; echo smarty_block_hook(array('name'=>"attach_images:options_for_detailed"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

            <?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"attach_images:options_for_detailed"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

        </div>

    </div>
    <?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_hook(array('name'=>"attach_images:detailed"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

    <?php }?>
</div><?php }} ?>
