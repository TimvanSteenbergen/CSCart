(function(_, $) {
    var methods = {
	    init: function(params) {
	    	var self = $(this);
	    	var ranges = [_.tr("today")];
	    	$.loadCss(['design/backend/css/lib/daterangepicker/daterangepicker.css']);

	    	if (typeof(moment) == 'undefined') {
	    		var scripts_to_load = [$.getScript("js/lib/daterangepicker/moment.min.js"), $.getScript("js/lib/daterangepicker/daterangepicker.js")];
				 $.when.apply(this, scripts_to_load).then (function() {
					return self.ceDateRangePicker();
	             })
	             return false;
	        }
    		moment.lang(_.tr("default_lang"), {
    		monthsShort : [_.tr("month_name_abr_1"), _.tr("month_name_abr_2"), _.tr("month_name_abr_3"), _.tr("month_name_abr_4"), _.tr("month_name_abr_5"), _.tr("month_name_abr_6"), _.tr("month_name_abr_7"), _.tr("month_name_abr_8"), _.tr("month_name_abr_9"), _.tr("month_name_abr_10"), _.tr("month_name_abr_11"), _.tr("month_name_abr_12")]
			});
			moment.lang(_.tr("default_lang"));
	        var default_params = {
	        	ranges: {
	    		},
	    		startDate: moment(_.time_from * 1000).startOf('day'),
        		endDate: moment(_.time_to * 1000).startOf('day'),
        		locale: {
		            applyLabel: _.tr("apply"),
		            clearLabel: _.tr("clear"),
		            fromLabel: _.tr("from"),
		            toLabel: _.tr("to"),
		            customRangeLabel: _.tr("custom_range"),
		            monthNames: [_.tr("month_name_abr_1"), _.tr("month_name_abr_2"), _.tr("month_name_abr_3"), _.tr("month_name_abr_4"), _.tr("month_name_abr_5"), _.tr("month_name_abr_6"), _.tr("month_name_abr_7"), _.tr("month_name_abr_8"), _.tr("month_name_abr_9"), _.tr("month_name_abr_10"), _.tr("month_name_abr_11"), _.tr("month_name_abr_12")],
		            daysOfWeek: [_.tr("weekday_abr_0"), _.tr("weekday_abr_1"), _.tr("weekday_abr_2"), _.tr("weekday_abr_3"), _.tr("weekday_abr_4"), _.tr("weekday_abr_5"), _.tr("weekday_abr_6")]
        		},
       			format: _.tr["format"]
	        };
	        default_params['ranges'][_.tr('today')] = [moment().startOf('day'), moment().endOf('day')];
	        default_params['ranges'][_.tr('yesterday')] = [moment().subtract('days', 1).startOf('day'), moment().subtract('days', 1).endOf('day')];
	        default_params['ranges'][_.tr('this_month')] = [moment().startOf('month'), moment().endOf('month')];
	        default_params['ranges'][_.tr('last_month')] = [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')];
	        default_params['ranges'][_.tr('this_year')] = [moment().startOf('year'), moment().endOf('year')];
	        default_params['ranges'][_.tr('last_year')] = [moment().subtract('year', 1).startOf('year'), moment().subtract('year', 1).endOf('year')];

	        $.extend(default_params, params);
	        return this.each(function() {

	            var elm = $(this);
	            var params = default_params;
	            elm.daterangepicker(params,
	            	function(start, end) {
						var self = elm;
						var $ = Tygh.$;

						start = !start ? moment(_.time_from * 1000).startOf('day') : start;
						end = !end ? moment(_.time_to * 1000).startOf('day') : end;

					    $('span', self).html(start.format('MMM D, YYYY') + ' — ' + end.format('MMM D, YYYY'));
					    if (self.data('ca-target-url') && self.data('ca-target-id')) {
					    	$.ceAjax('request', $.attachToUrl(self.data('ca-target-url'), 'time_from=' + (start.valueOf() / 1000) + '&time_to=' + (parseInt(end.valueOf() / 1000))), {
				    			result_ids: self.data('ca-target-id'),
				    			caching: false,
				    			force_exec: true,
				    			callback: function(id) {
				    				Tygh.$('.reportrange').each(function(){
				    					self.ceDateRangePicker(elm);
				    				});
				    			}
					    	});
					    }
					}
	            );
	        });
	    }
	};
	$.fn.ceDateRangePicker = function(method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if ( typeof method === 'object' || ! method ) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('ty.tooltip: method ' +  method + ' does not exist');
        }
    };

    //<![CDATA[
	$(document).ready(function() {
		$('.cm-date-range').ceDateRangePicker();
	});
//]]>
}(Tygh, Tygh.$));
