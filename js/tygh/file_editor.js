(function(_, $) {

    function initFileTree(files_list)
    {
        var fileTreeElm = $('.cm-te-file-tree');
        fileTreeElm.html(files_list);
        fileTreeElm.css({top: fileTreeElm.parent('.sidebar-row').position().top});
        var active = fileTreeElm.find('li.active > a');

        if (active.length) {
            file_editor.fileTree(active, true);
        } else {
            $('.cm-te-content > div').hide();
            file_editor.selected_file = {};
            file_editor._showErrorMessage('te-empty-folder');
            file_editor.parsePath();
        }
    }

    function getFileType(file_ext)
    {
        var textFormats = ['html', 'htm', 'php', 'txt', 'js', 'sql', 'ini', 'xml', 'tpl', 'css', 'less', 'json', 'yaml', 'csv'];
        var imageFormats = ['jpg', 'png', 'gif', 'jpeg'];
        var archiveFormats = ['zip', 'tgz'];

        if($.inArray(file_ext, textFormats) !== -1) {
            return 'text';
        } else if ($.inArray(file_ext, imageFormats) !== -1) {
            return 'image';
        } else if ($.inArray(file_ext, archiveFormats) !== -1) {
            return 'archive';
        }

        return false;
    }

    var file_editor = {
        selected_file: {
            fileFullPath: ''
        },
        rel_path: '',
        ed: '#template_text',

        init: function () {

            var self = this,
                path = '';

            // hide all
            $('.cm-te-messages > div').hide();
            self._showErrorMessage('te-empty-folder');

            //hide edit buttons
            $('.cm-te-edit').hide();

            if (self.selected_path || $.cookie.get('te_selected_path')) {
                path = self.selected_path || $.cookie.get('te_selected_path');
            }

            self._action('file_editor.init_view', {
                dir: path
            });

            // file tree
            $(_.doc).on('click', '.cm-te-file-tree li a', function() {
                self.fileTree(this, false);
            });

            // change path
            $(_.doc).on('click', '.cm-te-path a', function() {
                self.changePath(this);
            });

            // get file
            $(_.doc).on('click', '.cm-te-getfile', function() {
                if(self.selected_file.fileName.length > 0){
                    self.getFile();
                }
            });

            // rename
            $(_.doc).on('click', '.cm-te-rename', function(){
                self.rename();
            });

            // delete file or folder
            $(_.doc).on('click', '.cm-te-delete', function(){
                if(self.selected_file.fileName.length > 0){
                    self.deleteFile();
                }
            });

            // change perms
            $(_.doc).on('click', '.cm-te-chmod', function() {
                self.setPerms();
            });

            // parse perms
            $(_.doc).on('click', '.cm-te-perms', function() {
                self.parsePerms();
            });

            // restore file
            $(_.doc).on('click', '.cm-te-restore', function() {
                self.restoreFile();
            });

            // save file
            $(_.doc).on('click', '.cm-te-save-file', function() {
                self.saveFile();
            });

            // create file
            $.ceEvent('on', 'ce.formpost_add_file_form', function(form) {
                var filename = $('#elm_new_file').val();
                self.createFile(filename);

                return false;
            });

            // create folder
            $.ceEvent('on', 'ce.formpost_add_folder_form', function(form) {
                var folder_name = $('#elm_new_folder').val();
                self.createFolder(folder_name);

                return false;
            });

            // decompress file
            $(_.doc).on('click', '.cm-te-decompress', function() {
                if(self.selected_file.fileName.length > 0){
                    self.decompressFile();
                }
            });

            // compress file/directory
            $(_.doc).on('click', '.cm-te-compress', function() {
                if(self.selected_file.fileName.length > 0){
                    self.compressFile();
                }
            });

            $(_.doc).on('click', '.cm-te-upload-file', function() {
               $("#upload_path").val(self.selected_file.fileFullPath);
            });

            $(self.ed).ceCodeEditor('init');
        },

        fileTree: function(context, init) {
            var self = this;

            if ($(self.ed).hasClass('cm-item-modified')) {
                if (!confirm(_.tr('text_changes_not_saved'))) {
                    return false;
                } else {
                    $(self.ed).removeClass('cm-item-modified');
                }
            }

            self.selected_file.filePath = $(context).data('ca-item-path');
            self.selected_file.fileFullPath = $(context).data('ca-item-full-path').toString();
            self.selected_file.fileType = $(context).data('ca-item-type');
            self.selected_file.fileExt = $(context).data('ca-item-ext');
            self.selected_file.filePerms = $(context).data('ca-item-perms');
            self.selected_file.fileName = $(context).data('ca-item-filename').toString();
            self.selected_file.context = context;

            var li = $(context).parent('li');

            //show edit buttons
            $('.cm-te-edit').show();
            $('.ce-te-actions,.ce-te-actions li').show();

            // if folder click
            if(self.selected_file.fileType == 'D' && ($(li).hasClass('parent') == false)) {
                $.ceAjax('request', fn_url('file_editor.browse?dir=' + self.selected_file.fileFullPath), {
                    cache: false,
                    callback: function(data) {
                        $(context).after(data.files_list);
                        $('.cm-te-file-tree li').removeClass('active');
                        $(li).addClass('parent active');
                    }
                });
            } else if (!init) {
                $('.cm-te-file-tree li').removeClass('active');
                $(li).addClass('active');
                $(li).children('ul').slideToggle('fast');
            }

            // set overlay margin
            var overlayMargin = $(context).parents("ul").length * 15;
            $(context).find('.overlay').css('left', '-'+overlayMargin+'px');

            $('.cm-te-delete').removeClass('disabled').prop('disabled', false);
            $('.cm-te-decompress').addClass('disabled').prop('disabled', true);
            $('.cm-te-compress').removeClass('disabled').prop('disabled', false);

            $('.cm-te-messages > div').hide();

            var file_type = getFileType(self.selected_file.fileExt);
            if (file_type == 'archive') {
                $('.cm-te-decompress').removeClass('disabled').prop('disabled', false);
                $('.cm-te-compress').addClass('disabled').prop('disabled', true);
            }

            // if file click
            if(self.selected_file.fileType == 'F') {
                $('.cm-te-save-file').removeClass('disabled').prop('disabled', false);
                $('.cm-te-getfile').removeClass('disabled').prop('disabled', false);

                if (file_type == 'text') {
                    $.ceAjax('request', fn_url('file_editor.edit'), {
                        data: {
                            file: self.selected_file.fileName,
                            file_path: self.selected_file.filePath
                        },
                        method: 'GET',
                        callback: function(data, params) {
                            self.viewContent(data);
                        }
                    });
                } else {
                    self.viewContent({});
                }
            }

            $.cookie.set('te_selected_path', self.selected_file.filePath + '/' + self.selected_file.fileName);

            if(self.selected_file.fileType == 'D') {

                $('.cm-te-getfile').addClass('disabled').prop('disabled', true);
                $('.cm-te-create').show();
                $('.cm-te-save-file').addClass('disabled').prop('disabled', true);
                self._showErrorMessage('te-empty-folder');

                var iconToggle = $(context).find('i');

                if($(iconToggle).is('.exicon-expand')){
                    $(iconToggle).removeClass('exicon-expand').addClass('exicon-collapse');
                } else {
                    $(iconToggle).removeClass('exicon-collapse').addClass('exicon-expand');
                }
            } else {
                $('.cm-te-create').hide();
            }

            // rebuild file path
            self.parsePath();

            $.ceEvent('trigger', 'ce.fileeditor_tree', [context]);
        },

        // load content
        viewContent: function(response_data) {

            var self = this;

            if(response_data === undefined) {
                return;
            }

            var content = response_data.content || '';

            $('.cm-te-content > div').hide();

            $.ceEvent('trigger', 'ce.fileeditor_view', [response_data, self.selected_file]);

            if(getFileType(self.selected_file.fileExt) == 'text') {
                $(self.ed).show();
                $(self.ed).ceCodeEditor('set_value', content).removeClass('cm-item-modified');

            } else if(getFileType(self.selected_file.fileExt) == 'image') {
                $('.cm-te-content #template_image').show();
                var imgTag = '<img src="' + _.current_location + '/' + self.rel_path + self.selected_file.fileFullPath  + '" />';
                $('#template_image').html(imgTag);
                $('.cm-te-save-file').addClass('disabled').prop('disabled', true);
            } else {
                self._showErrorMessage('te-unknown-file');
            }
        },

        // parse path
        parsePath: function() {
            var self = this;
            var fullPath = self.selected_file.fileFullPath || '';

            fullPath=fullPath.split('/');

            var sub_path = [];
            var result = [];

            for(var i=0; i < fullPath.length; i++) {
                sub_path.push(fullPath[i]);
                result[i] = '<a data-ce-path="'+ sub_path.join('/') +'">' + fullPath[i] + '</a>';
            }

            $('.cm-te-path').html(result.join(' / '));
        },

        // change path
        changePath: function(context) {
            var path = $(context).data('ce-path');
            $('.cm-te-file-tree li a[data-ca-item-full-path="' + path + '"]').click();
        },

        // get file
        getFile: function() {
            var self = this;
            $.redirect(fn_url('file_editor.get_file?file=' + self.selected_file.fileName + '&file_path=' + self.selected_file.filePath));
        },

        rename: function() {
            var self = this;
            if (self.selected_file.fileName.length > 0) {
                var rename_to = prompt(_.tr('text_enter_filename'), self.selected_file.fileName);
                if (rename_to) {
                    self._action('file_editor.rename_file', {
                        file: self.selected_file.fileName,
                        file_path: self.selected_file.filePath,
                        rename_to: rename_to
                    });
                }
            }
        },

        // Delete file or directory
        deleteFile: function() {
            var self = this;
            if (self.selected_file.fileName.length > 0) {
                if (confirm(_.tr('text_are_you_sure_to_delete_file'))) {
                    self._action('file_editor.delete_file', {
                        file: self.selected_file.fileName,
                        file_path: self.selected_file.filePath
                    });
                }
            }
        },

        // set perms
        setPerms: function() {
            var self = this;

            var text_perms = '';
            var perms = 0;
            perms = $('#o_read').prop('checked') ? perms + 400 : perms;
            perms = $('#o_write').prop('checked') ? perms + 200 : perms;
            perms = $('#o_exec').prop('checked') ? perms + 100 : perms;
            perms = $('#g_read').prop('checked') ? perms + 40 : perms;
            perms = $('#g_write').prop('checked') ? perms + 20 : perms;
            perms = $('#g_exec').prop('checked') ? perms + 10 : perms;
            perms = $('#w_read').prop('checked') ? perms + 4 : perms;
            perms = $('#w_write').prop('checked') ? perms + 2 : perms;
            perms = $('#w_exec').prop('checked') ? perms + 1 : perms;

            text_perms = $('#o_read').prop('checked') ? text_perms + 'r' : text_perms + '-';
            text_perms = $('#o_write').prop('checked') ? text_perms + 'w' : text_perms + '-';
            text_perms = $('#o_exec').prop('checked') ? text_perms + 'x' : text_perms + '-';
            text_perms = $('#g_read').prop('checked') ? text_perms + 'r' : text_perms + '-';
            text_perms = $('#g_write').prop('checked') ? text_perms + 'w' : text_perms + '-';
            text_perms = $('#g_exec').prop('checked') ? text_perms + 'x' : text_perms + '-';
            text_perms = $('#w_read').prop('checked') ? text_perms + 'r' : text_perms + '-';
            text_perms = $('#w_write').prop('checked') ? text_perms + 'w' : text_perms + '-';
            text_perms = $('#w_exec').prop('checked') ? text_perms + 'x' : text_perms + '-';

            var recursive =  $('#chmod_recursive').prop('checked');

            if (self.selected_file.fileName.length > 0) {
                self._action('file_editor.chmod', {
                    file: self.selected_file.fileName,
                    file_path: self.selected_file.filePath,
                    perms: perms,
                    r: recursive
                });
            }
        },

        parsePerms: function() {
            var self = this;

            var perms = self.selected_file.filePerms;

            $('#o_read').prop('checked', (perms.charAt(0) == '-') ? false : true);
            $('#o_write').prop('checked', (perms.charAt(1) == '-') ? false : true);
            $('#o_exec').prop('checked', (perms.charAt(2) == '-') ? false : true);
            $('#g_read').prop('checked', (perms.charAt(3) == '-') ? false : true);
            $('#g_write').prop('checked', (perms.charAt(4) == '-') ? false : true);
            $('#g_exec').prop('checked', (perms.charAt(5) == '-') ? false : true);
            $('#w_read').prop('checked', (perms.charAt(6) ==    '-') ? false : true);
            $('#w_write').prop('checked', (perms.charAt(7) == '-') ? false : true);
            $('#w_exec').prop('checked', (perms.charAt(8) == '-') ? false : true);
        },

        // Restore file from the repository
        restoreFile: function() {
            var self = this;
            if (confirm(_.tr('text_restore_question'))) {
                self._action('file_editor.restore', {
                    file: self.selected_file.fileName,
                    file_path: self.selected_file.filePath,
                }, function(response_data) {
                    if (typeof(response_data.content) != 'undefined') {
                        self.viewContent(response_data);
                    }
                });
            }

            return false;
        },

        // Create file or directory
        createFile: function(filename)
        {
            var self = this;
            var file = filename;
            var file_path = self.selected_file.fileFullPath || '';

            self._action('file_editor.create_file', {
                file: file,
                file_path: file_path
            });
        },

        // Create file or directory
        createFolder: function(folder)
        {
            var self = this;
            var file_path = this.selected_file.fileFullPath || '';

            self._action('file_editor.create_folder', {
                file: folder,
                file_path: file_path
            });
        },

        saveFile: function()
        {
            var self = this;

            var ed = $(self.ed);
            if (ed.hasClass('cm-item-modified')) {
                $.ceAjax('request', fn_url('file_editor.edit'), {
                    data: {
                        file: this.selected_file.fileName,
                        file_path: this.selected_file.filePath,
                        file_content: ed.ceCodeEditor('value')
                    },
                    callback: function(response_data, params, response_text) {
                        if (response_data.saved) {
                            ed.removeClass('cm-item-modified');
                        }
                    },
                method: 'post'});
            }
        },

        // decompress file
        decompressFile: function() {
            var self = this;
            var file = self.selected_file.fileName;
            var file_path = self.selected_file.filePath;

            self._action('file_editor.decompress', {
                file: file,
                file_path: file_path
            });
        },

        // compress file
        compressFile: function() {
            var self = this;
            var file = self.selected_file.fileName;
            var file_path = self.selected_file.filePath;

            self._action('file_editor.compress', {
                file: file,
                file_path: file_path
            });
        },

        _showErrorMessage: function(type) {
            $('.cm-te-content > div').hide();
            $('.cm-te-messages > div').hide();
            $('.cm-te-messages .' + type).show();
        },

        _action: function(dispatch, data, callback, method) {
            $.ceAjax('request', fn_url(dispatch), {
                data: data,
                callback: function(response_data, params, response_text) {
                    if (callback) {
                        callback(response_data);
                    }
                    initFileTree(response_data.files_list);
                },
                cache: false,
                method: method || 'get'
            });
        }
    };

    _.file_editor = file_editor;

    $(document).ready(function() {
        file_editor.init();
    });

}(Tygh, Tygh.$));
