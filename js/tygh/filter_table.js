(function($) {
    var methods = {
        init: function(params) {
            return this.each(function() {
                
                $(this).on("keyup input", function() {
                    var data = this.value.split(" ");
                    var jelm = $(params.table).find("tr");
                    var filtered_data = [];

                    if (this.value == "") {
                        jelm.show();
                        return;
                    }
                    //hide all the rows
                    jelm.hide();
                    
                    filtered_data = jelm.filter(function(i, v) {
                        // jquery contains selector case insensitive
                        $.extend($.expr[':'], {
                          'containsi': function(elem, i, match, array)
                          {
                            return (elem.textContent || elem.innerText || '').toLowerCase()
                            .indexOf((match[3] || "").toLowerCase()) >= 0;
                          }
                        });

                        var t = $(this);
                        for (var d = 0; d < data.length; ++d) {
                            if (t.is(":containsi('" + data[d] + "')")) {
                                return true;
                            }
                        }
                        return false;
                    });

                    filtered_data.show();

                    console.log(filtered_data);

                    if(filtered_data.length <= 0) {
                        $(params.empty).removeClass("hidden");
                    } else {
                        $(params.empty).addClass("hidden");
                    }

                }).focus(function() {
                    this.value = "";
                    $(this).unbind('focus');
                });
            });
        }
    };
    $.fn.ceFilterTable = function(method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('ty.filterTable: method ' + method + ' does not exist');
        }
    };
})($);