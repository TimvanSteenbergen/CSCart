/*
 * Period selector
 *
 */
(function($){

    
    var methods = {

        init: function(params) {
            var self = $(this);

            self.change(function() {
                var dates = methods._getDates(self.val());
                $('#' + params.from).datepicker('setDate', dates.from);
                $('#' + params.to).datepicker('setDate', dates.to);
            });
        },

        _daysInMonth: function(m, y) {
            return 32 - new Date(y, m, 32).getDate();
        },

        _getDates: function(value) {
            var date_obj = new Date();
            var from_date, to_date = {};
            from_date = {
                day: date_obj.getDate(),
                month: date_obj.getMonth(),
                year: date_obj.getFullYear()
            };

            to_date.day = from_date.day;
            to_date.month = from_date.month;
            to_date.year = from_date.year;

            if (value == 'A') {
                from_date = to_date = {};
            } else if (value == 'D') {
                // default
            } else if (value == 'W') {
                from_date.day = date_obj.getUTCDate() - date_obj.getDay() + 1;
            } else if (value == 'M') {
                from_date.day = 1;
            } else if (value == 'Y') {
                from_date.day = 1;
                from_date.month = 0;
                from_date.year = date_obj.getFullYear();
            } else if (value == 'LD') {
                from_date.day = date_obj.getUTCDate() - 1;
                to_date.day = date_obj.getUTCDate() - 1;
            } else if (value == 'HH') {
                from_date.day = date_obj.getUTCDate() - 1;
            } else if (value == 'LW') {
                from_date.day = date_obj.getUTCDate() - (date_obj.getDay() + 6);
                to_date.day = date_obj.getUTCDate() - date_obj.getDay();
            } else if (value == 'LM') {
                from_date.month = date_obj.getMonth() - 1;
                from_date.day = 1;
                var m_date = from_date.month < 0 ? from_date.month + 12 : from_date.month;
                var y_date = from_date.month < 0 ? from_date.year - 1 : from_date.year;
                to_date.day = methods._daysInMonth(m_date, y_date);
                to_date.month = m_date;
                to_date.year = y_date;
            } else if (value == 'LY') {
                from_date.year = to_date.year = date_obj.getFullYear() - 1;
                from_date.month = 0;
                from_date.day = 1;
                to_date.month = 11;
                to_date.day = methods._daysInMonth(to_date.month, to_date.year);
            } else if (value == 'HM') {
                from_date.day -= 30;
            } else if  (value == 'HW') {
                from_date.day -= 7;
            }

            if (from_date.day <= 0) {
                from_date.month -= 1;
                if (from_date.month < 0) {
                    from_date.year -= 1;
                    from_date.month += 12;
                }
                from_date.day += methods._daysInMonth(from_date.month, from_date.year);
            }

            if (from_date.month < 0) {
                from_date.year -= 1;
                from_date.month += 12;
            }

            return {
                from: (from_date.year ? new Date(from_date.year, from_date.month, from_date.day) : null),
                to: (to_date.year ? new Date(to_date.year, to_date.month, to_date.day) : null)
            };
        }
    };

    $.fn.cePeriodSelector = function(method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if ( typeof method === 'object' || ! method ) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('ty.periodselector: method ' +  method + ' does not exist');
        }
    };
})(Tygh.$);
