/* editior-description:text_tinymce */
(function(_, $) {
    $.ceEditor('handlers', {

        editorName: 'tinymce',
        params: null,
        is_destroying: false,
        
        run: function(elm, params) {
            if (typeof($.fn.tinymce) == 'undefined') {
                $.ceEditor('state', 'loading');
                return $.getScript('js/lib/tinymce/jquery.tinymce.min.js', function() {
                    $.ceEditor('state', 'loaded');
                    elm.ceEditor('run', params);
                });
            }
            
            // You have to change this array if you want to add a new lang pack.
            var support_langs = ['ar', 'hy', 'az', 'eu', 'be', 'bs', 'ca', 'hr', 'cs', 'da', 'dv', 'nl', 'et', 'fo', 'fi', 'gl', 'de', 'el', 'id', 'it', 'ja', 'kk', 'lv', 'lt', 'lb', 'fa', 'pl', 'ro', 'ru', 'sr', 'sk', 'es', 'tg', 'ta', 'ug', 'uk', 'vi', 'cy', 'fr', 'ka', 'he', 'hu', 'is', 'bg', 'zh', 'en', 'km', 'ko', 'ml', 'nb', 'pt', 'si', 'sl', 'sv', 'ta', 'th', 'tr'];
            var lang_map = {
                'fr': 'fr_FR',
                'ka': 'ka_GE',
                'he': 'he_IL',
                'hu': 'hu_HU',
                'is': 'is_IS',
                'bg': 'bg_BG',
                'zh': 'zh_CN',
                'en': 'en_GB',
                'km': 'km_KH',
                'ko': 'ko_KR',
                'ml': 'ml_IN',
                'nb': 'nb_NO',
                'pt': 'pt_PT',
                'si': 'si_LK',
                'sl': 'sl_SI',
                'sv': 'sv_SE',
                'ta': 'ta_IN',
                'th': 'th_TH',
                'tr': 'tr_TR'
            };

            var lang = fn_get_listed_lang(support_langs);
            if (lang in lang_map) {
                lang = lang_map[lang];
            }

            if (!this.params) {
                this.params = {

                    script_url : _.current_location + '/js/lib/tinymce/tinymce.min.js',
                    plugins: [
                        "advlist autolink lists link image charmap print preview anchor",
                        "searchreplace visualblocks code fullscreen",
                        "insertdatetime media table contextmenu paste textcolor"
                    ],
                    menubar: false,
                    statusbar: true,
                    mode : "textareas",
                    force_p_newlines : true,
                    extended_valid_elements: "i[*],span[*]",
                    forced_root_block : '',
                    media_strict: false,

                    toolbar: 'formatselect fontselect fontsizeselect bold italic underline forecolor backcolor | link image | numlist bullist indent outdent | alignleft aligncenter alignright | code',
                    resize: true,
                    theme : 'modern',
                    language: lang,
                    strict_loading_mode: true,
                    convert_urls: false,
                    remove_script_host: false,
                    body_class: 'wysiwyg-content',
                    content_css: $.ceEditor('content_css').join(),
        
                    file_browser_callback : function(field_name, url, type, win) {
                        tinyMCE.activeEditor.windowManager.open({
                            file : _.current_location + '/js/lib/elfinder/elfinder.tinymce.html',
                            title: fn_strip_tags(_.tr('file_browser')),
                            width : 600,
                            height : 450,
                            resizable : 'yes',
                            inline : 'yes',
                            close_previous : 'no',
                            popup_css : false // Disable TinyMCE's default popup CSS
                        }, {
                            'window': win,
                            'input': field_name,
                            'connector_url': fn_url('elf_connector.images?ajax_custom=1')
                        });
                    },
                    setup: function(ed) {
                        ed.on('init', function(ed) {
                            if (elm.prop('disabled')) {
                                elm.ceEditor('disable', true);
                            }
                        });

                        ed.on('change', function() {
                            elm.ceEditor('changed', ed.getContent());
                        });
                    },
                };

                if (typeof params !== 'undefined' && params[this.editorName]) {
                    $.extend(this.params, params[this.editorName]);
                }
            }

            elm.tinymce(this.params);
        },
    
        destroy: function(elm) {
            tinymce.remove();
            this.is_destroying = true;
            setTimeout(function() {
                // TinyMCE editor disappears by timeout after destroy, even if editor is recovered
                // add delay to track it
                this.is_destroying = false;
            }, 1);
        },
    
        recover: function(elm) {
            if (this.is_destroying) {
                setTimeout(function() {    
                    elm.ceEditor('run');
                }, 1);
            } else {
                elm.ceEditor('run');
            }
        },
               
        val: function(elm, value) {
            if (typeof(value) == 'undefined') {
                return elm.val();
            } else {
                elm.val(value);
            }
            
            return true;
        },

        updateTextFields: function(elm) {
            return true;
        },

        disable: function(elm, value) {
            var state = (value == true) ? 'Off' : 'On';
            $('.mce-toolbar-grp').toggle();
            tinyMCE.editors[0].getBody().setAttribute('contenteditable', !value);
            elm.prop('disabled', value);
        }
    });
}(Tygh, Tygh.$));
