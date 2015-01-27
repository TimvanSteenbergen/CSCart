/* editior-description:text_redactor */
(function(_, $) {
    var methods = {
        _getEditor: function(elm) {
            var obj = $('#' + elm.prop('id'));
            if (obj.data('redactor')) {
                return obj;
            }
            
            return false;
        }
    };
    
    $.ceEditor('handlers', {

        editorName: 'redactor',
        params: null,
        elms: [],

        run: function(elm, params) {

            var translations = {'no': 'no_NB', 'zh': 'zh_tw'};
            var lang_code = (_.cart_language in translations) ? translations[_.cart_language] : _.cart_language;

            if (typeof($.fn.redactor) == 'undefined') {
                $.ceEditor('state', 'loading');
                $.loadCss(['js/lib/redactor/redactor.css']);
                // Load elFinder
                $.loadCss(['js/lib/elfinder/css/elfinder.css']);

                $.getScript('js/lib/elfinder/js/elfinder.min.js');
                $.getScript('js/lib/redactor/plugins/fontcolor/fontcolor.js');
                $.getScript('js/lib/redactor/redactor.min.js', function() {
                    if (lang_code != 'en') {
                        $.getScript('js/lib/redactor/lang/' + lang_code + '.js', function() {
                            callback();
                        });
                    } else {
                        callback();
                    }
                });

                var callback = function() {
                    $.ceEditor('state', 'loaded');
                    elm.ceEditor('run', params);
                };

                return true;
            }

            if (!this.params) {
                this.params = {
                    lang: lang_code,

                };
            }

            if (typeof params !== 'undefined' && params[this.editorName]) {
                $.extend(this.params, params[this.editorName]);
            }

            this.params.initCallback = function()
            {
                this.buttonAddBefore('video', 'image', 'Image', function() {
                    // Start button processing
                    this.selectionSave();
                    var modal_image = String() +
                        '<section>' +
                            '<div id="redactor-progress" class="redactor-progress redactor-progress-striped" style="display: none;">' +
                                '<div id="redactor-progress-bar" class="redactor-progress-bar" style="width: 100%;"></div>' +
                            '</div>' +
                            '<div id="redactor_tab3" class="redactor_tab">' +
                                '<label>' + this.opts.curLang.image_web_link + '</label>' +
                                '<input type="text" name="redactor_file_link" id="redactor_file_link" class="redactor_input"  />' +
                                '<button class="redactor_modal_btn" style="margin-left: 0px; margin-top: 10px;" id="elfinder_control">'+ fn_strip_tags(_.tr("browse")) +'</button>' +
                            '</div>' +
                        '</section>' +
                        '<footer>' +
                            '<button class="redactor_modal_btn redactor_btn_modal_close">' + this.opts.curLang.cancel + '</button>' +
                            '<button class="redactor_modal_btn redactor_modal_action_btn" id="redactor_upload_btn">' + this.opts.curLang.insert + '</button>' +
                        '</footer>';
                
                    var callback = $.proxy(function()
                    {
                        $('#redactor_upload_btn').click($.proxy(this.imageCallbackLink, this));
                        $('#redactor_file_link').focus();

                        var elf_config = {
                            url  : fn_url('elf_connector.images?ajax_custom=1')
                        };

                        $('#elfinder_control').click(function(){
                            $('<div id="elfinder_browser" />').elfinder({
                                url : fn_url('elf_connector.images?ajax_custom=1'),
                                lang : 'en',
                                dialog: {width: 900, modal: true, title: fn_strip_tags(_.tr('file_browser'))},
                                closeOnEditorCallback : true,
                                places: '',
                                view: 'list',
                                disableShortcuts: true,
                                editorCallback: function(file) {
                                    $('#redactor_file_link').val(file + '?' + new Date().getTime());
                                }
                            });
                            $(".el-finder-dialog").css({'z-index': 500001});
                        });

                    }, this);

                    this.modalInit(this.opts.curLang.image, modal_image, 610, callback);

                    // End button processing
                });
            };

            this.params.modalOpenedCallback = function() {
                $('#redactor_modal_overlay,#redactor_modal,.redactor_dropdown').each(function() {
                    this.style.setProperty('z-index', 50001, 'important');
                })

            };

            this.params.plugins = ['fontcolor'];
            this.params.buttons = ['html', '|', 'formatting', '|', 'bold', 'italic', 'deleted', '|', 'unorderedlist', 'orderedlist', 'outdent', 'indent', '|',
                    'video', 'file', 'table', 'link', '|', 'alignment', '|', 'horizontalrule']; // 'underline', 'alignleft', 'aligncenter', 'alignright', 'justify'
            this.params.convertDivs = false;
            this.params.changeCallback = function(html) {
                elm.ceEditor('changed', html);
            };
            
            // Launch Redactor
            elm.redactor(this.params);

            if (elm.prop('disabled')) {
                elm.ceEditor('disable', true);
            }

            this.elms.push(elm.get(0));
            return true;
        },
        
        destroy: function(elm) {
            var ed = methods._getEditor(elm);
            if (ed) {
                ed.redactor('destroy');
            }
        },

        recover: function(elm) {
            if ($.inArray(elm.get(0), this.elms) !== -1) {
                $.ceEditor('run', elm);
            }
        },
               
        val: function(elm, value) {
            var ed = methods._getEditor(elm);
            if (!ed) {
                return false;
            }
            
            if (typeof(value) == 'undefined') {
                return ed.redactor('get');
            } else {
                ed.redactor('set', value);
            }
            return true;
        },

        updateTextFields: function(elm) {
            return true;
        },

        disable: function(elm, value) {
            var ed = methods._getEditor(elm);
            if (ed) {
                var obj = ed.redactor('getBox');
                if (value == true) {
                    if (!$(obj).parent().hasClass('disable-overlay-wrap')) {
                        $(obj).wrap("<div class='disable-overlay-wrap wysiwyg-overlay'></div>");
                        $(obj).before("<div id='" + elm.prop('id') + "_overlay' class='disable-overlay'></div>");
                        elm.prop('disabled', true);
                    }
                } else {
                    $(obj).unwrap();
                    $('#' + elm.prop('id') + '_overlay').remove();
                    elm.prop('disabled', false);
                }
            }
        }
    });
}(Tygh, Tygh.$));
