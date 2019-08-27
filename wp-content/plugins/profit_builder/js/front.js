function FixTop(StickToTopDiv) {
    if (StickToTopDiv.length) {
        var $FixTopHeight = jQuery('#pbuilder_body_frame').contents().find('.pbuilder_row_stick_top').height();
        $FixTopHeight = $FixTopHeight + 10;
        jQuery('#pbuilder_body_frame').contents().find('.pb_fix_top').css("margin-top", $FixTopHeight + 'px');
    }
}


/**
 * jQuery JSON plugin v2.6.0
 * https://github.com/Krinkle/jquery-json
 *
 * @author Brantley Harris, 2009-2011
 * @author Timo Tijhof, 2011-2016
 * @source This plugin is heavily influenced by MochiKit's serializeJSON, which is
 *         copyrighted 2005 by Bob Ippolito.
 * @source Brantley Harris wrote this plugin. It is based somewhat on the JSON.org
 *         website's http://www.json.org/json2.js, which proclaims:
 *         "NO WARRANTY EXPRESSED OR IMPLIED. USE AT YOUR OWN RISK.", a sentiment that
 *         I uphold.
 * @license MIT License <https://opensource.org/licenses/MIT>
 */
(function (factory) {
	if (typeof define === 'function' && define.amd) {
		// AMD
		define(['jquery'], factory);
	} else if (typeof exports === 'object') {
		// CommonJS
		factory(require('jquery'));
	} else {
		// Browser globals
		factory(jQuery);
	}
}(function ($) {
	'use strict';

	var escape = /["\\\x00-\x1f\x7f-\x9f]/g,
		meta = {
			'\b': '\\b',
			'\t': '\\t',
			'\n': '\\n',
			'\f': '\\f',
			'\r': '\\r',
			'"': '\\"',
			'\\': '\\\\'
		},
		hasOwn = Object.prototype.hasOwnProperty;

	/**
	 * jQuery.toJSON
	 * Converts the given argument into a JSON representation.
	 *
	 * @param o {Mixed} The json-serializable *thing* to be converted
	 *
	 * If an object has a toJSON prototype, that will be used to get the representation.
	 * Non-integer/string keys are skipped in the object, as are keys that point to a
	 * function.
	 *
	 */
	$.toJSON = typeof JSON === 'object' && JSON.stringify ? JSON.stringify : function (o) {
		if (o === null) {
			return 'null';
		}

		var pairs, k, name, val,
			type = $.type(o);

		if (type === 'undefined') {
			return undefined;
		}

		// Also covers instantiated Number and Boolean objects,
		// which are typeof 'object' but thanks to $.type, we
		// catch them here. I don't know whether it is right
		// or wrong that instantiated primitives are not
		// exported to JSON as an {"object":..}.
		// We choose this path because that's what the browsers did.
		if (type === 'number' || type === 'boolean') {
			return String(o);
		}
		if (type === 'string') {
			return $.quoteString(o);
		}
		if (typeof o.toJSON === 'function') {
			return $.toJSON(o.toJSON());
		}
		if (type === 'date') {
			var month = o.getUTCMonth() + 1,
				day = o.getUTCDate(),
				year = o.getUTCFullYear(),
				hours = o.getUTCHours(),
				minutes = o.getUTCMinutes(),
				seconds = o.getUTCSeconds(),
				milli = o.getUTCMilliseconds();

			if (month < 10) {
				month = '0' + month;
			}
			if (day < 10) {
				day = '0' + day;
			}
			if (hours < 10) {
				hours = '0' + hours;
			}
			if (minutes < 10) {
				minutes = '0' + minutes;
			}
			if (seconds < 10) {
				seconds = '0' + seconds;
			}
			if (milli < 100) {
				milli = '0' + milli;
			}
			if (milli < 10) {
				milli = '0' + milli;
			}
			return '"' + year + '-' + month + '-' + day + 'T' +
				hours + ':' + minutes + ':' + seconds +
				'.' + milli + 'Z"';
		}

		pairs = [];

		if ($.isArray(o)) {
			for (k = 0; k < o.length; k++) {
				pairs.push($.toJSON(o[k]) || 'null');
			}
			return '[' + pairs.join(',') + ']';
		}

		// Any other object (plain object, RegExp, ..)
		// Need to do typeof instead of $.type, because we also
		// want to catch non-plain objects.
		if (typeof o === 'object') {
			for (k in o) {
				// Only include own properties,
				// Filter out inherited prototypes
				if (hasOwn.call(o, k)) {
					// Keys must be numerical or string. Skip others
					type = typeof k;
					if (type === 'number') {
						name = '"' + k + '"';
					} else if (type === 'string') {
						name = $.quoteString(k);
					} else {
						continue;
					}
					type = typeof o[k];

					// Invalid values like these return undefined
					// from toJSON, however those object members
					// shouldn't be included in the JSON string at all.
					if (type !== 'function' && type !== 'undefined') {
						val = $.toJSON(o[k]);
						pairs.push(name + ':' + val);
					}
				}
			}
			return '{' + pairs.join(',') + '}';
		}
	};

	/**
	 * jQuery.evalJSON
	 * Evaluates a given json string.
	 *
	 * @param str {String}
	 */
	$.evalJSON = typeof JSON === 'object' && JSON.parse ? JSON.parse : function (str) {
		/*jshint evil: true */
		return eval('(' + str + ')');
	};

	/**
	 * jQuery.secureEvalJSON
	 * Evals JSON in a way that is *more* secure.
	 *
	 * @param str {String}
	 */
	$.secureEvalJSON = typeof JSON === 'object' && JSON.parse ? JSON.parse : function (str) {
		var filtered =
			str
			.replace(/\\["\\\/bfnrtu]/g, '@')
			.replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']')
			.replace(/(?:^|:|,)(?:\s*\[)+/g, '');

		if (/^[\],:{}\s]*$/.test(filtered)) {
			/*jshint evil: true */
			return eval('(' + str + ')');
		}
		throw new SyntaxError('Error parsing JSON, source is not valid.');
	};

	/**
	 * jQuery.quoteString
	 * Returns a string-repr of a string, escaping quotes intelligently.
	 * Mostly a support function for toJSON.
	 * Examples:
	 * >>> jQuery.quoteString('apple')
	 * "apple"
	 *
	 * >>> jQuery.quoteString('"Where are we going?", she asked.')
	 * "\"Where are we going?\", she asked."
	 */
	$.quoteString = function (str) {
		if (str.match(escape)) {
			return '"' + str.replace(escape, function (a) {
				var c = meta[a];
				if (typeof c === 'string') {
					return c;
				}
				c = a.charCodeAt();
				return '\\u00' + Math.floor(c / 16).toString(16) + (c % 16).toString(16);
			}) + '"';
		}
		return '"' + str + '"';
	};

}));



(function ($) {
    var pbuilder_shortcode_sw = false;
    var pbuilder_timer_id;
    var rows_columns = '';
    window.pbuilder_changes_made = false;


    $(document).ready(function () {

        localStorage.clear();
        document.addEventListener('keydown', function (event) {
            if (event.keyCode == 89 && event.ctrlKey) {
                redo();
            }
            else if (event.keyCode == 90 && event.ctrlKey) {
                undo();
            }
        }, true);
        if (pbuilder_sw == 'on' && pbuilder_user) {
            var html = '<div class="pbuilder_header">'
            html += '<a href="#" title="Save Page" class="pbuilder_save"><img src="' + pbuilder_url + 'images/ajax-loader.gif" alt="" class="save_loader" /><i class="fa fa-floppy-o" aria-hidden="true"></i></a>';
            html += '<a href="#" title="Save Template" class="pbuilder_save_template"><span class="fa-stack fa-lg"><i class="fa fa-floppy-o fa-stack-2x"></i><i class="fa fa-floppy-o fa-stack-1x"></i></span></a>';
            html += '<a href="#" title="Load Template" class="pbuilder_load"><i class="fa fa-folder-open" aria-hidden="true"></i></a>';
            html += '<a href="#" title="Import Template" class="pbuilder_import"><span class="fa-stack fa-lg"><i class="fa fa-file-code-o fa-stack-2x"></i><i class="fa  fa-arrow-circle-right fa-stack-1x"></i></span><span>Import Template</span></a>';
            html += '<a href="#" title="Export Template" class="pbuilder_export"><span class="fa-stack fa-lg"><i class="fa fa-file-code-o fa-stack-2x"></i><i class="fa  fa-arrow-circle-right fa-stack-1x"></i></span><span>Export Template</span></a>';
            html += '<a href="#" title="Export HTML" class="pbuilder_exporthtml"><span class="fa-stack fa-lg"><i class="fa fa-file-code-o fa-stack-2x"></i><i class="fa  fa-arrow-circle-right fa-stack-1x"></i></span><span>Export HTML</span></a>';
            html += '<a href="' + frbPagePermalink + '" title="Preview Page" class="pbuilder_permalink_preview_trigger" target="_blank"><i class="fa fa-eye" aria-hidden="true"></i><span>Preview Page</span></a>';
            html += '<a href="#" class="pbuilder_toggle" title="Show / Hide Controls"><i class="fa fa-sliders" aria-hidden="true"></i><span>Show / Hide Controls</span></a>';
            html += '<a href="#" class="pbuilder_toggle_zoom_trigger" title="Toggle Zoom"><i class="fa fa-search-plus"></i></a>';
            html += '<a href="#" id="undo" class="pbuilder_permalink_preview_trigger" title="Undo"><i id="fa_undo" class="fa fa-undo"></i></a>';
            html += '<a href="#" id="redo" class="pbuilder_permalink_preview_trigger" title="Redo"><i id="fa_redo" class="fa fa-repeat"></i></a>';
            html += '<a href="#" class="pbuilder_toggle_screen" data-width="1200" title="Desktop View"><i class="fa fa-desktop fawesome"></i></a>';
            html += '<a href="#" class="pbuilder_toggle_screen" data-width="960" title="Laptop View"><i class="fa fa-laptop fawesome"></i></a>';
            html += '<a href="#" class="pbuilder_toggle_screen" data-width="768" title="Tablet View"><i class="fa fa-tablet fawesome"></i></a>';
            html += '<a href="#" class="pbuilder_toggle_screen" data-width="340" title="Mobile View"><i class="fa fa-mobile-phone fawesome"></i></a>';
            html += '<a href="#" class="pbuilder_add_row_popup_trigger"><i class="fa fa-plus"></i><span>Add new Row</span></a>';

            html += '<a href="#" class="pbuilder_show_revisions"><i class="fa fa-clock-o"></i><span>Revisions</span></a>';
            if(pbuilderl==2){
              html += '<a href="#" class="pbuilder_show_abtest"><i class="fa fa-language" aria-hidden="true"></i><span>A/B Test</span></a>';
              html += '<a href="#" class="pbuilder_show_funnel"><i class="fa fa-filter" aria-hidden="true"></i><span>Funnel</span></a>';
            }
      
      
            html += '<a href="'+frbPageEditlink+'" class="pbuilder_close_editor"><span>Close ProfitBuilder</span><i class="fa fa-close"></i></a>';



            html += '</div>';

            html += '<div id="pbuilder_add_shortcode_popup" class="pbuilder_popup">';
            for (var x in pbuilder_main_menu) {
                pbuilder_main_menu[x]['type'] = 'shortcode-popup';
                var newControl = new pbuilderControl(x, pbuilder_main_menu[x]);
                html += newControl.html();
            }
            html += '<a href="#" class="pbuilder_gradient pbuilder_button pbuilder_popup_close right">Close</a></div>';
            html += '<div id="pbuilder_add_row_popup" class="pbuilder_popup"><span class="frb_headline">Chose Row Type:</span><div class="clear"></div>';
            for (var x in pbuilder_rows) {
                var newRow = pbuilder_rows[x];
                html += '<a href="#' + x + '" class="pbuilder_row_button pbuilder_gradient" title="' + newRow.label + '"><img src="' + newRow.image + '" alt="" /></a>';
            }
            html += '<div class="clear"></div><a href="#" class="pbuilder_gradient pbuilder_button">Close</a></div>';
            $('#pbuilder_body').css({borderTopWidth: 37});
            $('body').append(html);
//			$('#pbuilder_editor-tmce').trigger('click');
        }
        else if (pbuilder_showall && pbuilder_sw == 'on') {
            var html = '<div class="pbuilder_header">'
            html += '<a href="#" class="pbuilder_disabled"><img src="' + pbuilder_url + 'images/icons/save-page.png" alt="" /><span>Save Page</span></a>';
            html += '<a href="#" class="pbuilder_disabled"><img src="' + pbuilder_url + 'images/icons/save-page-template.png" alt="" /><span>Save as Page Template</span></a>';
            html += '<a href="#" class="pbuilder_load"><img src="' + pbuilder_url + 'images/icons/load-page-template.png" alt="" /><span>Load Page</span></a>';
            html += '<a href="#" class="pbuilder_disabled"><img src="' + pbuilder_url + 'images/icons/import.png" alt="" /><span>Import Template</span></a>';
            html += '<a href="#" class="pbuilder_disabled"><img src="' + pbuilder_url + 'images/icons/export.png" alt="" /><span>Export Template</span></a>';
            html += '<a href="' + frbPagePermalink + '" class="pbuilder_permalink_preview_trigger" target="_blank"><i class="fa fa-link"></i><span>Preview Page</span></a>';
            html += '<a href="#" class="pbuilder_toggle"><img src="' + pbuilder_url + 'images/icons/save-hide-builder-controls.png" alt="" /><span>Show / Hide Builder Controls</span></a>';
            html += '<a href="#" class="pbuilder_toggle_zoom_trigger"><i class="fa fa-search"></i><span>Toggle Zoom</span></a>';
            html += '<a href="#" class="pbuilder_toggle_screen" data-width="1200"><i class="fa fa-desktop fawesome"></i></a>';
            html += '<a href="#" class="pbuilder_toggle_screen" data-width="960"><i class="fa fa-laptop fawesome"></i></a>';
            html += '<a href="#" class="pbuilder_toggle_screen" data-width="768"><i class="fa fa-tablet fawesome"></i></a>';
            html += '<a href="#" class="pbuilder_toggle_screen" data-width="340"><i class="fa fa-mobile-phone fawesome"></i></a>';
            html += '<a href="#" class="pbuilder_add_row_popup_trigger"><i class="fa fa-plus"></i><span>Add new Row</span></a>';
            html += '</div>';

            html += '<div id="pbuilder_add_shortcode_popup" class="pbuilder_popup">';
            for (var x in pbuilder_main_menu) {
                pbuilder_main_menu[x]['type'] = 'shortcode-popup';
                var newControl = new pbuilderControl(x, pbuilder_main_menu[x]);
                html += newControl.html();
            }
            html += '<a href="#" class="pbuilder_gradient pbuilder_button pbuilder_popup_close right">Close</a></div>';
            html += '<div id="pbuilder_add_row_popup" class="pbuilder_popup"><span class="frb_headline">Chose Row Type:</span><div class="clear"></div>';
            for (var x in pbuilder_rows) {
                var newRow = pbuilder_rows[x];
                html += '<a href="#' + x + '" class="pbuilder_row_button pbuilder_gradient" title="' + newRow.label + '"><img src="' + newRow.image + '" alt="" /></a>';
            }
            html += '<div class="clear"></div><a href="#" class="pbuilder_gradient pbuilder_button">Close</a></div>';
            $('#pbuilder_body').css({borderLeftWidth: 0, borderTopWidth: 37});
            $('body').append(html);
        }
        $('#pbuilder_body_frame').ready(function () {
            if (pbuilder_sw == 'on' && (pbuilder_user || pbuilder_showall)) {
                pbuilderIframeInit(this);
            }

        });
    });
    function pbuilderIframeInit($this) {
        if (typeof $this.contentWindow == 'undefined' || typeof $this.contentWindow.jQuery === 'undefined' || typeof $this.contentWindow.jQuery.ui === 'undefined' || typeof $this.contentWindow.jQuery.ui.sortable === 'undefined' || typeof $this.contentWindow.jQuery.ui.draggable === 'undefined') {
            setTimeout(function () {
                pbuilderIframeInit($('#pbuilder_body_frame')[0])
            }, 1000);
        }
        else {
            var loc = '',
                    win = $this.contentWindow,
                    doc = win.document,
                    body = doc.body;
            win.jQuery(doc).on('click', 'a', function () {
                if (typeof win.jQuery(this).attr('href') != 'undefined' && win.jQuery(this).attr('href') != '' && win.jQuery(this).attr('href').substr(0, 1) != '#')
                    loc = win.jQuery(this).attr('href');
            });
            $(win).unload(function () {
                if (loc != '' && loc != '#') {
                    window.location = loc;
                }
            });
            var $iframe = $($this).contents();
            pbuilderFrameControls($iframe);
            pbuilderSortableInit(win.jQuery('#pbuilder_content .pbuilder_row'));
            pbuilderControlsInit(win.jQuery, doc);
            pbuilderRefreshDragg(win.jQuery);
            $('#pbuilder_frame_cover').hide();
            if (!pbuilder_user)
                $('.pbuilder_toggle').trigger('click');
            if (pbuilder_sw == 'on' && !pbuilder_user && $('.pbuilder_toggle').hasClass('active')) {
                $('.pbuilder_toggle').trigger('click');
            }
            $("[class*=timed-row-]", $iframe).each(function () {
                $(this).show();
            });
        }
        //				Nav Away Failsafe
        var iWindow = $('#pbuilder_body_frame')[0].contentWindow;
        
        $(iWindow).on('beforeunload', function (e) {
            if(window.pbuilder_changes_made == true){
              var message = 'Are you sure you want to leave the page? Any unsaved data will be lost.';
              e.returnValue = message;
              return message;
            }
        });
        setTimeout(function () {
            checkHTML();
        }, 3000);
    }
    function pbuilderFrameControls($iframe) {
        $iframe.find('#pbuilder_wrapper .pbuilder_row').each(function () {
            var parentRow = $(this).parent().closest('.pbuilder_row');
            if (parentRow.length <= 0 || (parentRow.closest('#pbuilder_wrapper').length <= 0 && $(this).closest('#pbuilder_wrapper').length > 0)) {
                if (!$(this).hasClass('pbuilder_sidebar'))
                    $(this).prepend('<div class="pbuilder_row_controls pbuilder_gradient"><a href="#" class="pbuilder_edit" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a><a class="pbuilder_drag_handle" href="#" title="Move"><i class="fa fa-arrows" aria-hidden="true"></i></a><a class="pbuilder_clone" href="#" title="Clone"><i class="fa fa-clone" aria-hidden="true"></i></a><a class="pbuilder_copy" href="#" title="Copy"><i class="fa fa-files-o" aria-hidden="true"></i></a><a class="pbuilder_paste" href="#" title="Paste"><i class="fa fa-clipboard" aria-hidden="true"></i></a><a class="pbuilder_delete" href="#" title="delete"><i class="fa fa-trash" aria-hidden="true"></i></a><a class="pbuilder_new_row_button" href="#" title="Add new row"><i class="fa fa-plus-square" aria-hidden="true"></i></a></div>');
                else
                    $(this).prepend('<div class="pbuilder_row_controls"><span class="pbuilder_sidebar_label">Sidebar</span></div>');
            }
        });
        $iframe.find('#pbuilder_wrapper .pbuilder_column').each(function () {
            var parentCol = $(this).parent().closest('.pbuilder_column');
            if (parentCol.length <= 0 ||
                    (parentCol.closest('#pbuilder_wrapper').length <= 0 && $(this).closest('#pbuilder_wrapper').length > 0) ||
                    (parentCol.length > 0 && parentCol.closest('#pbuilder_wrapper').length <= 0)) {
                        $(this).prepend('<div class="pbuilder_column_controls pbuilder_gradient"><a href="#" class="pbuilder_edit" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a></div>');
                        $(this).append('<div class="pbuilder_drop_borders"><div class="pbuilder_empty_content"><div class="pbuilder_add_shortcode pbuilder_gradient">+</div><span>Add Shortcode</span></div></div>');
            }
        });
        $iframe.find('#pbuilder_wrapper .pbuilder_module').each(function () {
            if ($(this).parent().closest('.pbuilder_module').length <= 0) {
                $(this).wrapInner('<div class="pbuilder_module_content" />');
                $(this).prepend('<img class="pbuilder_module_loader" src="' + pbuilder_url + 'images/module-loader-new.gif" /><div class="pbuilder_module_controls pbuilder_gradient"><a href="#" class="pbuilder_edit" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a><a href="#" class="pbuilder_drag" title="Drag"><i class="fa fa-arrows" aria-hidden="true"></i></a><a href="#" class="pbuilder_clone" title="Clone"><i class="fa fa-clone" aria-hidden="true"></i></a><a class="pbuilder_copy" href="#" title="Copy"><i class="fa fa-files-o" aria-hidden="true"></i></a><a class="pbuilder_paste" href="#" title="Paste"><i class="fa fa-clipboard" aria-hidden="true"></i></a><a href="#" class="pbuilder_delete" title="Delete Element"><i class="fa fa-trash" aria-hidden="true"></i></a><a class="pbuilder_add_shortcode_column" href="#" title="Add Shortcode After Element"><i class="fa fa-plus-square" aria-hidden="true"></i></a></div>');

				/*
				<a href="#" class="pbuilder_edit" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>\
					<a class="pbuilder_drag_handle" href="#" title="Move"><i class="fa fa-arrows" aria-hidden="true"></i></a>\
					<a class="pbuilder_clone" href="#" title="Clone"><i class="fa fa-clone" aria-hidden="true"></i></a>\
					<a class="pbuilder_copy" href="#" title="Copy"><i class="fa fa-files-o" aria-hidden="true"></i></a>\
					<a class="pbuilder_paste" href="#" title="Paste"><i class="fa fa-clipboard" aria-hidden="true"></i></a>\
					<a class="pbuilder_delete" href="#" title="delete"><i class="fa fa-trash" aria-hidden="true"></i></a>\
					*/
            }
        });

        if(typeof pbuilder_items['rows'] !== 'object'){
          var rows = '<div class="pbuilder_row_holder" style="display:none;">';
        } else {
          var rows = '<div class="pbuilder_row_holder">';
        }
        rows+='<a href="#" class="pbuilder_new_row pbuilder_gradient"><i class="fa fa-plus" aria-hidden="true"></i> Add new row</a>';
        rows+='<div class="pbuilder_row_holder_inner">';

        for (var x in pbuilder_rows) {
            var newRow = pbuilder_rows[x];
            rows_columns += '<a href="#' + x + '" class="pbuilder_row_button pbuilder_gradient" title="' + newRow.label + '"><img src="' + newRow.image + '" alt="" /></a>';
        }



		rows += rows_columns + '<div style="clear:both;"></div></div></div>';
        $iframe.find('#pbuilder_wrapper').addClass('edit').children('#pbuilder_content_wrapper').append(rows);
        $iframe.find('#pbuilder_content').sortable({
            items: "> div",
			scroll : false,
            handle: '.pbuilder_row_controls .pbuilder_drag_handle',
            stop: function (event, ui) {
                pbuilder_items['rowOrder'] = [];
                $iframe.find('#pbuilder_content .pbuilder_row').each(function (index) {
                    pbuilder_items['rowOrder'][index] = parseInt($(this).attr('data-rowid'));

                });
                window.pbuilder_changes_made = true;
                //$($iframe).off('mousemove');
            }
        });
    }
    // pbuilderControl Class
    function pbuilderControl(name, values) {
        this.name = name;
        this.values = values;
        
        this.html = function () {
            var hideCond = '';
            var halfControl = (typeof this.values['half_column'] != 'undefined' && this.values['half_column'] == 'true' ? ' pbuilder_half_control' : '');
            var labelWidth = 0.5;
            var controlWidth = 0.5;
            if (this.values.type == 'image' || this.values.type == 'textarea') {
                labelWidth = 1;
                controlWidth = 1;
            }
            labelWidth = (typeof this.values['label_width'] != 'undefined' ? this.values['label_width'] : labelWidth);
            controlWidth = (typeof this.values['control_width'] != 'undefined' ? this.values['control_width'] : controlWidth);
            var wrapper =
                    '<div class="pbuilder_control' + (typeof this.values['controlclass'] != 'undefined' ? ' ' + this.values['controlclass'] + ' ' : '') + (typeof this.values['hide_if'] != 'undefined' ? ' pbuilder_hidable' : '') + (typeof this.values['class'] != 'undefined' ? ' ' + this.values['class'] : '') + halfControl + '">' +
                    (typeof (this.values.label) != 'undefined' ?
                            '<div class="pbuilder_label" style="width:' + (labelWidth * 100) + '%;' + (labelWidth == 0 ? 'display:none;' : '') + '">' +
                            '<label for="' + this.name + '">' + this.values.label + ' </label>' +
                            (typeof (this.values.desc) != 'undefined' ? '<span class="pbuilder_desc pbuilder_gradient">' + this.values.desc + '</span>' : '') +
                            '</div>' : '') +
                    '<div class="pbuilder_control_content" style="width:' + (controlWidth * 100) + '%">';
            var wrapperClose = (typeof (this.values['desc']) != 'undefined' && this.values['desc'] != '' ? '<span class="pbuilder_control_desc">' + this.values['desc'] + '</span>' : '') + '</div><div style="clear:both;"></div></div>';
            var html = '';
            switch (this.values.type) {
                case 'div' :
                    var id = (typeof this.values['id'] != 'undefined' && this.values['id'] != '' ? ' id="' + this.values['id'] + '"' : '');
                    //html += wrapper;
                    html = '<div class="pbuilder_div" ' + id + ' ></div>';
                    //html += wrapperClose;
                    break;
                case 'imagepreview' :
                    var divid = (typeof this.values['divid'] != 'undefined' && this.values['divid'] != '' ? ' id="' + this.values['divid'] + '"' : '');
                    var imgid = (typeof this.values['imgid'] != 'undefined' && this.values['imgid'] != '' ? ' id="' + this.values['imgid'] + '"' : '');
                    var std = (typeof this.values['std'] != 'undefined' && this.values['std'] != '' ? this.values['std'] : pbuilder_url + 'images/blankpreview.png');
                    //html += wrapper;
                    html = '<div class="pbuilder_image_preview_div pbuilder_control " ' + divid + ' ><img src="' + std + '" class="pbuilder_image_preview_img" ' + imgid + ' /></div>';
                    //html += wrapperClose;
                    break;
                case 'input' :
                    var str_value = '';


                    if (typeof (this.values.std) != 'undefined' && this.values.std != '') {
                        if (typeof (this.values.std) == 'string'){
                            pbuilder_timer_id=this.values.std.replace(/\%sqs\%+(.*?)\%sqe\%+/g, "[$1]");
            							str_value = ' value="' + this.values.std.replace(/\%sqs\%+(.*?)\%sqe\%+/g, "[$1]") + '" ';
            						}
                        else {
            							pbuilder_timer_id=this.values.std;
                                        str_value = ' value="' + this.values.std + '" ';
            						}
                    }

                    html += wrapper;
                    html += '<input class="pbuilder_input pbuilder_text ' + (typeof this.values['hide_if'] != 'undefined' ? ' pbuilder_hidable_control' : '') + '" name="' + this.name + '" id="pbuilder_' + this.values.type + '_' + this.name + '" ' + str_value + '/>';
                    html += wrapperClose;
                    break;

                case 'textarea' :
                    html += wrapper;
                    html += '<textarea class="pbuilder_textarea pbuilder_text ' + (typeof this.values['hide_if'] != 'undefined' ? ' pbuilder_hidable_control' : '') + '" name="' + this.name + '" id="pbuilder_' + this.values.type + '_' + this.name + '">' + (typeof (this.values.std) != 'undefined' && this.values.std != '' ? (this.values.std.replace(/\%sqs\%+(.*?)\%sqe\%+/g, "[$1]").replace(/&quot;/g,'"').replace(/&/g,'&amp;')) : '') + '</textarea>';
                    html += '<a href="#" class="pbuilder_wp_editor_button pbuilder_button pbuilder_gradient">Open in WP Editor</a><div style="clear:both;"></div>';
					          html += '<div id="container"></div>';
                    html += wrapperClose;

                    break;

          				case 'marginpadding' :
          					var sc_values = this.values.std.split('|');
          					html += wrapper;
                              html += '<div class="pbuilder_marginpadding">';
          					html += '<div class="pbuilder_marginpadding_center_padding"></div>';
          					html += '<div class="pbuilder_marginpadding_center"><i class="fa fa-list fa-2x" aria-hidden="true"></i></div>';


          					html += '<input name="mp_margin_top" type="text" value="'+sc_values[0]+'" id="pbuilder_marginpadding_margin_top" />';
          					html += '<input name="mp_margin_right" type="text" value="'+sc_values[1]+'" id="pbuilder_marginpadding_margin_right" />';
          					html += '<input name="mp_margin_bottom" type="text" value="'+sc_values[2]+'" id="pbuilder_marginpadding_margin_bottom" />';
          					html += '<input name="mp_margin_left" type="text" value="'+sc_values[3]+'" id="pbuilder_marginpadding_margin_left" />';

          					html += '<input name="mp_padding_top" type="text" value="'+sc_values[4]+'" id="pbuilder_marginpadding_padding_top" />';
          					html += '<input name="mp_padding_right" type="text" value="'+sc_values[5]+'" id="pbuilder_marginpadding_padding_right" />';
          					html += '<input name="mp_padding_bottom" type="text" value="'+sc_values[6]+'" id="pbuilder_marginpadding_padding_bottom" />';
          					html += '<input name="mp_padding_left" type="text" value="'+sc_values[7]+'" id="pbuilder_marginpadding_padding_left" />';


          					html += '</div>';
          					html += '<div style="clear:both;"></div>';
                              html += wrapperClose;
                              break;

          				case 'border' :
                    
          					var sc_values = this.values.std.split('|');
          					var border_advanced=sc_values[0];

          					var border_simple_width=sc_values[1];
          					var border_simple_style=sc_values[2];
          					var border_simple_color=sc_values[3];

          					var border_width={};
          					var border_style={};
          					var border_color={};

          					border_width.top=sc_values[4];
          					border_style.top=sc_values[5];
          					border_color.top=sc_values[6];

          					border_width.right=sc_values[7];
          					border_style.right=sc_values[8];
          					border_color.right=sc_values[9];

          					border_width.bottom=sc_values[10];
          					border_style.bottom=sc_values[11];
          					border_color.bottom=sc_values[12];

          					border_width.left=sc_values[13];
          					border_style.left=sc_values[14];
          					border_color.left=sc_values[15];

          					if(border_advanced == 'undefined') border_advanced='false';
          					if(border_simple_width == 'undefined') border_simple_width='0';
          					if(border_simple_style == 'undefined') border_simple_style='solid';
          					if(border_simple_color == 'undefined') border_simple_color='#000000';

          					if(border_width.top == 'undefined') border_width.top='0';
          					if(border_style.top == 'undefined') border_style.top='solid';
          					if(border_color.top == 'undefined') border_color.top='#000000';

          					if(border_width.right == 'undefined') border_width.right='0';
          					if(border_style.right == 'undefined') border_style.right='solid';
          					if(border_color.right == 'undefined') border_color.right='#000000';

          					if(border_width.bottom == 'undefined') border_width.bottom='0';
          					if(border_style.bottom == 'undefined') border_style.bottom='solid';
          					if(border_color.bottom == 'undefined') border_color.bottom='#000000';

          					if(border_width.left == 'undefined') border_width.left='0';
          					if(border_style.left == 'undefined') border_style.left='solid';
          					if(border_color.left == 'undefined') border_color.left='#000000';


          					html += wrapper;

          					html += '<div class="pbuilder_border_style_wrapper">';
          						html += '<div class="pbuilder_control"><div id="pbuilder_border_advanced" class="pbuilder_checkbox' + (typeof (border_advanced) != 'undefined' && border_advanced != '' && border_advanced == 'true' ? ' active' : '') + '"></div>';
          						html += '<input class="pbuilder_checkbox_input" name="pbuilder_border_advanced" style="display:none;" ' + (typeof (border_advanced) != 'undefined' && border_advanced == 'true' ? ' value="' + border_advanced + '"' : ' value="false"') + ' />';
          						html += '<div class="pbuilder_checkbox_label"><label for="pbuilder_border_advanced">Advanced </label><span class="pbuilder_desc pbuilder_gradient" style="display: none; opacity: 0;">check to set separate settings for top, left, right or bottom border</span></div>';
          						html += '</div>';
          						html += '<div style="clear:both;"></div>';

          						var def=0;
          						var min=0;
          						var max=40;
          						var std=0;
          						var step=1;
          						var unit='px';
          						var maxStr=''+max;
          						var border_styles=['solid','dashed','dotted','double','inset','outset','ridge','groove'];

          						html += '<div class="pbuilder_border_style_simple" '+( border_advanced == 'true' ? ' style="display:none;" ' : '' )+' style="color:#FFF">';
          						html += '<div style="clear:both;"></div>';

          						html += '<div class="pbuilder_control"><div class="pbuilder_label" style="width:50%"><label>Width:</label></div><div class="pbuilder_control_content" style="width:50%; text-align:right;">';
          						html += '<div class="pbuilder_number_bar_wrapper"><div class="pbuilder_number_bar" data-default="' + def + '" data-min="' + min + '" data-max="' + max + '" data-std="' + border_simple_width + '" data-step="' + step + '" data-unit="' + unit + '"></div></div><input class="pbuilder_number_amount pbuilder_input" name="mp_border_simple_width" id="pbuilder_border_simple_width" value="'+border_simple_width+'"/><div class="pbuilder_number_button pbuilder_gradient"></div><div style="clear:both;"></div>';
          						html += '</div></div>';


          						html += '<div class="pbuilder_control"><div class="pbuilder_label" style="width:50%"><label>Style:</label></div><div class="pbuilder_control_content" style="width:50%;">';
          							html += '<input name="mp_border_simple_style" id="pbuilder_border_simple_style" style="display:none;" value="'+border_simple_style+'" />';
          							html += '<div class="pbuilder_select" data-name="mp_border_simple_style">';
          							html += '<span>'+border_simple_style+'</span><div class="drop_button"></div>';
          							html += '<ul style="display:none">';

          							for(x in border_styles){
          								html += '<li><a href="#" data-value="'+border_styles[x]+'">'+border_styles[x]+'</a></li>';
          							}

          							html += '</ul>';
          							html += '<div class="clear"></div>';
          							html += '</div>';
          						html += '</div></div>';

          						html += '<div class="pbuilder_control"><div class="pbuilder_label" style="width:50%"><label>Color:</label></div><div class="pbuilder_control_content pbuilder_color_wrapper pbuilder_border_style_input" id="pbuilder_border_top_color">';
          						html += '<input name="mp_border_simple_color" class="pbuilder_color pbuilder_input" type="text" value="'+border_simple_color+'" />';


          						html += '</div></div>';

          						html += '</div>';


          						html += '<div class="pbuilder_border_style" '+( border_advanced != 'true' ? ' style="display:none;" ' : '' )+'>';
          							html += '<div class="pbuilder_border_style_center_padding"></div>';
          							html += '<div class="pbuilder_border_style_center"><i class="fa fa-list fa-2x" aria-hidden="true"></i></div>';

          							var border_positions=['top','right','bottom','left'];

          							for(p in border_positions){

          							  html += '<div class="pbuilder_control pbuilder_control_select" id="pbuilder_border_'+border_positions[p]+'_width">';
          							  html += '<div class="pbuilder_number_bar_wrapper"><div class="pbuilder_number_bar" data-default="' + def + '" data-min="' + min + '" data-max="' + max + '" data-std="'+border_width[border_positions[p]]+'" data-step="' + step + '" data-unit="' + unit + '"></div></div><input class="pbuilder_number_amount pbuilder_input" name="mp_border_'+border_positions[p]+'_width" value="'+border_width[border_positions[p]]+'"/><div class="pbuilder_number_button pbuilder_gradient"></div><div style="clear:both;"></div>';
          							  html += '</div>';

          							  html += '<div class="pbuilder_control_content pbuilder_control_select" id="pbuilder_border_'+border_positions[p]+'_style">';
          								  html += '<input name="mp_border_'+border_positions[p]+'_style" style="display:none;" value="'+border_style[border_positions[p]]+'" />';
          								  html += '<div class="pbuilder_select" data-name="mp_border_'+border_positions[p]+'_style">';
          								  html += '<span>'+border_style[border_positions[p]]+'</span><div class="drop_button"></div>';
          								  html += '<ul style="display:none">';

          								  for(x in border_styles){
          									  html += '<li><a href="#" data-value="'+border_styles[x]+'">'+border_styles[x]+'</a></li>';
          								  }

          								  html += '</ul>';
          								  html += '<div class="clear"></div>';
          								  html += '</div>';
          							  html += '</div>';


          							  html += '<div class="pbuilder_color_wrapper pbuilder_border_style_input" id="pbuilder_border_'+border_positions[p]+'_color">';
          							  html += '<input name="mp_border_'+border_positions[p]+'_color" class="pbuilder_color pbuilder_input" type="text" value="'+border_color[border_positions[p]]+'" />';
          							  html += '</div>';
          							}

          						html += '</div>';
          					html += '</div>';
          					html += '<div style="clear:both;"></div>';
                              html += wrapperClose;
                              break;


          				case 'checkbox' :
                              html +=
                                      '<div class="pbuilder_control' + halfControl + '">' +
                                      '<div class="pbuilder_checkbox' + (typeof (this.values.std) != 'undefined' && this.values.std != '' && this.values.std == 'true' ? ' active' : '') + '"></div>' +
                                      '<input class="pbuilder_checkbox_input' + (typeof this.values['hide_if'] != 'undefined' ? ' pbuilder_hidable_control' : '') + '" name="' + this.name + '" id="pbuilder_' + this.values.type + '_' + this.name + '" style="display:none;"' +
                                      (typeof (this.values.std) != 'undefined' && this.values.std == 'true' ? ' value="' + this.values.std + '"' : ' value="false"') + ' />' +
                                      '<div class="pbuilder_checkbox_label">' +
                                      (typeof (this.values.label) != 'undefined' ? '<label for="' + this.name + '">' + this.values.label + ' </label>' : '') +
                                      (typeof (this.values.desc) != 'undefined' ? '<span class="pbuilder_desc pbuilder_gradient">' + this.values.desc + '</span>' : '') +
                                      '</div><div style="clear:both;"></div>' +
                                      '</div>';
                              break;
                  case 'select' :
                    var options = this.values.options;

          					if(this.name == 'timer_parent'){
          						delete options[pbuilder_timer_id];
          					}
                    html += wrapper;
                    html += '<input class="' + (typeof this.values['hide_if'] != 'undefined' ? 'pbuilder_hidable_control' : '') + (typeof this.values['input_class'] != 'undefined' ? ' ' + this.values['input_class'] : '') + '" name="' + this.name + '" id="pbuilder_' + this.values.type + '_' + this.name + '" style="display:none;"';
                    html += (typeof this.values.std != 'undefined' && this.values.std != '' ? ' value="' + this.values.std + '"' : '');
                    var visibleSelect = '<div class="pbuilder_select' + (typeof this.values['search'] != 'undefined' && this.values['search'] == 'true' ? ' pbuilder_select_with_search' : '') + (typeof this.values['multiselect'] != 'undefined' && this.values['multiselect'] == 'true' ? ' pbuilder_select_multi' : '') + '" data-name="' + this.name + '">';
                    var count = 0;
                    if (typeof (this.values.multiselect) != 'undefined' && this.values.multiselect == 'true')
                        var explVal = this.values.std.split(',');
                    if ($.isEmptyObject(options)) {
                        if(this.name == 'timer_parent'){
                          visibleSelect += '<span>No Parent Timers</span>';
                        } else {
                          visibleSelect += '<span>-</span>';
                        }
                    } // fail-safe if no data fetch
                    for (var x in options) {
                        if (count == 0) {
                            html += (typeof this.values.std == 'undefined' || this.values.std != '' ? ' value="' + x + '"' : '');
                            if (typeof (this.values.multiselect) != 'undefined' && this.values.multiselect == 'true') {
                                if (typeof (this.values.std) != 'undefined' && this.values.std != '') {
                                    visibleSelect += '<span>';
                                    for (y in explVal) {
                                        if (y != 0)
                                            visibleSelect += ',';
                                        visibleSelect += options[explVal[y]];
                                    }
                                    visibleSelect += '</span>';
                                }
                                else {
                                    visibleSelect += '<span>' + options[x].replace(/\%sqs\%+(.*?)\%sqe\%+/g, "[$1]") + '</span>';
                                }
                            }
                            else {
                                visibleSelect +=
                                        '<span>' + (typeof (this.values.std) != 'undefined' && this.values.std != '' ? typeof (options[this.values.std]) != 'undefined' ? options[this.values.std].replace(/\%sqs\%+(.*?)\%sqe\%+/g, "[$1]") : '' : options[x].replace(/\%sqs\%+(.*?)\%sqe\%+/g, "[$1]")) + '</span>';
                            }
                            visibleSelect +=
                                    '<div class="drop_button"></div>' +
                                    (typeof this.values['search'] != 'undefined' && this.values['search'] == 'true' ? '<input class="pbuilder_select_search" placeholder="Search..." value="" style="display:none" />' : '') +
                                    '<ul style="display:none">';
                            if (typeof (this.values.multiselect) != 'undefined' && this.values.multiselect == 'true') {
                                visibleSelect +=
                                        '<li><a href="#" data-value="' + x + '"' + ((explVal.indexOf(x) != -1) ? ' class="selected"' : '') + '>' + options[x].replace(/\%sqs\%+(.*?)\%sqe\%+/g, "[$1]") + '</a></li>';
                            }
                            else {
                                visibleSelect +=
                                        '<li><a href="#" data-value="' + x + '"' + ((typeof this.values.std == 'undefined' || this.values.std == '' || this.values.std == x) ? ' class="selected"' : '') + '>' + options[x].replace(/\%sqs\%+(.*?)\%sqe\%+/g, "[$1]") + '</a></li>';
                            }
                        }
                        else {
                            if (typeof (this.values.multiselect) != 'undefined' && this.values.multiselect == 'true') {
                                visibleSelect +=
                                        '<li><a href="#" data-value="' + x + '"' + ((explVal.indexOf(x) != -1) ? ' class="selected"' : '') + '>' + options[x].replace(/\%sqs\%+(.*?)\%sqe\%+/g, "[$1]") + '</a></li>';
                            }
                            else {
                                /*
                                 * Code Changed by Asim Ashraf - DevBatch
                                 * Reason: Javascript Cash on integer value. now integer changed to string.
                                 * Change: "options[x].replace(/\%sqs\%+(.*?)\%sqe\%+/g, "[$1]")" To "options[x].toString().replace(/\%sqs\%+(.*?)\%sqe\%+/g, "[$1]")"
                                 * Date: 11 Feb 2015
                                 */
                                visibleSelect += '<li><a href="#" data-value="' + x + '"' + (this.values.std == x ? ' class="selected"' : '') + '>' + options[x].toString().replace(/\%sqs\%+(.*?)\%sqe\%+/g, "[$1]") + '</a></li>';
                            }
                        }
                        count++;
                    }
                    html += ' />';
                    visibleSelect +=
                            '</ul>' +
                            '<div class="clear"></div>' +
                            '</div>';
                    html += visibleSelect;
                    html += wrapperClose;
                    break;
                case 'icon' :
                    var reg = /\bba\b/gi;
                    var dataMin = ((typeof this.values['notNull'] != 'undefined' && this.values['notNull'] == false) ? 'no-icon' : 'fa-adjust');
                    dataMin = dataMin.replace(reg, "fa");
                    var current = ((typeof (this.values.std) != 'undefined' && this.values.std != '' && this.values.std != null) ? this.values.std : dataMin);
                    current = current.replace(reg, "fa");
                    var prefix = current.substr(0, 2);
                    var old = false;
                    if (current.substr(2, 1) != '-') {
                        old = true;
                        prefix = 'fa';
                    }
                    html += wrapper;
                    html += '<input class="' + (typeof this.values['hide_if'] != 'undefined' ? ' pbuilder_hidable_control' : '') + '" type="hidden" name="' + this.name + '" id="pbuilder_' + this.values.type + '_' + this.name + '" data-min="' + dataMin + '" value="' + current + '" /><div class="pbuilder_icon_holder"><i class="' + (old ? 'old_icon ' : '') + prefix + ' ' + current + ' frb_icon"></i></div><a href="#" class="pbuilder_gradient pbuilder_icon_pick">Change</a>';
                    html += '<div style="clear:both;"></div><span class="pbuilder_icon_drop_arrow"></span><div class="pbuilder_icon_dropdown"><div class="pbuilder_icon_dropdown_tabs">';
                    var icon_drop_content = '<div class="pbuilder_icon_dropdown_scroll">';
                    for (var x in pbuilder_icons) {
                        if (x == 'noicon') {
                            if (typeof this.values['notNull'] != 'undefined' && this.values['notNull'] == false) {
                                html += '<span class="pbuilder_icon_noicon' + (prefix == pbuilder_icons[x]['prefix'] ? ' active' : '') + '">' + pbuilder_icons[x]['label'] + '</span>';
                            }
                        }
                        else {
                            html += '<span data-tabid="' + pbuilder_icons[x]['prefix'] + '" class="pbuilder_icon_tab' + (prefix == pbuilder_icons[x]['prefix'] ? ' active' : '') + '">' + pbuilder_icons[x]['label'] + '</span>';
                            icon_drop_content += '<div class="pbuilder_icon_dropdown_content' + (prefix == pbuilder_icons[x]['prefix'] ? ' active' : '') + '" data-tabid="' + pbuilder_icons[x]['prefix'] + '">';
                            for (var y in  pbuilder_icons[x]['icons']) {
                                icon_drop_content += '<a href="' + pbuilder_icons[x]['icons'][y] + '"><i class="' + pbuilder_icons[x]['prefix'] + ' ' + pbuilder_icons[x]['icons'][y] + ' frb_icon"></i></a>';
                            }
                            icon_drop_content += '<div style="clear:both;"></div></div>';
                        }
                    }
                    icon_drop_content += '</div>';
                    html += '</div>' + icon_drop_content;
                    html += '<div style="clear:both;"></div></div><div style="clear:both;"></div>';
                    html += wrapperClose;
                    break;
                case 'image' :
                    html += wrapper;
                    html += '<a html="' + this.name + '" class="pbuilder_image_button pbuilder_button pbuilder_gradient" data-input="pbuilder_' + this.values.type + '_' + this.name + '">Upload</a>';
                    html += '<div class="pbuilder_image_input"><input class="pbuilder_input' + (typeof this.values['hide_if'] != 'undefined' ? ' pbuilder_hidable_control' : '') + '" name="' + this.name + '" id="pbuilder_' + this.values.type + '_' + this.name + '" value="' + (typeof (this.values.std) != 'undefined' && this.values.std != '' ? this.values.std + '" />' : '" /><span>Enter image url...</span>') + '</div>';
                    html += '<div style="clear:both;"></div>';
                    html += wrapperClose;
                    break;
                case 'media_select' :
                    html += wrapper;
                    html += '<a html="' + this.name + '" class="pbuilder_media_select_button pbuilder_button pbuilder_gradient" data-input="pbuilder_' + this.values.type + '_' + this.name + '">+ Add Media</a>';
                    html += '<div class="pbuilder_media_select_input"><input class="pbuilder_input' + (typeof this.values['hide_if'] != 'undefined' ? ' pbuilder_hidable_control' : '') + '" name="' + this.name + '" id="pbuilder_' + this.values.type + '_' + this.name + '" value="' + (typeof (this.values.std) != 'undefined' && this.values.std != '' ? this.values.std + '" />' : '" />') + '</div>';
                    html += '<div style="clear:both;"></div>';
                    html += wrapperClose;
                    break;
                case 'shortcode-popup' :
                    html += '<div class="pbuilder_add_shortcode_popup_inner pbuilder_controls_wrapper">';
                    if (typeof this.values.groups != 'undefined') {
                        var cntHtml = '<div class="pbuilder_shortcode_groups">';
                        var selectHtml = '';
                        var selectArray = {};
                        var cnt = 0;
						var allshortcodesHtml = '<div class="pbuilder_shortcode_group pbuilder_shortcode_group_all" data-group="All">';
                        for (var g in this.values.groups) {
                            /* select */
                            selectArray[this.values.groups[g]['id']] = this.values.groups[g]['label'];
                            /* content */
                            cntHtml += '<div class="pbuilder_shortcode_group" data-group="' + this.values.groups[g]['id'] + '">';
                            for (var x in pbuilder_shortcodes) {
                                if (typeof pbuilder_shortcodes[x]['group'] != 'undefined' && this.values.groups[g]['id'] == pbuilder_shortcodes[x]['group']) {
                                    pbuilder_shortcodes[x]['type'] = 'shortcode-popup-clickable';
                                    var newControl = new pbuilderControl(x, pbuilder_shortcodes[x]);
                                    cntHtml += newControl.html();
									allshortcodesHtml += newControl.html();
                                }
                            }
                            cntHtml += '<div style="clear:both;"></div></div>';
                            cnt++;
                        }

						allshortcodesHtml += '<div style="clear:both;"></div></div>';
						cntHtml += allshortcodesHtml;
						selectArray['All'] = 'All';

                        var selectOptions = {
                            'type': 'select',
                            'label': 'Shortcode group',
                            'label_width': 0.35,
                            'control_width': 0.65,
                            'options': selectArray
                        }
                        var selectCtrl = new pbuilderControl('pbuilder_add_shortcode_group', selectOptions);
                        selectHtml += '<div class="pbuilder_add_shortcode_popup_controls">' + selectCtrl.html() + '<div style="clear:both;"></div></div>';
                        tabsHtml += '</div>';
                        cntHtml += '</div>';
                        html += selectHtml + cntHtml;
                    }
                    else {
                    }
                    html += '</div>';
                    break;
                case 'shortcode-popup-clickable' :
                    html += '<div class="pbuilder_shortcode_block" data-shortcode="' + this.name + '"><span class="shortcode_icon">' + (typeof this.values.icon != 'undefined' && this.values.icon != '' && this.values.icon.indexOf("http")!=0 ? this.values.icon : '<i class="fa fa-code" aria-hidden="true"></i>') + '</span><span class="shortcode_name">' + this.values.text + '</span></div>';
                    break;
                case 'shortcode-holder' :
                    html += '<div class="pbuilder_shortcode_holder">';
                    if (typeof this.values.groups != 'undefined') {
                        var tabsHtml = '<div class="pbuilder_shortcode_tabs">';
                        var cntHtml = '<div class="pbuilder_shortcode_groups">';
                        var selectHtml = '<div class="pbuilder_shortcode_group_select">';
                        var cnt = 0;
                        for (var g in this.values.groups) {
                            /* select */
                            if (cnt == 0) {
                                selectHtml += '<div class="pbuilder_shortcode_tab_select">';
                                selectHtml += '<img src="' + this.values.groups[g]['img'] + '" alt="" />';
                                selectHtml += '</div>';
                            }
                            /* tabs */
                            tabsHtml += '<div class="pbuilder_shortcode_tab' + (cnt != 0 ? ' after' : '') + '" data-group="' + this.values.groups[g]['id'] + '" style="left:' + (cnt * 42) + 'px;">';
                            tabsHtml += '<img src="' + this.values.groups[g]['img'] + '" alt="" />';
                            tabsHtml += '</div>';
                            /* content */
                            cntHtml += '<div class="pbuilder_shortcode_group" data-group="' + this.values.groups[g]['id'] + '">';
                            for (var x in pbuilder_shortcodes) {
                                if (typeof pbuilder_shortcodes[x]['group'] != 'undefined' && this.values.groups[g]['id'] == pbuilder_shortcodes[x]['group']) {
                                    var newControl = new pbuilderControl(x, pbuilder_shortcodes[x]);
                                    cntHtml += newControl.html();
                                }
                            }
                            cntHtml += '</div>';
                            cnt++;
                        }
                        selectHtml += '<div class="pbuilder_shortcode_tab_select_name">Change shortcode group</div></div>';
                        tabsHtml += '</div>';
                        cntHtml += '</div>';
                        html += selectHtml + tabsHtml + cntHtml;
                    }
                    else {
                    }
                    html += '</div>';
                    /*
                     var group = (typeof this.values.group != 'undefined' ? this.values.group : 'General');
                     if(typeof this.values.collapsible == 'undefined' || this.values.collapsible == true) html += '<div class="pbuilder_shortcode"><div class="pbuilder_shortcode">'+group+'</div><div class="pbuilder_collapsible_content">';
                     html += '<div class="pbuilder_draggable_holder">';
                     for (var x in pbuilder_shortcodes) {
                     if(typeof this.values.group == 'undefined' || (typeof pbuilder_shortcodes[x]['group'] != 'undefined' && this.values.group == pbuilder_shortcodes[x]['group'])) {
                     var newControl = new pbuilderControl(x,pbuilder_shortcodes[x]);
                     html += newControl.html();
                     }
                     }
                     if(typeof this.values.collapsible == 'undefined' || this.values.collapsible == true) html += '</div></div>';
                     html += '</div>';
                     html = wrapper + html + wrapperClose;
                     */
                    break;
                case 'draggable' :
                    html += '<div class="pbuilder_draggable" data-shortcode="' + this.name + '"><span class="shortcode_icon">' + (typeof this.values.icon != 'undefined' && this.values.icon != '' ? '<img src="' + this.values.icon + '" alt="" />' : '<img src="' + pbuilder_url + 'images/icons/11.png" alt="" />') + '</span><span class="pbuilder_shortcode_name">' + this.values.text + '</span></div>';
                    break;
                case 'button' :
                    var cl = (typeof this.values['class'] != 'undefined' && this.values['class'] != '' ? this.values['class'] : '');
                    var href = (typeof this.values['href'] != 'undefined' && this.values['href'] != '' ? this.values['href'] : '#');
                    var id = (typeof this.values['id'] != 'undefined' && this.values['id'] != '' ? ' id="' + this.values['id'] + '"' : '');
                    var name = (typeof this.values['id'] != 'undefined' && this.values['id'] != '' ? ' name="' + this.values['id'] + '"' : '');
                    var style = (this.values['style'] == 'primary' ? 'pbuilder_gradient_primary' : 'pbuilder_gradient');
                    var align = (this.values['align'] == 'right' ? ' style="float:right;"' : '');
                    var click = (typeof this.values['click'] != 'undefined' && this.values['click'] != '' ? ' onclick="' + this.values['click'] + '"' : '');
                    if ((typeof this.values['no_wrap'] == 'undefined')) {
                        wrapper = '<div class="pbuilder_control' + (typeof this.values['hide_if'] != 'undefined' ? ' pbuilder_hidable' : '') + (typeof this.values['class'] != 'undefined' ? ' ' + this.values['class'] : '') + halfControl + '">' + '<div class="pbuilder_control_content" style="width:' + (controlWidth * 100) + '%">';
                        html += wrapper;
                        html += '<button type="button" ' + id + ' ' + name + ' href="' + href + '" class="' + style + (typeof this.values['hide_if'] != 'undefined' ? ' pbuilder_hidable_control' : '') + ' pbuilder_button ' + cl + '"' + align + '>' + this.values['label'] + '</button>' + (typeof this.values['loader'] != 'undefined' && this.values['loader'] == 'true' ? '<img src="' + pbuilder_url + 'images/save-loader.gif" class="pbuilder_save_loader" alt="" />' : '') + (this.values['clear'] != 'false' ? '<div style="clear:both;"></div>' : '');
                        html += wrapperClose;
                    } else {
                        html += '<a ' + id + ' ' + name + ' href="' + href + '" class="' + style + ' pbuilder_button ' + cl + '"' + align + ' ' + click + ' >' + this.values['label'] + '</a>' + (typeof this.values['loader'] != 'undefined' && this.values['loader'] == 'true' ? '<img src="' + pbuilder_url + 'images/save-loader.gif" class="pbuilder_save_loader" alt="" />' : '') + (this.values['clear'] != 'false' ? '<div style="clear:both;"></div>' : '');
                    }
                    break;
                case 'number' :
                    var min = (typeof this.values['min'] != 'undefined' && this.values['min'] != '' ? parseInt(this.values['min']) : 0);
                    var max = (typeof this.values['max'] != 'undefined' && this.values['max'] != '' ? parseInt(this.values['max']) : 100);
                    if(this.values['std'] == 'false' || this.values['std'] == 'true') this.values['std'] = 0;
                    var std = (typeof this.values['std'] != 'undefined' && this.values['std'] != '' ? this.values['std'] == 'default' ? 0 : parseInt(this.values['std']) : 0);
                    var def = (typeof this.values['default'] != 'undefined' ? this.values['default'] : '');
                    var step = (typeof this.values['step'] != 'undefined' && this.values['step'] != '' ? parseInt(this.values['step']) : 1);
                    var unit = (typeof this.values['unit'] != 'undefined' && this.values['unit'] != '' ? this.values['unit'] : '');

                    var maxStr = '' + max;
                    html += wrapper;
                    html += '<div class="pbuilder_number_bar_wrapper"><div class="pbuilder_number_bar" data-default="' + def + '" data-min="' + min + '" data-max="' + max + '" data-std="' + std + '" data-step="' + step + '" data-unit="' + unit + '"></div></div><input class="pbuilder_number_amount pbuilder_input' + (typeof this.values['hide_if'] != 'undefined' ? ' pbuilder_hidable_control' : '') + '" name="' + this.name + '" id="pbuilder_' + this.values.type + '_' + this.name + '" value="' + (this.values['std'] == 'default' ? 'default' : std + unit) + '"/><div class="pbuilder_number_button pbuilder_gradient"></div><div style="clear:both;"></div>';
                    html += wrapperClose;
                    break;
                case 'color' :
                    html += wrapper;
                    html += '<div class="pbuilder_color_wrapper">';
                    html += '<input class="pbuilder_color pbuilder_input' + (typeof this.values['hide_if'] != 'undefined' ? ' pbuilder_hidable_control' : '') + '" name="' + this.name + '" id="pbuilder_' + this.values.type + '_' + this.name + '" ' + (typeof (this.values.std) != 'undefined' && this.values.std != '' ? 'value="' + this.values.std + '"' : '') + '/>';
                    html += '</div>';
                    html += wrapperClose;
                    break;
                case 'collapsible' :
                    var lab = (typeof (this.values.label) != 'undefined' ? '<label for="' + this.name + '">' + this.values.label + ' </label>' : '');
                    var open = (typeof this.values['open'] != 'undefined' && this.values['open'] == 'true');
                    html += '<div class="pbuilder_collapsible_big pbuilder_collapsible" data-name="' + this.name + '"><div class="pbuilder_collapsible_header">' + lab + '<span class="pbuilder_collapse_trigger pbuilder_gradient' + (open ? ' active' : '') + '">' + (open ? '-' : '+') + '</span></div><div class="pbuilder_collapsible_content"' + (open ? ' style="display:block"' : '') + '>';
                    var controlObj = $.extend(true, {}, this.values['options']);
                    for (var y in controlObj) {
                        var newControl = new pbuilderControl(y, controlObj[y]);
                        html += newControl.html();
                    }
                    html += '<div style="clear:both;"></div></div></div>';
                    break;
                case 'sortable' :
                    var item_name = (typeof this.values['item_name'] != 'undefined' && this.values['item_name'] != '' ? this.values['item_name'] : 'item');
                    html += wrapper;
                    html += '<div class="pbuilder_sortable_holder" data-name="' + this.name + '" data-iname="' + item_name + '" id="pbuilder_' + this.values.type + '_' + this.name + '">';
                    html += '<div class="pbuilder_sortable">';
                    if (typeof this.values['std'] != 'undefined' && this.values['std'] != '') {
                        if (typeof this.values['std']['order'] != 'undefined' && this.values['std']['order'] != '' && this.values['std']['order'] != {}) {
                            for (var x in this.values['std']['order']) {
                                var sortid = this.values['std']['order'][x];
                                html += '<div class="pbuilder_sortable_item pbuilder_collapsible" data-sortid="' + sortid + '" data-sortname="' + this.name + '"><div class="pbuilder_gradient pbuilder_sortable_handle pbuilder_collapsible_header">' + item_name + ' ' + sortid + ' - <span class="pbuilder_sortable_delete">delete</span>, <span class="pbuilder_sortable_clone">clone</span><span class="pbuilder_collapse_trigger">+</span></div><div class="pbuilder_collapsible_content">';
                                var controlObj = $.extend(true, {}, this.values['options']);
                                for (var y in controlObj) {
                                    if (typeof this.values['std']['items'][sortid][y] != 'undefined') {
                                        controlObj[y]['std'] = this.values['std']['items'][sortid][y];
                                    }
                                    var newControl = new pbuilderControl('fsort-' + sortid + '-' + y, controlObj[y]);
                                    html += newControl.html();
                                }
                                html += '<div style="clear:both"></div></div></div>';
                            }
                        }
                    }
                    html += '</div>';
                    html += '<a href="#" class="pbuilder_sortable_add pbuilder_gradient pbuilder_button">+ Add new ' + item_name + '</a>';
                    html += '<div style="clear:both;"></div>';
                    html += '</div>';
                    html += wrapperClose;
                    break;
            }
            return html;
        }
    }
    var counter = 0;
    /*  Ajax shortcode gathering  */
    window.pbuilder_shajax = {}
    function pbuilderGetShortcode(f, holder, options) {
        holder.closest('.pbuilder_module').find('.pbuilder_module_loader').show();
        var data = {
            action: 'pbuilder_shortcode',
            f: f,
			      post_id:post_id
        }
        var modid = holder.closest("[data-modid]").attr("data-modid");
        pbuilder_items['items'][modid]['options']["pbuilder_scid"] = modid;
        options.pbuilder_scid = modid;
        pbuilder_items['items'][modid]['options']["pbuilder_pgid"] = post_id;
        options.pbuilder_pgid = post_id;
        if (typeof options !== 'undefined') {
            data.options = JSON.stringify(options);
        }
        var modid = holder.closest('.pbuilder_module').attr('data-modid');
        if (typeof window.pbuilder_shajax[modid] != 'undefined')
            window.pbuilder_shajax[modid].abort();
        window.pbuilder_shajax[modid] = $.post(ajaxurl, data, function (response) {
          holder.html(response);
          holder.closest('.pbuilder_module').trigger('refresh');
          holder.closest('.pbuilder_module').find('.pbuilder_module_loader').hide();
          checkHTML();
          var cla = document.getElementById("fa_undo");
          cla.style.color = '#ffffff';
          counter++
          localStorage.setItem("counter", counter);
          localStorage.setItem("counter_lim", counter);
            
          if(f == 'pbuilder_facebooklike' || f == 'pbuilder_fbcomments'){
            var iWindow = $('#pbuilder_body_frame')[0].contentWindow;
            iWindow.FB.XFBML.parse();   
          }
        });
    }
    var keyTimeout = {};
    function pbuilderContolChange($jq, $control, timeout) {
        var name = $control.attr('name');
        $menu = $('.pbuilder_shortcode_menu:first');
        if ($menu.length > 0) {
            var modid = parseInt($menu.attr('data-modid'));

			      var shortcode = $menu.attr('data-shortcode');
            var val = $control.val();


            if ($control.hasClass("pbuilder_text")) {//(shortcode == "button" && name == "url") || (shortcode == "image" && name == "link"))
                val = val.replace(/\[+(.*?)\]+/g, "%sqs%$1%sqe%");//escape(val);
                val = val.replace(/\"/g, "&quot;");
            }
            if (name.search('fsort') == -1) {
  
                if ($menu.hasClass('pbuilder_rowedit_menu')) {
                    if (typeof pbuilder_items['rows'][modid]['options'] == 'undefined'){
                      pbuilder_items['rows'][modid]['options'] = {};
                    }
                    pbuilder_items['rows'][modid]['options'][name] = val;
                } else if ($menu.hasClass('pbuilder_columnedit_menu')) {
              
                var columnid = parseInt($menu.attr('data-columnid'));
                    if (typeof pbuilder_items['columns'] == 'undefined'){
                      pbuilder_items['columns']={};
                    }
                    if (typeof pbuilder_items['columns'][modid] == 'undefined'){
                      pbuilder_items['columns'][modid]={};
                    }
                    if (typeof pbuilder_items['columns'][modid][columnid] == 'undefined'){
                     pbuilder_items['columns'][modid][columnid] = {};
                    }
                    if (typeof pbuilder_items['columns'][modid][columnid]['options'] == 'undefined'){
                      pbuilder_items['columns'][modid][columnid]['options'] = {};
                    }
                    pbuilder_items['columns'][modid][columnid]['options'][name] = val;
                }
                else {
        					if (typeof pbuilder_items['items'][modid]['options'] == 'undefined'){
        					  pbuilder_items['items'][modid]['options'] = {};
        					}

                  var oldval = pbuilder_items['items'][modid]['options'][name];
                  pbuilder_items['items'][modid]['options'][name] = val;

                }
            }
            else {
                var subname = name.substr(name.search('-') + 1);
                name = subname.substr(subname.search('-') + 1);
                var sortid = parseInt(subname.substr(0, subname.search('-')));
                var $parent = $control.closest('.pbuilder_sortable_item');
                sortname = $parent.attr('data-sortname');
                if ($menu.hasClass('pbuilder_rowedit_menu')) {
                                if (typeof pbuilder_items['rows'][modid]['options'] == 'undefined'){
                                     pbuilder_items['rows'][modid]['options'] = {};
                                }
                    var oldval = pbuilder_items['rows'][modid]['options'][sortname]['items'][sortid][name];
                pbuilder_items['rows'][modid]['options'][sortname]['items'][sortid][name] = val;
                } else if ($menu.hasClass('pbuilder_columnedit_menu')) {
                var columnid = parseInt($menu.attr('data-columnid'));
                if (typeof pbuilder_items['rows'][modid]['columns'][columnid]['options'] == 'undefined'){

                }

            		if (typeof pbuilder_items['rows'][modid]['columns'][columnid]['options'] == 'undefined'){
                      pbuilder_items['rows'][modid]['columns'][columnid]['options'] = {};
					}
                    pbuilder_items['rows'][modid]['columns'][columnid]['options'][sortname]['items'][sortid][name] = val;
                }
                else {
                   
                       var oldval = pbuilder_items['items'][modid]['options'][sortname]['items'][sortid][name];
				               pbuilder_items['items'][modid]['options'][sortname]['items'][sortid][name] = val;
                }
            }

            if (pbuilder_items['items'][modid] != null) {
                pbuilder_items['items'][modid]['options']["pbuilder_scid"] = modid;
                pbuilder_items['items'][modid]['options']["pbuilder_pgid"] = post_id;
            }

            if(name == "push_through_flow_id"){
              var provider = $('#pbuilder_select_formprovider').val();
              if(provider == 'leadsflowpro'){
                var overwrite_fields = confirm("Changing the selected Flow will overwrite existing overlay fields. Do you want to continue?");
                if (overwrite_fields == true) {
                    var selected_flow_id = $('#pbuilder_select_push_through_flow_id').val();
                    

                    var data = {
                              action: 'pbuilder_lfpfields',
              				        flowid: selected_flow_id,
                    };
                    window.pbuilder_popupajax = $.get(ajaxurl, data, function (response) {
                      var response=JSON.parse(response);

                      pbuilderParseFormCodeCustom($jq, response.fields, modid);

                      pbuilder_items['items'][modid]['options']["formurl"] = response.flowurl;
                      $('#pbuilder_input_formurl').val(response.flowurl);
                      pbuilder_items['items'][modid]['options']["formmethod"] = 'GET';
                      $('#pbuilder_select_formmethod').val('GET');
                      $('#pbuilder_select_formmethod').parent().find('span').html('GET');


              			});
                } else {
                    $('#pbuilder_select_push_through_flow_id').val(oldval);
                    var oldtext = $('#pbuilder_select_push_through_flow_id').parent().find('*[data-value="'+oldval+'"]').html();
                    $('#pbuilder_select_push_through_flow_id').parent().find('span').html(oldtext);
                }
              }
            }
            
            if (name == "formcode") {
                var provider = $('#pbuilder_select_formprovider').val();
                if(provider == 'webinarjam' || provider == 'everwebinar'){
                
                } else {
                  if($(':focus').attr('id') == 'pbuilder_textarea_formcode'){                    
                    pbuilderParseFormCode($jq, $control, modid);
                  } else {
                    pbuilderParseFormCodeFields($jq, $control, modid);
                  }
                }
                if(val.search('app.convertkit.com')>0){
                  pbuilder_items['items'][modid]['options']["formmethod"] = 'GET';
                  $('#pbuilder_select_formmethod').val('GET');
                  $('#pbuilder_select_formmethod').parent().find('span').html('GET');
                  console.log('Set to GET');
                }
                
            }

            if(name == "form_webinar_url") {
              var provider = $('#pbuilder_select_formprovider').val();
              if(provider == 'demio'){
                var demiourl = $('#pbuilder_input_form_webinar_url').val();
                if(demiourl.indexOf('my.demio.com')>0){
                  
                  fields = [];
                  fields['name']=[];
                  fields['name']['is_name']=true;
                  fields['name']['name']='name';
                  fields['name']['type']='text';
                  fields['name']['val']='';

                  fields['email']=[];
                  fields['email']['is_email']=true;
                  fields['email']['name']='email';
                  fields['email']['type']='text';
                  fields['email']['val']='';

  
                  pbuilderParseFormCodeCustom($jq, fields, modid);

                  pbuilder_items['items'][modid]['options']["formurl"] = demiourl;
                  $('#pbuilder_input_formurl').val(demiourl);
                  $('#pbuilder_input_form_webinar_url').removeClass('pbuilder_input_error');
                } else {
                  $('#pbuilder_input_form_webinar_url').addClass('pbuilder_input_error');
                }
              } else if(provider == 'gotowebinar'){
                var gotowebinarurl = $('#pbuilder_input_form_webinar_url').val();
                if(gotowebinarurl.indexOf('https://attendee.gotowebinar.com/register/')==0){
                  var webinar_id=gotowebinarurl.replace('https://attendee.gotowebinar.com/register/','');

                  fields = [];
                  fields['name']=[];
                  fields['name']['is_name']=true;
                  fields['name']['name']='name';
                  fields['name']['type']='text';
                  fields['name']['val']='';

                  fields['email']=[];
                  fields['email']['is_email']=true;
                  fields['email']['name']='email';
                  fields['email']['type']='text';
                  fields['email']['val']='';


                  fields['registrant.givenName']=[];
                  fields['registrant.givenName']['name']='registrant.givenName';
                  fields['registrant.givenName']['type']='hidden';
                  fields['registrant.givenName']['val']='';


                  fields['registrant.surname']=[];
                  fields['registrant.surname']['name']='registrant.surname';
                  fields['registrant.surname']['type']='hidden';
                  fields['registrant.surname']['val']='';

                  fields['registrant.email']=[];
                  fields['registrant.email']['name']='registrant.email';
                  fields['registrant.email']['type']='hidden';
                  fields['registrant.email']['val']='';

                  fields['webinar']=[];
                  fields['webinar']['name']='webinar';
                  fields['webinar']['type']='hidden';
                  fields['webinar']['val']=webinar_id;

                  fields['registrant.source']=[];
                  fields['registrant.source']['name']='registrant.source';
                  fields['registrant.source']['type']='hidden';
                  fields['registrant.source']['val']='';

                  fields['registrant.timeZone']=[];
                  fields['registrant.timeZone']['name']='registrant.timeZone';
                  fields['registrant.timeZone']['type']='hidden';
                  fields['registrant.timeZone']['val']='America/Chicago';
  
                  pbuilderParseFormCodeCustom($jq, fields, modid);

                  pbuilder_items['items'][modid]['options']["formurl"] = 'https://attendee.gotowebinar.com/registration.tmpl';
                  $('#pbuilder_input_formurl').val('https://attendee.gotowebinar.com/registration.tmpl');
                  $('#pbuilder_input_form_webinar_url').removeClass('pbuilder_input_error');
                } else {
                  $('#pbuilder_input_form_webinar_url').addClass('pbuilder_input_error');
                }
              } else if(provider == 'webinarjeo'){
                var webinarjeourl = $('#pbuilder_input_form_webinar_url').val();
                if(webinarjeourl.indexOf('https://app.webinarjeo.com/node/webinar/view/')==0 || webinarjeourl.indexOf('http://app.webinarjeo.com/node/webinar/view/')==0){
                  var webinar_slug=webinarjeourl.replace('https://app.webinarjeo.com/node/webinar/view/','').replace('https://app.webinarjeo.com/node/webinar/view/','');
                  var webinar_id=webinar_slug.substr(0,webinar_slug.indexOf('-'));

                  fields = [];
                  fields['name']=[];
                  fields['name']['is_name']=true;
                  fields['name']['name']='name';
                  fields['name']['type']='text';
                  fields['name']['val']='';

                  fields['email']=[];
                  fields['email']['is_email']=true;
                  fields['email']['name']='email';
                  fields['email']['type']='text';
                  fields['email']['val']='';


                  fields['ParticipantRegisterForm%sqs%name%sqe%']=[];
                  fields['ParticipantRegisterForm%sqs%name%sqe%']['name']='ParticipantRegisterForm%sqs%name%sqe%';
                  fields['ParticipantRegisterForm%sqs%name%sqe%']['type']='hidden';
                  fields['ParticipantRegisterForm%sqs%name%sqe%']['val']='';


                  fields['ParticipantRegisterForm%sqs%email%sqe%']=[];
                  fields['ParticipantRegisterForm%sqs%email%sqe%']['name']='ParticipantRegisterForm%sqs%email%sqe%';
                  fields['ParticipantRegisterForm%sqs%email%sqe%']['type']='hidden';
                  fields['ParticipantRegisterForm%sqs%email%sqe%']['val']='';

                  fields['ParticipantRegisterForm%sqs%webinarId%sqe%']=[];
                  fields['ParticipantRegisterForm%sqs%webinarId%sqe%']['name']='ParticipantRegisterForm%sqs%webinarId%sqe%';
                  fields['ParticipantRegisterForm%sqs%webinarId%sqe%']['type']='hidden';
                  fields['ParticipantRegisterForm%sqs%webinarId%sqe%']['val']=webinar_id;


                  pbuilderParseFormCodeCustom($jq, fields, modid);

                  pbuilder_items['items'][modid]['options']["formurl"] = 'https://app.webinarjeo.com/node/webinar/register/'+webinar_slug;
                  $('#pbuilder_input_formurl').val('https://app.webinarjeo.com/node/webinar/register/'+webinar_slug);
                  $('#pbuilder_input_form_webinar_url').removeClass('pbuilder_input_error');
                } else {
                  $('#pbuilder_input_form_webinar_url').addClass('pbuilder_input_error');
                }
              }
            }
            if (typeof timeout !== 'undefined') {
                window.clearTimeout(keyTimeout[modid]);
                keyTimeout[modid] = window.setTimeout(function () {
                    $menu.trigger('fchange');
                }, timeout);
            }
            else {
                $menu.trigger('fchange');
            }
        }

		    pbuilderHideControls($control);
        pbuilderRefreshControls($jq, $control)
    }


    function pbuilderParseFormCodeCustom($jq, fields, modid) {
        var div = $('<div />');
        var input_elems = {};
        customvalues = {};
        hiddenvalues = {};
        customvalues[modid] = {};
        hiddenvalues[modid] = {};

        // RESET FORM FIELDS
        $(".mCSB_container", $("#pbuilder_select_namefield").parent()).html("");
        $(".mCSB_container", $("#pbuilder_select_emailfield").parent()).html("");
        $("#pbuilder_select_namefield").val("");
        $("#pbuilder_select_emailfield").val("");
        pbuilder_items['items'][modid]['options']["namefield"] = "";
        pbuilder_items['items'][modid]['options']["emailfield"] = "";
        $("span", $("#pbuilder_select_namefield").parent()).eq(0).html("");
        $("span", $("#pbuilder_select_emailfield").parent()).eq(0).html("");
        var removecount = 0;
        $(".customfieldremove").each(function () {
            var id = $(this).attr("id").replace("customfieldremove", "");
            if (pbuilder_items['items'][modid]['options']["customfieldtype" + id] == "formfield") {
                removecount++;
                $(this).attr("form-code", "true");
                $(this).trigger("click");
            }
        });
        $(".hiddenfieldremove").each(function () {
            $(this).trigger("click");
        });

        // ADD NEW FIELDS


          var hiddenfieldscount = 0;
          var customfieldscount = 0;

          for(f in fields) {
                var key = fields[f]['name'];
                var type = fields[f]['type'];
				        var val = fields[f]['val'];
                var label = fields[f]['label'];
                if (type != 'hidden') {
                    var li = '<li><a data-value="' + key + '" title="' + key + '"';
                    if (fields[f]['is_name']) {
                        li += ' class="selected" '
                        $("#pbuilder_select_namefield").val(key);
                        pbuilder_items['items'][modid]['options']["namefield"] = key;
                        $('#pbuilder_select_namefield').siblings('div.pbuilder_select').children('span').html(key);
                    }
                    li += ' href="#">' + key.replace(/\%sqs\%+(.*?)\%sqe\%+/g, "[$1]") + '</a></li>';
                    $(".mCSB_container", $("#pbuilder_select_namefield").parent()).append(li);
                    var li = '<li><a data-value="' + key + '" title="' + key + '"';
                    if (fields[f]['is_email']) {
                        li += ' class="selected" '
                        $("#pbuilder_select_emailfield").val(key);
                        pbuilder_items['items'][modid]['options']["emailfield"] = key;
                        $('#pbuilder_select_emailfield').siblings('div.pbuilder_select').children('span').html(key);
                    }
                    li += ' href="#">' + key.replace(/\%sqs\%+(.*?)\%sqe\%+/g, "[$1]") + '</a></li>';
                };
                $(".mCSB_container", $("#pbuilder_select_emailfield").parent()).append(li);


      				 var field_style = typeof( style ) != "undefined" ? 1 : 0;
      	   		 if( typeof( style ) != "undefined"){
      					 if($(this).css('display') == "none"){
                   var field_display_value = 1;
                 }
      				 } else {
      				 	 var field_display_value = 0;
      				 }

               if (!fields[f]['is_name'] && !fields[f]['is_email']) {
				            if (type == "hidden" || (field_style == 1 && field_display_value == 1 ) ) {
					            pbuilderAddHiddenField(-1, key, val, "formfield");
                      hiddenfieldscount++;
                    }

                    if (type == "text" && field_display_value == 0 ){
    					        pbuilderAddCustomField(-1, label, key, "formfield");
                      customfieldscount++;
        						}
                }
            }
            if (customfieldscount > 0 && !$("#pbuilder_checkbox_customfields").prev(".pbuilder_checkbox").hasClass("active")) {
                $("#pbuilder_checkbox_customfields").prev(".pbuilder_checkbox").trigger("click");
            }

            if (hiddenfieldscount > 0 && !$("#pbuilder_checkbox_hiddenfields").prev(".pbuilder_checkbox").hasClass("active")) {
                $("#pbuilder_checkbox_hiddenfields").prev(".pbuilder_checkbox").trigger("click");
            }
    }


    function pbuilderParseFormCode($jq, $control, modid) {
        var div = $('<div />');
        var input_elems = {};
        customvalues = {};
        hiddenvalues = {};
        customvalues[modid] = {};
        hiddenvalues[modid] = {};
        if (true) {
            //$('#pbuilder_select_formmethod').val('');
            //$('#pbuilder_input_formurl').val('');
            jQuery(".mCSB_container", jQuery("#pbuilder_select_namefield").parent()).html("");
            jQuery(".mCSB_container", jQuery("#pbuilder_select_emailfield").parent()).html("");
            $("#pbuilder_select_namefield").val("");
            $("#pbuilder_select_emailfield").val("");
            pbuilder_items['items'][modid]['options']["namefield"] = "";
            pbuilder_items['items'][modid]['options']["emailfield"] = "";
            $("span", $("#pbuilder_select_namefield").parent()).eq(0).html("");
            $("span", $("#pbuilder_select_emailfield").parent()).eq(0).html("");
            var removecount = 0;
            $(".customfieldremove").each(function () {
                var id = $(this).attr("id").replace("customfieldremove", "");
                if (pbuilder_items['items'][modid]['options']["customfieldtype" + id] == "formfield") {
                    removecount++;
                    $(this).attr("form-code", "true");
                    $(this).trigger("click");
                }
            });
            $(".hiddenfieldremove").each(function () {
                var id = $(this).attr("id").replace("hiddenfieldremove", "");
                if (pbuilder_items['items'][modid]['options']["hiddenfieldtype" + id] == "formfield") {
                    $(this).attr("form-code", "true");
                    $(this).trigger("click");
                }
            });
        }
        try {
            //div.html($control.val().replace(/<!--.*-->/g, "").replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi,'').replace(/<script.*/gi, ''));
            var html = $control.val().replace(/<!--.*-->/g, "").replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '').replace(/<script.*/gi, '');
            html = html.replace(new RegExp('<script', 'g'), "< script");
            html = html.replace(/\[+(.*?)\]+/g, "%sqs%$1%sqe%");
            //html = html.replace(/\"/g, "&quot;");
            //html = html.replace(new RegExp('http://', 'g'), "[http]://");
            //html = html.replace(new RegExp('https://', 'g'), "[https]://");
            div.html(html);
            $control.val(html.replace(/\%sqs\%+(.*?)\%sqe\%+/g, "[$1]")).trigger("change");
            pbuilder_items['items'][modid]['options']["formcode"] = html;
        } catch (error) {
        }
        var form = div.find('form[action]');
        var hiddenfieldscount = 0;
        var customfieldscount = 0;
        if (form.length > 0) {
            $('input[name]:not(:button,:submit)', form).each(function () {
                var key = $(this).attr('name');
                var type = $(this).attr('type');
                var style = $(this).attr('style');                
				        var val = $(this).val();
                if (type != 'hidden') { //do not show hidden fields in dropdown
                    var li = '<li><a data-value="' + key + '" title="' + key + '"';
                    if (key == 'name') {
                        li += ' class="selected" '
                        $("#pbuilder_select_namefield").val(key);
                        //$("span", $("#pbuilder_select_namefield").parent()).eq(0).html(key.replace(/\%sqs\%+(.*?)\%sqe\%+/g, "[$1]"));
                        pbuilder_items['items'][modid]['options']["namefield"] = key;
                        $('#pbuilder_select_namefield').siblings('div.pbuilder_select').children('span').html(key);
                        
                    }
                    li += ' href="#">' + key.replace(/\%sqs\%+(.*?)\%sqe\%+/g, "[$1]") + '</a></li>';
                    $(".mCSB_container", $("#pbuilder_select_namefield").parent()).append(li);
                    var li = '<li><a data-value="' + key + '" title="' + key + '"';
                    if (key  == 'email') {
                        li += ' class="selected" '
                        $("#pbuilder_select_emailfield").val(key);
                        pbuilder_items['items'][modid]['options']["emailfield"] = key;
                        $('#pbuilder_select_emailfield').siblings('div.pbuilder_select').children('span').html(key);
                    }
                    li += ' href="#">' + key.replace(/\%sqs\%+(.*?)\%sqe\%+/g, "[$1]") + '</a></li>';
                }; // if type != hidden ends here --- (do not show hidden fields in dropdown ends)

                $(".mCSB_container", $("#pbuilder_select_emailfield").parent()).append(li);
                
				 var field_style = typeof( style ) != "undefined" ? 1 : 0;
	//			 var field_display_value = typeof( style ) != "undefined" && $(this).attr('style').indexOf("none") >= 0 ? 1 : 0;
				 //if( typeof( style ) != "undefined" && ( $(this).attr('style').indexOf("display:none") >= 0 || $(this).attr('style').indexOf("display: none") >= 0  || $(this).attr('style').indexOf("display : none") >= 0  || $(this).attr('style').indexOf("display :none") >= 0 ) )
				 if( typeof( style ) != "undefined")
				 {
					if($(this).css('display') == "none")
				 	var field_display_value = 1;
				 }
				 else
				 {
				 	var field_display_value = 0;
				 }
			//	 var er =1;
				// $('input').each(function(){
				//	alert($(this).css('display'));
				//	if ( $(this).css('display') == 'none')
					{
						//alert(er);
					   //do something
					}
				//	er++;
			//	});
               // alert( type +', ('+ style +'), '+ field_style +', '+ field_display_value );
				if (key.toLowerCase() != 'name' && key.toLowerCase() != 'email') {

				    if (type == "hidden" || (field_style == 1 && field_display_value == 1 ) ) {
					    pbuilderAddHiddenField(-1, key, val, "formfield");
                        hiddenfieldscount++;
                    } //else {
                        if (type == "text")
						{
					//	alert(3+' => '+field_display_value);
                        if( field_display_value == 0 )
						{
					        pbuilderAddCustomField(-1, "", key, "formfield");
                        	customfieldscount++;
						}
						}
                   // }
                }
            }); // foreach
            if (typeof form.attr('target') != "undefined") {
                if ((form.attr('target').toLowerCase() == "_blank" && !$("#pbuilder_checkbox_newwindow").prev(".pbuilder_checkbox").hasClass("active")) ||
                        (form.attr('target').toLowerCase() != "_blank" && $("#pbuilder_checkbox_newwindow").prev(".pbuilder_checkbox").hasClass("active"))) {
                    $("#pbuilder_checkbox_newwindow").prev(".pbuilder_checkbox").trigger("click");
                }
            } else {
                if ($("#pbuilder_checkbox_newwindow").prev(".pbuilder_checkbox").hasClass("active")) {
                    $("#pbuilder_checkbox_newwindow").prev(".pbuilder_checkbox").trigger("click");
                }
            }
            var formmethod = (form.attr('method') || 'post').toUpperCase();
            $('#pbuilder_select_formmethod').val(formmethod);//.trigger("keyup");
            $('#pbuilder_input_formurl').val(form.attr('action'));//.trigger("keyup");
            pbuilder_items['items'][modid]['options']["formmethod"] = formmethod;
            pbuilder_items['items'][modid]['options']["formurl"] = form.attr('action');
            //pbuilder_items['items'][modid]['options']["formurl2"] = form.attr('action');
            $('[data-name=formmethod] span').html(formmethod);
            $('[data-name=formmethod] ul li a.selected').removeClass("selected");
            $('[data-name=formmethod] ul li a[data-value=' + formmethod + ']').addClass("selected");
            if (customfieldscount > 0 && !$("#pbuilder_checkbox_customfields").prev(".pbuilder_checkbox").hasClass("active")) {
                $("#pbuilder_checkbox_customfields").prev(".pbuilder_checkbox").trigger("click");
            }/*else if(customfieldscount <= 0 || $("#pbuilder_checkbox_customfields").prev(".pbuilder_checkbox").hasClass("active")){
             $("#pbuilder_checkbox_customfields").prev(".pbuilder_checkbox").trigger("click");
             }*/
            if (hiddenfieldscount > 0 && !$("#pbuilder_checkbox_hiddenfields").prev(".pbuilder_checkbox").hasClass("active")) {
                $("#pbuilder_checkbox_hiddenfields").prev(".pbuilder_checkbox").trigger("click");
            }/*else if(hiddenfieldscount <= 0 || $("#pbuilder_checkbox_hiddenfields").prev(".pbuilder_checkbox").hasClass("active")){
             $("#pbuilder_checkbox_hiddenfields").prev(".pbuilder_checkbox").trigger("click");
             }*/
            //$("#pbuilder_checkbox_hiddenfields").trigger("keyup");
        }
    }
    
    
    function pbuilderParseFormCodeFields($jq, $control, modid) {
        var div = $('<div />');
        var input_elems = {};
        customvalues = {};
        hiddenvalues = {};
        customvalues[modid] = {};
        hiddenvalues[modid] = {};
        
        try {
            var html_fields = $control.val().replace(/<!--.*-->/g, "").replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '').replace(/<script.*/gi, '');
            html_fields = html_fields.replace(new RegExp('<script', 'g'), "< script");
            html_fields = html_fields.replace(/\[+(.*?)\]+/g, "%sqs%$1%sqe%");
            div.html(html_fields);
        } catch (error) {
        }
               
        var form = div.find('form[action]');
        var hiddenfieldscount = 0;
        var customfieldscount = 0;
        
        $(".mCSB_container", $("#pbuilder_select_emailfield").parent()).html('');        
        $(".mCSB_container", $("#pbuilder_select_namefield").parent()).html('');
        
        if (form.length > 0) {
            $('input[name]:not(:button,:submit)', form).each(function () {
                var key = $(this).attr('name');
                var type = $(this).attr('type');
                var style = $(this).attr('style');                
				        var val = $(this).val();
                if (type != 'hidden') { //do not show hidden fields in dropdown
                    var li = '<li><a data-value="' + key + '" title="' + key + '"';                    
                    if($('#pbuilder_select_namefield').val() == key){
                       li += ' class="selected" ';
                       $("span", $("#pbuilder_select_namefield").parent()).html(key.replace(/\%sqs\%+(.*?)\%sqe\%+/g, "[$1]"));
                    }
                    li += ' href="#">' + key.replace(/\%sqs\%+(.*?)\%sqe\%+/g, "[$1]") + '</a></li>';
                    
                    $(".mCSB_container", $("#pbuilder_select_namefield").parent()).append(li);
                    
                    var li = '<li><a data-value="' + key + '" title="' + key + '"';
                    if($('#pbuilder_select_emailfield').val() == key){
                       li += ' class="selected" ';
                       $("span", $("#pbuilder_select_emailfield").parent()).html(key.replace(/\%sqs\%+(.*?)\%sqe\%+/g, "[$1]"));
                    }
                    li += ' href="#">' + key.replace(/\%sqs\%+(.*?)\%sqe\%+/g, "[$1]") + '</a></li>';
                }; // if type != hidden ends here --- (do not show hidden fields in dropdown ends)

                $(".mCSB_container", $("#pbuilder_select_emailfield").parent()).append(li);               
				 				 
            }); // foreach           
        }
    }
    
    
    
    function pbuilderColumnChange($column, options) {
        window.pbuilder_changes_made=true;
        
        var rowback = '';
        var rowbackimage = '';
        var rowbackvideo = '';
        var rowbackrep = '';
        var rowbackpos = '';
        var rowbackcolor = '';
        var shadow_h_shadow = '';
        var shadow_v_shadow = '';
        var shadow_blur = '';
        var shadow_color = '';
        var back_type = 'static';
        var back_color = '';
        var back_color2 = '';
        var gradient_type = 'linear';
        var timed_row = 'false';
        var timed_row_min = 0;
        var timed_row_sec = 0;
    		var margin_padding = '0|0|0|0|10|20|10|20';
    		var back_image_zoom = 'false';
    		var back_opacity = 'false';
        var column_border_css = '';
        var column_border_thickness = 0;
    		
        for (var x in options) {
            switch (x) {
                case 'padding_top':
                    $row.css('padding-top', parseInt(options[x]) + 'px');
                    break;
                case 'padding_bot':
                    $row.css('padding-bottom', parseInt(options[x]) + 'px');
                    break;
                case 'full_width' :
                    if (options[x] == 'true') {
                        $row.addClass('pbuilder_row_full_width');
                        $row.trigger('row_width_change');
                    } else {
                        $row.removeClass('pbuilder_row_full_width');
                        $row.trigger('row_width_change');
                    }
                    break;
                case 'timed_row' :
                    timed_row = options[x];
                    break;
                case 'timed_row_min' :
                    timed_row_min = parseInt(options[x]);
                    break;
                case 'timed_row_sec' :
                    timed_row_sec = parseInt(options[x]);
                    break;
                case 'row_style' :
                    $row.removeClass('pbuilder_row_stick_top').removeClass('pbuilder_row_stick_bottom');
                    if (options[x] == 'normal') {
                    } else if (options[x] == 'sticktop') {
                        $row.addClass('pbuilder_row_stick_top');
                        jQuery('#pbuilder_body_frame').contents().find('.stick-top-div').remove();
                        jQuery('#pbuilder_body_frame').contents().find('#pbuilder_content').prepend("<div class='stick-top-div'></div>");
                        var StickToTopDiv = jQuery('#pbuilder_body_frame').contents().find('.pbuilder_row_stick_top');
                        var StickToTopDivAn = jQuery('#pbuilder_body_frame').contents().find('.stick-top-div');
                        var heightToAdd = StickToTopDiv.height();
                        if (heightToAdd > 0) {
                            StickToTopDivAn.css("height", heightToAdd+"px");
                        }
                    } else if (options[x] == 'stickbottom') {
                        $row.addClass('pbuilder_row_stick_bottom');                        
                    }
                    $row.trigger('row_style_change');
                    break;
                case 'border_color':
                    column_border_css+='border-color:'+options[x]+';';
                    break;
                case 'border_width':
                    column_border_css+='border-width:'+parseInt(options[x]) + 'px;';
                    break;
                case 'border_style':
                    column_border_css+='border-style:'+options[x]+ ';';
                    break;
                case 'border_round':
                    column_border_css+='border-radius:'+parseInt(options[x]) + 'px;';
                    break;
                case 'shadow_h_shadow':
                    if (options[x] != '')
                        shadow_h_shadow = parseInt(options[x]) + 'px ';
                    break;
                case 'shadow_v_shadow':
                    if (options[x] != '')
                        shadow_v_shadow = parseInt(options[x]) + 'px ';
                    break;
                case 'shadow_blur':
                    if (options[x] != '')
                        shadow_blur = parseInt(options[x]) + 'px ';
                    break;
                case 'shadow_color':
                    if (options[x] != '')
                        shadow_color = options[x] + ' ';
                    //$row.css('box-shadow','1px 1px 1px '+options[x]);
                    break;
                case 'back_type' :
                    back_type = options[x];
                    if (options[x] == 'parallax' || options[x] == 'video_fixed') {
                        rowbackpos = 'fixed';
                    }
                    if (options[x] == 'parallax_animated' || options[x] == 'video_parallax') {
                        rowbackpos = 'parallax';
                    }
                    if (options[x] == 'video' || options[x] == 'video_fixed' || options[x] == 'video_parallax') {
                        if (typeof options['back_video_source'] != 'undefined') {
                            rowbackvideo = options['back_video_source'];
                        }
                        else {
                            rowbackvideo = 'youtube';
                        }
                    }
                    if (options[x] == 'parallax')
                        $row.addClass('pbuilder_row_parallax');
                    else
                        $row.removeClass('pbuilder_row_parallax');
                    break;
                case 'back_color' :
                    if (options[x] != ''){
                        back_color = options[x];
					          }
                    break;
                case 'back_color2' :
                    if (options[x] != '')
                        back_color2 = options[x];
                    break;
                case 'back_opacity' :
                    if (options[x] != '')
                        back_opacity = options[x].replace('%','');
                    break;
                case 'gradient_type' :
                    gradient_type = options[x] != '' ? options[x] : 'linear';
                    break;
                case 'back_image' :
                    if (options[x] != '')
                        rowbackimage = 'background-image:url(' + options[x] + ');';
                    break;
                case 'back_image_zoom' :
                    if (options[x] != 'false'){
                      back_image_zoom = ' pbuilder-background-image-zoom ';
                    } else {
                      back_image_zoom ='';
                    }
                    break;
                case 'back_repeat' :
                    if (options[x] == 'repeat') {
                        rowbackrep = 'background-repeat:repeat;';
                    } else if (options[x] == 'repeatx') {
                        rowbackrep = 'background-repeat:repeat-x;background-position:center top;';
                    } else if (options[x] == 'stretched') {
                        rowbackrep = '  -webkit-background-size: cover;  -moz-background-size: cover;  -o-background-size: cover;  background-size: cover;background-position:center top;';
                    }
                    break;
                case 'margin_padding' :
					          margin_padding = options[x];
                    break;
            }
        }
      
      if(typeof options['border'] === 'undefined'){
        options['border'] = 'false|0|solid|#000000|0|solid|#000000|0|solid|#000000|0|solid|#000000|0|solid|#000000';
      } else {
        var border_properties=options['border'].split('|');
        column_border_css = '';
        var horizontal_border = 0;
        var vertical_border = 0;
        
        if(border_properties[0]!='true'){
          if(parseInt(border_properties[1])>0){
            column_border_css+='border:'+border_properties[1]+' '+border_properties[2]+' '+border_properties[3]+';';
            horizontal_border=vertical_border=parseInt(border_properties[1]);
          }
        } else {
          if(parseInt(border_properties[4])>0){
            column_border_css+='border-top:'+border_properties[4]+' '+border_properties[5]+' '+border_properties[6]+';';
            vertical_border+=parseInt(border_properties[4]);
          }
          if(parseInt(border_properties[7])>0){
            column_border_css+='border-right:'+border_properties[7]+' '+border_properties[8]+' '+border_properties[9]+';';
          }
          if(parseInt(border_properties[10])>0){
            column_border_css+='border-bottom:'+border_properties[10]+' '+border_properties[11]+' '+border_properties[12]+';';
          }
          if(parseInt(border_properties[13])>0){            
            column_border_css+='border-left:'+border_properties[13]+' '+border_properties[14]+' '+border_properties[15]+';';
            horizontal_border+=parseInt(border_properties[13]);
          }
        }
        
        if(column_border_css.length == 0 ){
          column_border_css+='border:none;';
        } else if(parseInt(options['border_round'])>0){
            column_border_css+='border-radius:'+parseInt(options['border_round']) + 'px;';
            
        }
      }
      
      column_border_css+='margin-left:-'+parseInt(horizontal_border)+'px;margin-top:-'+parseInt(vertical_border)+'px;';
      
      
      var margin_padding_arr = margin_padding.split('|');
      $column.find('.pbuilder_column_inner').css('margin-top', margin_padding_arr[0]+'px').css('margin-right', margin_padding_arr[1]+'px').css('margin-bottom', margin_padding_arr[2]+'px').css('margin-left', margin_padding_arr[3]+'px').css('padding-top', margin_padding_arr[4]+'px').css('padding-right', margin_padding_arr[5]+'px').css('padding-bottom', margin_padding_arr[6]+'px').css('padding-left', margin_padding_arr[7]+'px');
      if(back_image_zoom == 'false') {back_image_zoom='';}
      
      if (back_type == 'static' && back_color2 != "") {
          rowbackcolor += ' background: -webkit-' + gradient_type + '-gradient(' + back_color + ', ' + back_color2 + '); ';
          rowbackcolor += ' background: -o-' + gradient_type + '-gradient(' + back_color + ', ' + back_color2 + '); ';
          rowbackcolor += ' background: -moz-' + gradient_type + '-gradient(' + back_color + ', ' + back_color2 + '); ';
          rowbackcolor += ' background: ' + gradient_type + '-gradient(' + back_color + ', ' + back_color2 + '); ';
      } else {
          rowbackcolor += ' background-color: ' + back_color + '; ';
      }

      var out;
      if (rowbackvideo != '') {
          var loop = (typeof options['back_video_loop'] == 'undefined' || options['back_video_loop'] != 'false');
          if (rowbackvideo == 'youtube') {
              id = 'yt' + Math.floor((Math.random() * 100000) + 1);
              if (typeof options['back_video_youtube_id'] != 'undefined' && options['back_video_youtube_id'] != '') {
                  out = '<div style="' + column_border_css + '"  class="pbuilder_row_video pbuilder_column_background' + (rowbackpos == 'fixed' ? ' pbuilder_row_background_fixed' : '') + (rowbackpos == 'parallax' ? ' pbuilder_row_background_parallax' : '') + '">' +
                          '<div id="' + id + '" class="YTPlayer" style="display:block; margin: auto; background: rgba(0,0,0,0.5)" data-property="{videoURL:\'http://youtu.be/' + options['back_video_youtube_id'] + '\',containment:\'self\',startAt:1,mute:true,autoPlay:true' + (loop ? ',loop:true' : ',loop:false') + ',opacity:1,showControls:true,quality:\'hd720\'}"></div>' +
                          '</div>';
              }
              else {
                  out = '';
              }
          }
          else if (rowbackvideo == 'vimeo') {
              out = '<div style="' + column_border_css + '"  class="pbuilder_row_video pbuilder_row_video_vimeo pbuilder_column_background' + (rowbackpos == 'fixed' ? ' pbuilder_row_background_fixed' : '') + (rowbackpos == 'parallax' ? ' pbuilder_row_background_parallax' : '') + '">' +
                      '<iframe src="//player.vimeo.com/video/' + options['back_video_vimeo_id'] + '?title=0&amp;byline=0&amp;portrait=0&amp;color=ffffff&amp;autoplay=1' + (loop ? '&amp;loop=1' : '') + '" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>' +
                      '</div>';
          }
          else {
              out = '<div style="' + column_border_css + '" class="pbuilder_row_video pbuilder_row_video_html5 pbuilder_column_background' + (rowbackpos == 'fixed' ? ' pbuilder_row_background_fixed' : '') + (rowbackpos == 'parallax' ? ' pbuilder_row_background_parallax' : '') + '">' +
                      '<video muted autoplay' + (loop ? ' loop' : '') +
                      (typeof options['back_video_html5_img'] != 'undefined' && options['back_video_html5_img'] != '' ? ' poster="' + options['back_video_html5_img'] + '"' : '') + ' >' +
                      (typeof options['back_video_html5_mp4'] != 'undefined' && options['back_video_html5_mp4'] != '' ? '<source src="' + options['back_video_html5_mp4'] + '" type="video/mp4">' : '') +
                      (typeof options['back_video_html5_webm'] != 'undefined' && options['back_video_html5_webm'] != '' ? '<source src="' + options['back_video_html5_webm'] + '" type="video/webm">' : '') +
                      (typeof options['back_video_html5_ogv'] != 'undefined' && options['back_video_html5_ogv'] != '' ? '<source src="' + options['back_video_html5_ogv'] + '" type="video/ogg">' : '') +
                      '</video></div>';
          }
      }
      else if (rowbackimage != '') {
          out = '<div style="'+column_border_css+'" class="pbuilder_column_background' + back_image_zoom + (rowbackpos == 'fixed' ? ' pbuilder_row_background_fixed' : '') + (rowbackpos == 'parallax' ? ' pbuilder_row_background_parallax' : '') + '" ><div class="pbuilder_row_back_image" style="' + rowbackimage + rowbackcolor + rowbackrep + '"></div></div>';
      }
      else if (rowbackcolor) {
          out = '<div class="pbuilder_column_background" style="' + column_border_css + rowbackcolor + '"></div>';
      }
      else {
          out = '';
      }

      
      $column.children('.pbuilder_column_background').remove();
      $column.prepend(out);
      $column.trigger('refresh');


      if (timed_row == "true") {
          timed_row_min = parseInt(timed_row_min) * 60 * 1000;
          timed_row_sec = parseInt(timed_row_sec) * 1000;
          var duration = timed_row_min + timed_row_sec;
          
      }


    }



	function pbuilderRowChange($row, options) {
        window.pbuilder_changes_made=true;
        
        var rowback = '';
        var rowbackimage = '';
        var rowbackvideo = '';
        var rowbackrep = '';
        var rowbacksize = 'default';
        var rowbackpos = '';
        var rowbackcolor = '';
        var shadow_h_shadow = '';
        var shadow_v_shadow = '';
        var shadow_blur = '';
        var shadow_color = '';
        var back_type = 'static';
        var back_color = '';
        var back_color2 = '';
        var gradient_type = 'linear';
        var timed_row = 'false';
        var timed_row_min = 0;
        var timed_row_sec = 0;
    		var margin_padding = '0|0|0|0|10|20|10|20';
    		var back_image_zoom = 'false';
        var back_full_width = 'true';
        
        for (var x in options) {
            switch (x) {

                case 'full_width' :
                    if (options[x] == 'true') {
                        $row.addClass('pbuilder_row_full_width');
                        $row.trigger('row_width_change');
                    } else {
                        $row.removeClass('pbuilder_row_full_width');
                        $row.trigger('row_width_change');
                    }
                    break;
                case 'timed_row' :
                    timed_row = options[x];
                    break;
                case 'timed_row_min' :
                    timed_row_min = parseInt(options[x]);
                    break;
                case 'timed_row_sec' :
                    timed_row_sec = parseInt(options[x]);
                    break;
                case 'row_style' :
                    $row.removeClass('pbuilder_row_stick_top').removeClass('pbuilder_row_stick_bottom');
                    if (options[x] == 'normal') {
                    } else if (options[x] == 'sticktop') {
                        $row.addClass('pbuilder_row_stick_top');
                        jQuery('#pbuilder_body_frame').contents().find('.stick-top-div').remove();
                        jQuery('#pbuilder_body_frame').contents().find('#pbuilder_content').prepend("<div class='stick-top-div'></div>");
                        var StickToTopDiv = jQuery('#pbuilder_body_frame').contents().find('.pbuilder_row_stick_top');
                        var StickToTopDivAn = jQuery('#pbuilder_body_frame').contents().find('.stick-top-div');
                        var heightToAdd = StickToTopDiv.height();
                        if (heightToAdd > 0) {
                            StickToTopDivAn.css("height", heightToAdd+"px");
                        }
                    } else if (options[x] == 'stickbottom') {
                        $row.addClass('pbuilder_row_stick_bottom');                        
                    }
                    $row.trigger('row_style_change');
                    break;
                
                case 'shadow_h_shadow':
                    if (options[x] != '')
                        shadow_h_shadow = parseInt(options[x]) + 'px ';
                    break;
                case 'shadow_v_shadow':
                    if (options[x] != '')
                        shadow_v_shadow = parseInt(options[x]) + 'px ';
                    break;
                case 'shadow_blur':
                    if (options[x] != '')
                        shadow_blur = parseInt(options[x]) + 'px ';
                    break;
                case 'shadow_color':
                    if (options[x] != '')
                        shadow_color = options[x] + ' ';
                    //$row.css('box-shadow','1px 1px 1px '+options[x]);
                    break;
                case 'back_type' :
                    back_type = options[x];
                    if (options[x] == 'parallax' || options[x] == 'video_fixed') {
                        rowbackpos = 'fixed';
                    }
                    if (options[x] == 'parallax_animated' || options[x] == 'video_parallax') {
                        rowbackpos = 'parallax';
                    }
                    if (options[x] == 'video' || options[x] == 'video_fixed' || options[x] == 'video_parallax') {
                        if (typeof options['back_video_source'] != 'undefined') {
                            rowbackvideo = options['back_video_source'];
                        }
                        else {
                            rowbackvideo = 'youtube';
                        }
                    }
                    if (options[x] == 'parallax')
                        $row.addClass('pbuilder_row_parallax');
                    else
                        $row.removeClass('pbuilder_row_parallax');
                    break;
                case 'back_color' :
                    if (options[x] != '')
                        back_color = options[x];
                    break;
                case 'back_color2' :
                    if (options[x] != '')
                        back_color2 = options[x];
                    break;
                case 'gradient_type' :
                    gradient_type = options[x] != '' ? options[x] : 'linear';
                    break;
                case 'back_image' :
                    if (options[x] != '')
                        rowbackimage = 'background-image:url(' + options[x] + ');';
                    break;
        				case 'back_image_zoom' :
                    if (options[x] != 'false'){
                        back_image_zoom = ' pbuilder-background-image-zoom ';
                    } else {
                      back_image_zoom ='';
                    }
                    break;
                case 'back_repeat' :
                    if (options[x] == 'repeat') {
                        rowbackrep = 'background-repeat:repeat;';
                    } else if (options[x] == 'repeatx') {
                        rowbackrep = 'background-repeat:repeat-x;background-position:center top;';
                    } else if (options[x] == 'repeaty') {
                        rowbackrep = 'background-repeat:repeat-y;background-position:center top;';
                    }
                    break;
                case 'back_size' :
                    rowbacksize = '';
                    if (options[x] == 'contain') {
                        rowbacksize = 'background-size:contain;';
                    } else if (options[x] == 'cover') {
                        rowbacksize = 'background-size:100%;background-size:100vw;';
                    }
                    break;
				case 'margin_padding' :
					margin_padding = options[x];
                    break;
            }
        }

    if(back_image_zoom == 'false') back_image_zoom='';
		var margin_padding_arr = margin_padding.split('|');
    
    

    $row.find('.pbuilder_row_colwrapper').css('margin-top', margin_padding_arr[0]+'px');
    if(margin_padding_arr[1]>0) $row.find('.pbuilder_row_colwrapper').css('margin-right', margin_padding_arr[1]+'px');
    $row.find('.pbuilder_row_colwrapper').css('margin-bottom', margin_padding_arr[2]+'px');
    if(margin_padding_arr[3]>0) $row.find('.pbuilder_row_colwrapper').css('margin-left', margin_padding_arr[3]+'px');

    $row.find('.pbuilder_row_colwrapper').css('padding-top', margin_padding_arr[4]+'px');
    $row.find('.pbuilder_row_colwrapper').css('padding-right', margin_padding_arr[5]+'px');
    $row.find('.pbuilder_row_colwrapper').css('padding-bottom', margin_padding_arr[6]+'px');
    $row.find('.pbuilder_row_colwrapper').css('padding-left', margin_padding_arr[7]+'px');
    
    
    if(typeof options['border'] === 'undefined'){
        options['border'] = 'false|0|solid|#000000|0|solid|#000000|0|solid|#000000|0|solid|#000000|0|solid|#000000';
      } else {
        var border_properties=options['border'].split('|');
        column_border_css = '';
        var horizontal_border = 0;
        var vertical_border = 0;
        
        if(border_properties[0]!='true'){
          if(parseInt(border_properties[1])>0){
            $row.css('border',border_properties[1]+' '+border_properties[2]+' '+border_properties[3]);
            horizontal_border=vertical_border=parseInt(border_properties[1]);
          }
        } else {
          $row.css('border','');
          if(parseInt(border_properties[4])>0){
            $row.css('border-top',border_properties[4]+' '+border_properties[5]+' '+border_properties[6]);
            vertical_border+=parseInt(border_properties[4]);
          }
          if(parseInt(border_properties[7])>0){
            $row.css('border-right',border_properties[7]+' '+border_properties[8]+' '+border_properties[9]);
          }
          if(parseInt(border_properties[10])>0){
            $row.css('border-bottom',border_properties[10]+' '+border_properties[11]+' '+border_properties[12]);
          }
          if(parseInt(border_properties[13])>0){            
            $row.css('border-left',border_properties[13]+' '+border_properties[14]+' '+border_properties[15]);
            horizontal_border+=parseInt(border_properties[13]);
          }
        }
        
        if(parseInt(options['border_round'])>0){
            $row.css('border-radius',parseInt(options['border_round']) + 'px');            
        }
      }
    

        var shadow = shadow_h_shadow + shadow_v_shadow + shadow_blur + shadow_color;
        if(shadow.length>0) {
			$row.css('box-shadow', shadow);
		}
        if (back_type == 'static' && back_color2 != "") {
            rowbackcolor += ' background: -webkit-' + gradient_type + '-gradient(' + back_color + ', ' + back_color2 + '); ';
            rowbackcolor += ' background: -o-' + gradient_type + '-gradient(' + back_color + ', ' + back_color2 + '); ';
            rowbackcolor += ' background: -moz-' + gradient_type + '-gradient(' + back_color + ', ' + back_color2 + '); ';
            rowbackcolor += ' background: ' + gradient_type + '-gradient(' + back_color + ', ' + back_color2 + '); ';
        } else {
            if(back_color.length>0){
				rowbackcolor += ' background-color: ' + back_color + '; ';
			}
        }
        var out;
        if (rowbackvideo != '') {
            var loop = (typeof options['back_video_loop'] == 'undefined' || options['back_video_loop'] != 'false');
            if (rowbackvideo == 'youtube') {
                id = 'yt' + Math.floor((Math.random() * 100000) + 1);
                if (typeof options['back_video_youtube_id'] != 'undefined' && options['back_video_youtube_id'] != '') {
                    out = '<div class="pbuilder_row_video pbuilder_row_background' + (rowbackpos == 'fixed' ? ' pbuilder_row_background_fixed' : '') + (rowbackpos == 'parallax' ? ' pbuilder_row_background_parallax' : '') + '">' +
                            '<div id="' + id + '" class="YTPlayer" style="display:block; margin: auto; background: rgba(0,0,0,0.5)" data-property="{videoURL:\'http://youtu.be/' + options['back_video_youtube_id'] + '\',containment:\'self\',startAt:1,mute:true,autoPlay:true' + (loop ? ',loop:true' : ',loop:false') + ',opacity:1,showControls:true,quality:\'hd720\'}"></div>' +
                            '</div>';
                }
                else {
                    out = '';
                }
            }
            else if (rowbackvideo == 'vimeo') {
                out = '<div class="pbuilder_row_video pbuilder_row_video_vimeo pbuilder_row_background' + (rowbackpos == 'fixed' ? ' pbuilder_row_background_fixed' : '') + (rowbackpos == 'parallax' ? ' pbuilder_row_background_parallax' : '') + '">' +
                        '<iframe src="//player.vimeo.com/video/' + options['back_video_vimeo_id'] + '?title=0&amp;byline=0&amp;portrait=0&amp;color=ffffff&amp;autoplay=1' + (loop ? '&amp;loop=1' : '') + '" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>' +
                        '</div>';
            }
            else {
                out = '<div class="pbuilder_row_video pbuilder_row_video_html5 pbuilder_row_background' + (rowbackpos == 'fixed' ? ' pbuilder_row_background_fixed' : '') + (rowbackpos == 'parallax' ? ' pbuilder_row_background_parallax' : '') + '">' +
                        '<video muted autoplay' + (loop ? ' loop' : '') +
                        (typeof options['back_video_html5_img'] != 'undefined' && options['back_video_html5_img'] != '' ? ' poster="' + options['back_video_html5_img'] + '"' : '') + ' >' +
                        (typeof options['back_video_html5_mp4'] != 'undefined' && options['back_video_html5_mp4'] != '' ? '<source src="' + options['back_video_html5_mp4'] + '" type="video/mp4">' : '') +
                        (typeof options['back_video_html5_webm'] != 'undefined' && options['back_video_html5_webm'] != '' ? '<source src="' + options['back_video_html5_webm'] + '" type="video/webm">' : '') +
                        (typeof options['back_video_html5_ogv'] != 'undefined' && options['back_video_html5_ogv'] != '' ? '<source src="' + options['back_video_html5_ogv'] + '" type="video/ogg">' : '') +
                        '</video></div>';
            }
        }
        else if (rowbackimage != '') {
            out = '<div class="pbuilder_row_background ' + back_image_zoom + (rowbackpos == 'fixed' ? ' pbuilder_row_background_fixed' : '') + (rowbackpos == 'parallax' ? ' pbuilder_row_background_parallax' : '') + '" ><div class="pbuilder_row_back_image" style="' + rowbackimage + rowbackcolor + rowbackrep + rowbacksize + '"></div></div>';
        }
        else if (rowbackcolor) {
            out = '<div class="pbuilder_row_background" style="' + rowbackcolor + '"></div>';
        }
        else {
            out = '';
        }

        $row.find('.pbuilder_row_background').remove();
                
        if(options['back_full_width'] == 'false'){
          $row.children('.pbuilder_row_colwrapper').prepend(out);
        } else {
          $row.prepend(out);
        }
        
        $row.trigger('refresh');

        if (timed_row == "true") {
            timed_row_min = parseInt(timed_row_min) * 60 * 1000;
            timed_row_sec = parseInt(timed_row_sec) * 1000;
            var duration = timed_row_min + timed_row_sec;
            
        }

    }



    function pbuilderCreateRowMenu(rowId, $item) {
        var rowJSON = $.extend(true, {}, pbuilder_row_controls);
        var html = '';

        var row_column_controls = pbuilder_items['rows'][rowId]['columns'].length;
        for (var x in rowJSON) {
            if (rowJSON[x]['type'] == 'collapsible') {
                for (var y in rowJSON[x]['options']) {
                    if ( typeof pbuilder_items['rows'][rowId]['options'] !== "undefined" && pbuilder_items['rows'][rowId]['options'] != null && typeof pbuilder_items['rows'][rowId]['options'][y] !== "undefined") {
                        rowJSON[x]['options'][y]['std'] = pbuilder_items['rows'][rowId]['options'][y];
                    }
                }
            }
            else if (typeof (pbuilder_items['rows'][rowId]['options']) !== "undefined" && typeof (pbuilder_items['rows'][rowId]['options'][x]) !== "undefined") {
                rowJSON[x]['std'] = pbuilder_items['rows'][rowId]['options'][x];
            }

            if(x == 'group_column_back'){
              row_column_controls=row_column_controls*2;
              for(y in rowJSON[x]['options']){
                row_column_controls--;
                if(row_column_controls<0){
                  delete(rowJSON[x]['options'][y]);
                }
              }
              var newControl = new pbuilderControl(x, rowJSON[x]);
              html += newControl.html();
            } else {
              var newControl = new pbuilderControl(x, rowJSON[x]);
              html += newControl.html();
            }

        }
        return html;
    }


    function pbuilderCreateColumnMenu(rowId, columnId, $item) {
        var columnJSON = $.extend(true, {}, pbuilder_column_controls);
        var html = '';
        var column_controls = Object.keys(pbuilder_items['columns'][rowId]).length;

        for (var x in columnJSON) {
            if (columnJSON[x]['type'] == 'collapsible') {
                for (var y in columnJSON[x]['options']) {
                    if (typeof (pbuilder_items['columns'][rowId][columnId]['options']) != "undefined" && typeof (pbuilder_items['columns'][rowId][columnId]['options'][y]) != "undefined") {
                        columnJSON[x]['options'][y]['std'] = pbuilder_items['columns'][rowId][columnId]['options'][y];
                    }
                }
            }
            else if (typeof (pbuilder_items['columns'][rowId][columnId]['options']) != "undefined" && typeof (pbuilder_items['columns'][rowId][columnId]['options'][x]) != "undefined") {
                columnJSON[x]['std'] = pbuilder_items['columns'][rowId][columnId]['options'][x];
            }

            if(x == 'group_column_back'){
              column_controls=column_controls*2;
              for(y in columnJSON[x]['options']){
                column_controls--;
                if(column_controls<0){
                  delete(columnJSON[x]['options'][y]);
                }
              }
              var newControl = new pbuilderControl(x, columnJSON[x]);
              html += newControl.html();
            } else {
              var newControl = new pbuilderControl(x, columnJSON[x]);
              html += newControl.html();
            }
        }
        return html;
    }


    function pbuilderCreateShortcodeMenu(itemId, $item) {
      
        var shortcodeJSON = $.extend(true, {}, pbuilder_shortcodes[$item.attr('data-shortcode')]);
        var html = '';
        for (var x in shortcodeJSON['options']) {
            if (shortcodeJSON['options'][x]['type'] == 'collapsible') {
                for (var y in shortcodeJSON['options'][x]['options']) {
                    if (typeof (pbuilder_items['items'][itemId]['options'][y]) != "undefined") {
                        shortcodeJSON['options'][x]['options'][y]['std'] = pbuilder_items['items'][itemId]['options'][y];
                    }
                }
            }
            else if (typeof (pbuilder_items['items'][itemId]['options'][x]) != "undefined") {
                shortcodeJSON['options'][x]['std'] = pbuilder_items['items'][itemId]['options'][x];
            }
            var newControl = new pbuilderControl(x, shortcodeJSON['options'][x]);
            html += newControl.html();
        }
        return html;
    }
    function pbuilderLoadContent(content) {
        var items = $.extend(true, {}, content);
        var output = '';
        var html = '';
        if (!$.isEmptyObject(items)) {
            if (typeof items['sidebar'] != 'undefined' && items['sidebar']['active']) {
                var sidebar = items['sidebar']['type'];
                html = '<div class="pbuilder_sidebar pbuilder_' + sidebar + ' pbuilder_row" data-rowid="sidebar"><div class="pbuilder_column">';
                for (var x in items['sidebar']['items']) {
                    if (typeof items['items'][items['sidebar']['items'][x]] != 'undefined' && items['items'][items['sidebar']['items'][x]] != null) {
                        html += '<div class="pbuilder_module" data-shortcode="' + items['items'][items['sidebar']['items'][x]]['slug'] + '" data-modid="' + items['sidebar']['items'][x] + '">';
                        html += '</div>';
                    }
                }
                html += '</div><div style="clear:both;"></div></div>';
            }
        }
        output += html +
                '<div id="pbuilder_content_wrapper"' + (sidebar != false ? ' class="pbuilder_content_' + sidebar + '"' : '') + '>' +
                '<div id="pbuilder_content">';
        if (!$.isEmptyObject(items)) {
            for (var rowId = 0; rowId < items['rowCount']; rowId++) {
                if (typeof items['rowOrder'] != 'undefined')
                    var row = items['rowOrder'][rowId];
                else
                    var row = null;
                if (row != null) {
                    var current = items['rows'][row];
                    html = pbuilder_rows[current['type']]['html'];
                    html = html.replace('%1$s', row);
                    var rowInterface = '<div class="pbuilder_row_controls"><a href="#" class="pbuilder_edit" title="Edit"></a><a href="#" class="pbuilder_drag_handle" title="Move"></a><a href="#" class="pbuilder_clone" title="Clone"></a><a class="pbuilder_copy" href="#" title="Copy"></a><a class="pbuilder_paste" href="#" title="Paste"></a><a href="#" class="pbuilder_delete" title="delete"></a></div>';
                    html = html.replace('%2$s', rowInterface);
                    for (var colId in current['columns']) {


						columnInterface = '<div class="pbuilder_column_controls pbuilder_gradient"><a href="#" class="pbuilder_edit" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a></div><div class="pbuilder_droppable">';
                        for (var x in current['columns'][colId]) {
                            if (typeof items['items'][current['columns'][colId][x]] != 'undefined' && items['items'][current['columns'][colId][x]] != null) {
                                var shortcode_slug = items['items'][current['columns'][colId][x]]['slug'];
                                columnInterface += '<div class="pbuilder_module" data-shortcode="' + shortcode_slug + '" data-modid="' + current['columns'][colId][x] + '">';
                                columnInterface += '<div class="pbuilder_module_controls pbuilder_gradient"><span class="pbuilder_module_name">' + pbuilder_shortcodes[shortcode_slug]['text'] + '</span> <img class="pbuilder_module_loader" src="' + pbuilder_url + 'images/save-loader.gif" /><a href="#" class="pbuilder_edit" title="Edit"></a><a href="#" class="pbuilder_clone" title="Clone"></a><a class="pbuilder_copy" href="#" title="Copy"></a><a class="pbuilder_paste" href="#" title="Paste"></a><a href="#" class="pbuilder_delete" title="delete"></a></div>';
                                columnInterface += '<div class="pbuilder_module_content"></div></div>';
                            }
                        }
                        columnInterface += '</div><div class="pbuilder_drop_borders"><div class="pbuilder_empty_content"><div class="pbuilder_add_shortcode pbuilder_gradient">+</div><span>Add Shortcode</span></div></div>';
                        html = html.replace('%' + (parseInt(colId) + 3) + '$s', columnInterface);
                    }
                    output += html;
                }
            }
        }
        output += '</div>' +
                '<div style="clear:both"></div>' +
                '</div>' +
                '<div style="clear:both"></div>';
        return output;
    }
    function pbuilderCloneCanvas(newCanvas, oldCanvas) {
        var context = newCanvas.getContext('2d');
        //set dimensions
        newCanvas.width = oldCanvas.width;
        newCanvas.height = oldCanvas.height;
        //apply the old canvas to the new one
        context.drawImage(oldCanvas, 0, 0);
    }
    function pbuilderHideControls($src, init, $mainquery) {

        var $shmenu = $('.pbuilder_shortcode_menu:first');
        if ($shmenu.hasClass('pbuilder_rowedit_menu')) {
            var shortcode = 'rowcontrols';
        } else if ($shmenu.hasClass('pbuilder_columnedit_menu')) {
            var shortcode = 'columncontrols';
        }
        else {
            var shortcode = $shmenu.attr('data-shortcode');
        }
        var name = $src.attr('name');

		//Hide/Show Control Groups
		for (var x in pbuilder_hideifs['groups'][shortcode]){
			for (var y in pbuilder_hideifs['groups'][shortcode][x]) {

			   var current_field_value=$('input[name="'+x+'"]').val();

			   if(pbuilder_hideifs['groups'][shortcode][x][y].indexOf(current_field_value) != -1){
				 $('div[data-name="'+y+'"]').addClass('pbuilder_collapsible_hidden');
			   } else {
				 $('div[data-name="'+y+'"]').removeClass('pbuilder_collapsible_hidden');
			   }
			}
		}


		var qquery = '';
        if (typeof $mainquery == 'undefined')
            $mainquery = $('body');
        if (typeof init == 'undefined') {
            if (name.substr(0, 5) != 'fsort') {
                if (typeof pbuilder_hideifs['parents'][shortcode] == 'object' && typeof pbuilder_hideifs['parents'][shortcode][name] != 'undefined') {
                    var objects = pbuilder_hideifs['parents'][shortcode][name];
                    for (var x in objects) {
                        if (objects[x]) {
                            if (typeof objects[x][0] == 'undefined') {
                                for (var y in objects[x])
                                {
                                    if (qquery != '')
                                        qquery += ', ';
                                    qquery += '[name$=' + y + ']';
                                }
                            } else {
                                if (qquery != '')
                                    qquery += ', ';
                                qquery += '[name=' + x + ']';
                            }
                        }
                    }
                }
            }
            else {
                var sliceName = name.split('-');
                var sortName = sliceName.slice(2).join('-');
                var sortStart = sliceName.slice(0, 2).join('-');
                var $sortHolder = $src.closest('.pbuilder_sortable_holder')
                var sortHolderName = $sortHolder.attr('data-name');
                if (typeof pbuilder_hideifs['parents'][shortcode][sortHolderName] == 'object' && typeof pbuilder_hideifs['parents'][shortcode][sortHolderName][sortName] != 'undefined') {
                    for (x in pbuilder_hideifs['parents'][shortcode][sortHolderName][sortName][sortHolderName]) {
                        if (qquery != '')
                            qquery += ', ';
                        qquery += '[name=' + sortStart + '-' + x + ']';
                    }
                }
            }
        }
        else {
            qquery = '.pbuilder_hidable_control';
        }
        $mainquery.find(qquery).each(function () {
            var hideBool = false;
            var hideName = $(this).attr('name');
            if (hideName.substr(0, 5) != 'fsort') {
                var hideArr = pbuilder_hideifs['children'][shortcode][hideName];
                for (var x in hideArr) {
                    var $hideObj = $('.pbuilder_shortcode_menu .pbuilder_control [name=' + x + ']:first');
                    if (hideArr[x].indexOf($hideObj.val()) != -1) {
                        hideBool = true;
                        break;
                    }
                }
            }
            else {
                var sliceName = hideName.split('-');
                var hideSName = sliceName.slice(2).join('-');
                var hideSStart = sliceName.slice(0, 2).join('-');
                var $hideSHolder = $(this).closest('.pbuilder_sortable_holder')
                var hideHName = $hideSHolder.attr('data-name');
                var hideArr = pbuilder_hideifs['children'][shortcode][hideHName][hideSName];
                for (var x in hideArr) {
                    if (!(hideArr[x] instanceof Array)) {
                        for (var y in hideArr[x]) {
                            if (hideArr[x][y] instanceof Array) {
                                var $hideObj = $hideSHolder.find('.pbuilder_control [name=' + hideSStart + '-' + y + ']:first');
                                if (hideArr[x][y].indexOf($hideObj.val()) != -1) {
                                    hideBool = true;
                                    break;
                                }
                            }
                            else {
                                var $hideObj = $hideSHolder.find('[name=' + x + ']:first');
                                if (hideArr[x][y] == $hideObj.val()) {
                                    hideBool = true;
                                    break;
                                }
                            }
                        }
                    }
                    else {
                        var $hideObj = $('.pbuilder_shortcode_menu .pbuilder_control [name=' + x + ']:first');
                        if (hideArr[x].indexOf($hideObj.val()) != -1) {
                            hideBool = true;
                            break;
                        }
                    }
                }
            }


            if (hideBool){
                $(this).closest('.pbuilder_control').addClass('pbuilder_control_hidden');
			} else {
                $(this).closest('.pbuilder_control').removeClass('pbuilder_control_hidden');
			}
        });
    }
    function jsonMod(key, value) {
        if (typeof (value) == "string") {
            return value.replace(/"/g, '&quot;');
        }
        if (typeof (value) == "array") {
            for (var x in value) {
                if (typeof (value[x]) == "string") {
                    value[x] = value[x].replace(/"/g, '&quot;');
                }
            }
        }

        return value;
    }
    function pbuilderAddCustomField(ind, stdlabel, stdtext, fieldtype) {
        var customfieldsdiv = $("#customfieldsdiv");
        var $menu = customfieldsdiv.closest(".pbuilder_shortcode_menu");
        var modid = $menu.attr("data-modid");
        if (ind == -1)
            ind = (typeof customfieldsdiv.attr("data-id") == "undefined" || $("input.pbuilder_input", customfieldsdiv).length == 0) ? 1 : parseInt(customfieldsdiv.attr("data-id")) + 1;// $("input.pbuilder_input", customfieldsdiv).length + 1;
        if (typeof customvalues[modid] != "undefined" && typeof customvalues[modid]["customfieldlabel" + ind] != "undefined")
            stdlabel = customvalues[modid]["customfieldlabel" + ind];
        if (stdlabel == "")
            stdlabel = "Enter Custom Field " + ind;
        var html = '';
        var controlOptions = {
            "type": "input",
            "label": "Label:",
            "desc": "Label for custom field",
            "std": stdlabel,
            "label_width": 0.5,
            "control_width": 0.5,
            "hide_if": {
                "customfields": ["false"],
                //"disablename":["true"],
                'formstyle': ['Horizontal'],
            }
        };
        var CustomField = new pbuilderControl("customfieldlabel" + ind, controlOptions);
        html += CustomField.html();
        if (typeof customvalues[modid] != "undefined" && typeof customvalues[modid]["customfield" + ind] != "undefined")
            stdtext = customvalues[modid]["customfield" + ind];
        if (stdtext == "")
            stdtext = "customfield" + ind;
        controlOptions = {
            "type": "input",
            "label": "Name:",
            "desc": "Input name for custom field",
            "std": stdtext,
            "label_width": 0.5,
            "control_width": 0.5,
            "hide_if": {
                "customfields": ["false"],
                //"disablename":["true"],
                'formstyle': ['Horizontal'],
            }
        };
        CustomField = new pbuilderControl("customfield" + ind, controlOptions);
        html += CustomField.html();
        var stdrequired = "false";
        if (typeof customvalues[modid] != "undefined" && typeof customvalues[modid]["customfieldrequired" + ind] != "undefined")
            stdrequired = customvalues[modid]["customfieldrequired" + ind];
        else
            stdrequired = "false";
        controlOptions = {
            "type": "checkbox",
            "label": "Required:",
            "std": stdrequired,
            "half_column": "true",
            "hide_if": {
                "customfields": ["false"],
                'formstyle': ['Horizontal'],
            }
        };
        CustomField = new pbuilderControl("customfieldrequired" + ind, controlOptions);
        html += CustomField.html();
        var stderror = "This field cannot be blank";
        if (typeof customvalues[modid] != "undefined" && typeof customvalues[modid]["customfielderror" + ind] != "undefined")
            stderror = customvalues[modid]["customfielderror" + ind];
        else
            stderror = "This field cannot be blank";
        var requiredid = "customfieldrequired" + ind + "";
        controlOptions = {
            "type": "input",
            "label": "",
            "label_width": 0,
            "control_width": 1,
            "std": stderror,
            "half_column": "true",
            "desc": "Error value for the field"
        };
        CustomField = new pbuilderControl("customfielderror" + ind, controlOptions);
        html += CustomField.html();
        controlOptions = {
            "id": "customfieldremove" + ind,
            "type": "button",
            "label": "X",
            "control_width": 0.5,
            "no_wrap": true,
            "class": "customfieldremove",
            "click": "pbuilderRemoveCustomField(this, '" + ind + "', '" + modid + "')",
            "hide_if": {
                "customfields": ["false"],
                //"disablename":["true"],
                'formstyle': ['Horizontal'],
            }
        };
        var CustomFieldRemove = new pbuilderControl("customfieldremove" + ind, controlOptions);
        //html += CustomFieldRemove.html();
        html = '<fieldset class="pbuilder_fieldset"><legend><span>Custom Field ' + ind + ':</span>' + CustomFieldRemove.html() + '</legend>' + html + '</fieldset>';
        //html = '<fieldset class="pbuilder_fieldset"><legend>Personalia:</legend>'+html+CustomFieldRemove.html()+'</fieldset>';
        customfieldsdiv.append(html);
        //customfieldsdiv.find("#pbuilder_input_customfield"+ind).eq(0).parent().parent().append(CustomFieldRemove.html());
        //pbuilder_hideifs['parents']['optin']['disablename']["customfield"+ind] = ['true'];
        pbuilder_hideifs['parents']['optin']['formstyle']["customfield" + ind] = ['Horizontal'];
        pbuilder_hideifs['parents']['optin']['customfields']["customfield" + ind] = ['false'];
        pbuilder_hideifs['children']['optin']["customfield" + ind] = {"customfields": ["false"]/*,"disablename":["true"]*/, "formstyle": ["Horizontal"]};
        //pbuilder_hideifs['parents']['optin']['disablename']["customfieldlabel"+ind] = ['true']
        pbuilder_hideifs['parents']['optin']['formstyle']["customfieldlabel" + ind] = ['Horizontal']
        pbuilder_hideifs['parents']['optin']['customfields']["customfieldlabel" + ind] = ['false']
        pbuilder_hideifs['children']['optin']["customfieldlabel" + ind] = {"customfields": ["false"]/*,"disablename":["true"]*/, "formstyle": ["Horizontal"]}
        pbuilder_hideifs['parents']['optin']['formstyle']["customfieldrequired" + ind] = ['Horizontal'];
        pbuilder_hideifs['parents']['optin']['customfields']["customfieldrequired" + ind] = ['false'];
        pbuilder_hideifs['children']['optin']["customfieldrequired" + ind] = {"customfields": ["false"], "formstyle": ["Horizontal"]};
        
        //pbuilder_items['items'][modid]['options']["customfield"+ind] = "";
        customfieldsdiv.attr("data-id", ind);
        pbuilder_items['items'][modid]['options']["customfield" + ind] = stdtext;
        pbuilder_items['items'][modid]['options']["customfieldlabel" + ind] = stdlabel;
        pbuilder_items['items'][modid]['options']["customfieldtype" + ind] = fieldtype;
        pbuilder_items['items'][modid]['options']["customfieldrequired" + ind] = stdrequired;
        pbuilder_items['items'][modid]['options']["customfielderror" + ind] = stderror;
        
        return ind;
    }
    function pbuilderAddHiddenField(ind, stdname, stdtext, fieldtype) {
        var hiddenfieldsdiv = $("#hiddenfieldsdiv");
        var $menu = hiddenfieldsdiv.closest(".pbuilder_shortcode_menu");
        var modid = $menu.attr("data-modid");
        if (ind == -1)
            ind = (typeof hiddenfieldsdiv.attr("data-id") == "undefined" || $("input.pbuilder_input", hiddenfieldsdiv).length == 0) ? 1 : parseInt(hiddenfieldsdiv.attr("data-id")) + 1;// $("input.pbuilder_input", hiddenfieldsdiv).length + 1;
        var html = '';
        if (typeof hiddenvalues[modid] != "undefined" && typeof hiddenvalues[modid]["hiddenfieldname" + ind] != "undefined")
            stdname = hiddenvalues[modid]["hiddenfieldname" + ind];
        var controlOptions = {
            "type": "input",
            "label": "Name:",
            "desc": "Name for hidden field",
            "std": stdname,
            "label_width": 0.5,
            "control_width": 0.5,
            "hide_if": {
                "hiddenfields": ["false"],
            }
        };
        var HiddenField = new pbuilderControl("hiddenfieldname" + ind, controlOptions);
        html += HiddenField.html();
        if (typeof hiddenvalues[modid] != "undefined" && typeof hiddenvalues[modid]["hiddenfield" + ind] != "undefined")
            stdtext = hiddenvalues[modid]["hiddenfield" + ind];
        controlOptions = {
            "type": "input",
            "label": "Value:",
            "desc": "Value for hidden field",
            "std": stdtext,
            "label_width": 0.5,
            "control_width": 0.5,
            "hide_if": {
                "hiddenfields": ["false"],
            }
        };
        HiddenField = new pbuilderControl("hiddenfield" + ind, controlOptions);
        html += HiddenField.html();
        controlOptions = {
            "id": "hiddenfieldremove" + ind,
            "type": "button",
            "label": "X",
            "control_width": 0.5,
            "no_wrap": true,
            "class": "hiddenfieldremove",
            "click": "pbuilderRemoveHiddenField(this, '" + ind + "', '" + modid + "')",
            "hide_if": {
                "hiddenfields": ["false"],
                //"disablename":["true"],
                "formstyle": ["Horizontal"],
            }
        };
        var HiddenFieldRemove = new pbuilderControl("hiddenfieldremove" + ind, controlOptions);
        //html += HiddenFieldRemove.html();
        html = '<fieldset class="pbuilder_fieldset"><legend><span>Hidden Field ' + ind + ':</span>' + HiddenFieldRemove.html() + '</legend>' + html + '</fieldset>';
        hiddenfieldsdiv.append(html);
        //hiddenfieldsdiv.find("#pbuilder_input_hiddenfield"+ind).eq(0).parent().parent().append(HiddenFieldRemove.html());
        pbuilder_hideifs['parents']['optin']['hiddenfields']["hiddenfield" + ind] = ['false']
        pbuilder_hideifs['children']['optin']["hiddenfield" + ind] = {"hiddenfields": ["false"]}
        pbuilder_hideifs['parents']['optin']['hiddenfields']["hiddenfieldname" + ind] = ['false']
        pbuilder_hideifs['children']['optin']["hiddenfieldname" + ind] = {"hiddenfields": ["false"]}
        //pbuilder_items['items'][modid]['options']["hiddenfield"+ind] = "";
        hiddenfieldsdiv.attr("data-id", ind);
        pbuilder_items['items'][modid]['options']["hiddenfield" + ind] = stdtext;
        pbuilder_items['items'][modid]['options']["hiddenfieldname" + ind] = stdname;
        pbuilder_items['items'][modid]['options']["hiddenfieldtype" + ind] = fieldtype;
    }
    /*  Refresh Profit Builder Controls  */
    function pbuilderRefreshControls($jq, $location) {
        if (typeof $jq == 'undefined')
            $jq = jQuery;
        if (typeof $location == 'undefined')
            $location = $('body');
        $location.find('#pbuilder_select_google_font').each(function () {
            $(this).on('change', function () {
                var name = $(this).attr('name');
                name = name.substr(0, name.length - 12);
                var $style = $location.find('[name="google_font_style"]').eq(0);
                var $styleCtrl = $style.closest('.pbuilder_control');
                var $styleUl = $styleCtrl.find('ul');
                var $styleMCSB = $styleCtrl.find('.mCSB_container');
                var $styleSpan = $styleCtrl.find('span:first');
                var font = $(this).val();
                var newOptions = {};
                if (font == 'default') {
                    newOptions = {'default': 'Default'};
                } else if ($.inArray(font, fontsStd) > -1) {
                    for (var x in fontsVar) {
                        var variant = fontsVar[x];
                        newOptions[variant] = variant.replace('+', ' ');
                    }
                } else {
                    for (var x in fontsObj['items']) {
                        font = font.replace(/\+/g, ' ');
                        if (fontsObj['items'][x]['family'] == font) {
                            for (y in fontsObj['items'][x]['variants']) {
                                var variant = fontsObj['items'][x]['variants'][y];
                                if (typeof variant == "string")
                                    newOptions[variant] = variant.replace('+', ' ');
                            }
                        }
                    }
                }
                $styleMCSB.empty();
                $style.empty(); // remove old options
                var firstOpt = true;
                $.each(newOptions, function (key, value) {
                    $style.append($('<option value="' + key + '">' + value + '</option>'));
                    $styleMCSB.append($('<li><a' + (firstOpt ? ' class="selected"' : '') + ' data-value="' + key + '">' + value + '</a></li>'));
                    if (firstOpt) {
                        $styleSpan.html(value);
                        firstOpt = false;
                    }
                });
                $style.val($styleMCSB.find("li a.selected").eq(0).attr('data-value'));
                $styleUl.fmCustomScrollbar('update');
                $style.trigger("keyup");
            });
        });
        $location.find("#addcustomfield").each(function () {
            $(this).bind('click', function () {
                var ind = pbuilderAddCustomField(-1, "", "", "newfield");
                $location.find("#pbuilder_input_customfieldlabel" + ind).eq(0).trigger("keyup");
            })
        });
        $location.find("#addhiddenfield").each(function () {
            $(this).bind('click', function () {
                pbuilderAddHiddenField(-1, "", "", "newfield");
            })
        });
        /* UI slider for number controles */
        $location.find(".pbuilder_number_bar").each(function () {
            if (!$(this).hasClass('ui-slider')) {
                var min = parseInt($(this).attr('data-min'));
                var max = parseInt($(this).attr('data-max'));
                var std = parseInt($(this).attr('data-std'));
                var step = parseInt($(this).attr('data-step'));
                var unit = $(this).attr('data-unit');
                $(this).slider({
                    min: min,
                    max: max,
                    step: step,
                    value: std,
                    range: "min",
                    slide: function (event, ui) {
                        var $pb_number_bar = $(this).closest('.pbuilder_control').find(".pbuilder_number_bar");
                        var def = (typeof $pb_number_bar.attr('data-default') != 'undefined' ? $pb_number_bar.attr('data-default') : '');
                        var min = parseInt($pb_number_bar.attr('data-min'));
                        if (ui.value <= min && def != '')
                            $(this).closest('.pbuilder_control').find(".pbuilder_number_amount").val(def);
                        else
                            $(this).closest('.pbuilder_control').find(".pbuilder_number_amount").val(ui.value + unit);
                    },
                    change: function (event, ui) {
                        var $input = $(this).closest('.pbuilder_control').find(".pbuilder_number_amount");
                        pbuilderContolChange($jq, $input, 400);
                    }
                });
            }
        });
        /* Sortable init on new controles */
        $location.find('.pbuilder_sortable').each(function () {
            if (!$(this).hasClass('ui-sortable')) {
                $(this).sortable({
                    items: '.pbuilder_sortable_item',
                    handle: '.pbuilder_sortable_handle',
					scroll : false,
                    stop: function (event, ui) {
                        var name = $(this).parent().attr('data-name');
                        var itemId = parseInt($('.pbuilder_shortcode_menu').attr('data-modid'));
                        pbuilder_items['items'][itemId]['options'][name]['order'] = {};
                        $(this).children('.pbuilder_sortable_item').each(function (index) {
                            pbuilder_items['items'][itemId]['options'][name]['order'][index] = parseInt($(this).attr('data-sortid'));
                        });
                        window.pbuilder_changes_made = true;
                        $('.pbuilder_shortcode_menu').trigger('fchange');
                    }
                });
            }
        });
        $location.find('.pbuilder_datetime .pbuilder_control_content .pbuilder_text').each(function () {
            var defaultValue = $(this).val();
            $(this).datetimepicker({
                defaultValue: defaultValue,
                timeFormat: 'HH:mm:ss z',
                onSelect: function (datetimeText, datepickerInstance) {
                    $(this).trigger("keyup");
                }
            });
            $(this).val(defaultValue);
        });
        /* Shortcode color control */
        var pbuilder_color_iris;
        $location.find('.pbuilder_color').each(function () {
            var $this = $(this);
            $(this).parent().find('.pbuilder_color_display span').css('background', $(this).val());
            
            $(this).spectrum({
                showAlpha: true,
                showInput: true,
                preferredFormat: "rgb",
                change: function(color) {
                    //color.toHexString(); // #ff0000
                    pbuilderContolChange($jq, $(this), true)
                }
            });
        });
        
        /* mCustomScrollbar when new items are created */
        $location.find('.pbuilder_select ul').each(function () {
            if (!$(this).hasClass('fmCustomScrollbar')) {
                $(this).fmCustomScrollbar({mouseWheelPixels: 150});
            }
        });
        $location.find('.fmCustomScrollbar').each(function () {
            $(this).fmCustomScrollbar('update');
        })
        $location.find('.pbuilder_icon_dropdown .pbuilder_icon_dropdown_scroll').each(function () {
            if (!$(this).hasClass('fmCustomScrollbar')) {
                $(this).fmCustomScrollbar();
            }
            else {
                $(this).fmCustomScrollbar('update');
            }
        });
        if (!$('.pbuilder_shortcode_menu').hasClass('fmCustomScrollbar')) {
            $('.pbuilder_shortcode_menu').fmCustomScrollbar({mouseWheelPixels: 150, advanced: {autoScrollOnFocus: false}});
        }
        else {
            $('.pbuilder_shortcode_menu').fmCustomScrollbar('update');
        }

        if ($("#pbuilder_body_frame").contents( ).hasClass(".parent_overlay"))
        {

            var formHTML = "";
            $("#pbuilder_body_frame").contents( ).find(".frb_textcenter form.overlayForm :input").each(function () {
                $(this).attr("readonly", true);
            });
            $("#pbuilder_body_frame").contents( ).find("#overlayForm").each(function () {
                // alert( $( this ) .html() );
                formHTML = $(this).html( );
            });
            //formHTML =  $.stripTags( formHTML );
            $location.find("#pbuilder_textarea_formcode").each(function () {
                $(this).val(formHTML);
            });
            var parent_overlay = $("#pbuilder_body_frame").contents( ).find(".parent_overlay").css({visibility: "hidden", display: "none"});
            $("#pbuilder_body_frame").contents( ).find(".width331").each(function () {
                var imgHeight = $("#pbuilder_body_frame").contents( ).find("#form_image_container").height( );
                var formHeight = $("#pbuilder_body_frame").contents( ).find("#form_container").height( );
                //alert('imgHeight: '+imgHeight);
                //alert('formHeight: '+formHeight);
                var diffH = formHeight - imgHeight;
                var HdiffH = parseInt(diffH / 2);
                //alert('HdiffH: '+HdiffH);
                $("#pbuilder_body_frame").contents( ).find("#form_image_container").css("margin", HdiffH + "px auto 0");
                //alert($("#pbuilder_body_frame") .contents( ) .find("#form_image_container") .attr( "style" ) );
            });	//	End of if( $("#pbuilder_body_frame") .contents( ) .find(".width331") )
            $("#pbuilder_body_frame").contents( ).find(".width521").each(function () {
                $("#pbuilder_body_frame").contents( ).find("#form_image_container").css("margin", "0 auto");
            });
            $("#pbuilder_body_frame").contents( ).find(".parent_overlay").css({visibility: "hidden", display: "none"});

        }	//	End of if( $("#pbuilder_body_frame") .contents( ) .find("#overlayForm") )


        //alert ('first: '+ JSON.stringify($location));
        //alert('second: '+$location.toSource());
        //$("#pbuilder_textarea_formcode") .val( $(".parent_overlay .frb_textcenter") .html() );

        /*$('i[class*="ba"]').each(function(){
         var reg = /ba/gi;
         var classes = $(this).attr("class");
         classes = classes.replace(reg, "fa");
         $(this).attr("class", classes);
         });*/
    }
    function pbuilderSortableInit($column) {
        $column.find('.pbuilder_droppable').sortable({
            items: '.pbuilder_module',
			scroll : false,
            connectWith: '.pbuilder_droppable',
            handle: '.pbuilder_module_controls .pbuilder_drag',
            start: function (event, ui) {
                pbuilder_sender = ui.item.parent();
                pbuilder_sender.css('z-index', '10');
            },
			scroll: false,
            stop: function (event, ui) {
                if (!ui.item.hasClass('pbuilder_module')) {
                    var shortcode_slug = ui.item.attr('data-shortcode');
                    ui.item.removeClass('pbuilder_draggable pbuilder_gradient').addClass('pbuilder_module').css('z-index', '2');
                    var moduleInterface = '<img class="pbuilder_module_loader" src="' + pbuilder_url + 'images/module-loader-new.gif" /><div class="pbuilder_module_controls pbuilder_gradient"><a href="#" class="pbuilder_edit" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a><a href="#" class="pbuilder_drag" title="Drag"><i class="fa fa-arrows" aria-hidden="true"></i></a><a href="#" class="pbuilder_clone" title="Clone"><i class="fa fa-clone" aria-hidden="true"></i></a><a class="pbuilder_copy" href="#" title="Copy"><i class="fa fa-files-o" aria-hidden="true"></i></a><a class="pbuilder_paste" href="#" title="Paste"><i class="fa fa-clipboard" aria-hidden="true"></i></a><a href="#" class="pbuilder_delete" title="Delete Element"><i class="fa fa-trash" aria-hidden="true"></i></a><a class="pbuilder_add_shortcode_column" href="#" title="Add Shortcode After Element"><i class="fa fa-plus-square" aria-hidden="true"></i></a></div>';
                    ui.item.html(moduleInterface + '<div class="pbuilder_module_content"></div>');
                    var sid = 0;
                    while (typeof pbuilder_items['items'][sid] != 'undefined') {
                        sid++;
                    }
                    ui.item.attr('data-modid', sid);
                    pbuilder_items['items'][sid] = {};
                    pbuilder_items['items'][sid]['f'] = pbuilder_shortcodes[shortcode_slug]['function'];
                    pbuilder_items['items'][sid]['slug'] = shortcode_slug;
                    pbuilder_items['items'][sid]['options'] = {};
                    for (var x in pbuilder_shortcodes[shortcode_slug]['options']) {
                        if (pbuilder_shortcodes[shortcode_slug]['options'][x]['type'] == 'sortable') {
                            pbuilder_items['items'][sid]['options'][x] = $.extend(true, {}, pbuilder_shortcodes[shortcode_slug]['options'][x]['std']);
                        }
                        else if (pbuilder_shortcodes[shortcode_slug]['options'][x]['type'] == 'collapsible') {
                            for (var y in pbuilder_shortcodes[shortcode_slug]['options'][x]['options']) {
                                if (pbuilder_shortcodes[shortcode_slug]['options'][x]['options'][y]['type'] == 'sortable') {
                                    pbuilder_items['items'][sid]['options'][y] = $.extend(true, {}, pbuilder_shortcodes[shortcode_slug]['options'][x]['options'][y]['std']);
                                }
                                else if (typeof pbuilder_shortcodes[shortcode_slug]['options'][x]['options'][y]['std'] != 'undefined') {
                                    pbuilder_items['items'][sid]['options'][y] = pbuilder_shortcodes[shortcode_slug]['options'][x]['options'][y]['std'];
                                }
                                else {
                                    pbuilder_items['items'][sid]['options'][y] = '';
                                }
                            }
                        }
                        else if (typeof pbuilder_shortcodes[shortcode_slug]['options'][x]['std'] != 'undefined') {
                            pbuilder_items['items'][sid]['options'][x] = pbuilder_shortcodes[shortcode_slug]['options'][x]['std'];
                        }
                        else {
                            pbuilder_items['items'][sid]['options'][x] = '';
                        }
                    }
                    pbuilderGetShortcode(pbuilder_items['items'][sid]['f'], ui.item.find('.pbuilder_module_content'), pbuilder_items['items'][sid]['options']);
                    ui.item.find('.pbuilder_edit').trigger('click');
                }
                else {
                    pbuilder_sender.css('z-index', 1);
                    if (pbuilder_sender.children('*').length == 0) {
                        pbuilder_sender.parent().addClass('empty');
//						pbuilder_sender.parent().next().addClass('empty');	bugs out the reappearance of add shortcode button to the first element on the right
                    }
                    var shortcode_slug = ui.item.attr('data-shortcode');
                    var sid = parseInt(ui.item.attr('data-modid'));

                    pbuilderGetShortcode(pbuilder_shortcodes[shortcode_slug]['function'], ui.item.find('.pbuilder_module_content'), pbuilder_items['items'][sid]['options']);
                    ui.item.find('.pbuilder_edit').trigger('click');
                }
                // update data
                for (var ii = 0; ii < 2; ii++) {
                    if (ii == 0) {
                        var $fbCol = ui.item.parent();
                    }
                    else {
                        if (pbuilder_sender[0] != ui.item.parent()[0]) {
                            var $fbCol = pbuilder_sender;
                        }
                        else {
                            break;
                        }
                    }
                    $fbCol = $fbCol.closest('.pbuilder_column');
                    if (ii == 0)
                        $fbCol.find('.pbuilder_droppable:first').parent().removeClass('empty');
                    var ind = parseInt($fbCol.attr('data-colnumber'));
                    var rowId = $fbCol.closest('[data-rowid]').attr('data-rowid');
                    if (rowId != 'sidebar') {
                        rowId = parseInt(rowId);
                        pbuilder_items['rows'][rowId]['columns'][ind] = new Array();
                        $fbCol.find('.pbuilder_module').each(function (index) {
                            pbuilder_items['rows'][rowId]['columns'][ind][index] = parseInt($(this).attr('data-modid'));
                        });
                    }
                    else {
                        if (typeof pbuilder_items['sidebar'] == 'undefined')
                            pbuilder_items['sidebar'] = {}
                        pbuilder_items['sidebar']['items'] = [];
                        $fbCol.find('.pbuilder_module').each(function (index) {
                            pbuilder_items['sidebar']['items'][index] = parseInt($(this).attr('data-modid'));
                        });
                    }
                }
            }
        });
    }
    function pbuilderRefreshDragg($jq) {
        var drElem = $jq('.pbuilder_droppable');
        drElem.each(function () {
            if ($jq(this).children('*').length == 0) {
                $jq(this).parent().addClass('empty');
            }
        })
        $jq(".pbuilder_draggable", document).draggable("option", "connectToSortable", drElem);
    }
    /*  Activate Profit Builder Controls  */
    function pbuilderControlsInit($jq, iDocument) {
        if (typeof $jq == 'undefined')
            $jq = jQuery;
        $jq('#pbuilder_content').sortable({
            items: "> div",
			scroll : false,
            handle: '.pbuilder_row_controls .pbuilder_drag_handle',
            stop: function (event, ui) {
                pbuilder_items['rowOrder'] = [];
                $jq('#pbuilder_content .pbuilder_row').each(function (index) {
                    pbuilder_items['rowOrder'][index] = parseInt($(this).attr('data-rowid'));
                });
                window.pbuilder_changes_made = true;
            }
        });
        $('.pbuilder_shortcode_tab_select').click(function () {
            if (!$(this).hasClass('active')) {
                $(this).addClass('active');
                $(this).closest('.pbuilder_shortcode_holder').find('.pbuilder_shortcode_tabs').show();
            }
            else {
                $(this).removeClass('active');
                $(this).closest('.pbuilder_shortcode_holder').find('.pbuilder_shortcode_tabs').hide();
            }
        });
        $('.pbuilder_shortcode_tab:first, .pbuilder_shortcode_group:first').addClass('active');
        $('.pbuilder_shortcode_tab').click(function (e) {
            e.preventDefault();
            var ind = $(this).index();
            var $holder = $(this).closest('.pbuilder_shortcode_holder');
            $holder.find('.pbuilder_shortcode_tab.active').removeClass('active');
            $holder.find('.pbuilder_shortcode_tab.after').removeClass('after');
            $(this).addClass('active');
            $(this).nextAll().addClass('after');
            $holder.find('.pbuilder_shortcode_group.active').removeClass('active');
            $holder.find('.pbuilder_shortcode_group').eq(ind).addClass('active').fmCustomScrollbar('update');
            $holder.find('.pbuilder_shortcode_tab_select img').attr('src', $(this).find('img').attr('src'));
            $holder.find('.pbuilder_shortcode_tab_select').trigger('click');
        })
        $(".pbuilder_shortcode_group").fmCustomScrollbar();
        $jq(".pbuilder_draggable", document).draggable({
            appendTo: $jq('body'),
            helper: 'clone',
            connectToSortable: $jq('.pbuilder_droppable'),
            start: function (event, ui) {
                ui.helper.css({width: $jq(this).width()});
                window.pbuilder_drag = true;
            },
            drag: function (event, ui) {
                ui.helper.css({marginTop: ui.offset.top - ui.position.top});
            }
        });
        $('.pbuilder_toggle_wrapper').hover(function () {
            $(this).stop(true).animate({bottom: 0}, 300);
        }, function () {
            $(this).stop(true).animate({bottom: -54}, 300);
        });
        $jq(iDocument).on('click', '.pbuilder_toggle_ctrl .frb_button', function (e) {
            e.preventDefault();
            $('.pbuilder_toggle').trigger('click');
        });
        $('.pbuilder_toggle').click(function (e) {
            e.preventDefault();
            if (!$(this).hasClass('active')) {
                $(this).addClass('active');
                $jq('.pbuilder_row_holder').hide();
                $jq('#pbuilder_wrapper').removeClass('edit');

                $('.pbuilder_shortcode_menu_toggle').stop(true).animate({right: -47}, 300);
                $('.pbuilder_shortcode_menu').stop(true).animate({right: -400}, 300);
                $('#pbuilder_body').stop(true).animate({borderLeftWidth: 0, borderRightWidth: 0}, 300);
            }
            else {
                $(this).removeClass('active');
                $jq('.pbuilder_row_holder').show();
                $jq('#pbuilder_wrapper').addClass('edit');

                if ($('.pbuilder_shortcode_menu').length > 0) {
                    $('.pbuilder_shortcode_menu_toggle').stop(true).animate({'right': 354}, 300);
                    $('.pbuilder_shortcode_menu').stop(true).animate({right: 0}, 300);
                    $('#pbuilder_body').stop(true).animate({borderRightWidth: 400}, 300);
                }
            }
        });
        //			shortcode menu toggle
        $(document).on('click', '.pbuilder_shortcode_menu_toggle', function () {
            $(this).stop(true).animate({'right': -47}, 300);
            $('.pbuilder_shortcode_menu').stop(true).animate({right: -400}, 300, function () {
                $(this).remove();
                pbuilder_shortcode_sw = false;
            });
            $('#pbuilder_body').stop(true).animate({borderRightWidth: 0}, 300);
            var savedData = {};
            if ($jq('.pbuilder_row.child_selected').length <= 0) {
                savedData['refid'] = parseInt($jq('.pbuilder_row.selected').attr('data-rowid'));
                savedData['type'] = 'row';
            } else {
                savedData['refid'] = parseInt($jq('.pbuilder_module.selected').attr('data-modid'));
                savedData['type'] = 'module';
            }
            $(this).data('menu_toggle_options', savedData);
            $jq('.pbuilder_module_controls.pbuilder_gradient_primary').removeClass('pbuilder_gradient_primary');
            $jq('.pbuilder_row.selected, .pbuilder_module.selected').removeClass('selected');
            $jq('.pbuilder_row.child_selected').removeClass('child_selected');
            jQuery('#pbuilder_body_frame').contents().find('.parent_overlay').css('display', 'none');


        });



		//control descriptions
        $(document).on('mouseenter', '.pbuilder_control [class*="_label"] label', function () {
            $(this).parent().addClass('hovered').find('.pbuilder_desc').show().stop(true).delay(500).animate({opacity: 1}, 200);
        });
        $(document).on('mouseleave', '.pbuilder_control [class*="_label"] label', function () {
            $(this).parent().removeClass('hovered').find('.pbuilder_desc').stop(true).animate({opacity: 0}, 200, function () {
                $(this).hide();
            });
        });
        $(document).on('mouseenter', '.pbuilder_control .pbuilder_checkbox', function () {
            $(this).siblings('.pbuilder_control [class*="_label"]').children('label').trigger('mouseenter');
        });
        $(document).on('mouseleave', '.pbuilder_control .pbuilder_checkbox', function () {
            $(this).siblings('.pbuilder_control [class*="_label"]').children('label').trigger('mouseleave');
        });
        //				add row popup
        $(document).on('click', '.pbuilder_add_row_popup_trigger', function (e) {
            e.preventDefault();
            $('#pbuilder_add_row_popup').show();
            $('#pbuilder_editor_popup_shadow').show();
        });
        $(document).on('click', '.pbuilder_popup#pbuilder_add_row_popup .pbuilder_button', function (e) {
            e.preventDefault();
            $('#pbuilder_add_row_popup').hide();
            $('#pbuilder_editor_popup_shadow').hide();
        });
        $(document).on('click', '.pbuilder_row_button', function (e) {
            e.preventDefault();
            window.pbuilder_changes_made = true;
            var value = parseInt($jq(this).attr('href').substr(1));
            var html = pbuilder_rows[value]['html'];
            var id = 0;
            while ($jq('#pbuilder_content .pbuilder_row[data-rowid=' + id + ']').length > 0)
                id++;
            html = html.replace('%1$s', id + '');
            var rowInterface = '<div class="pbuilder_row_controls pbuilder_gradient"><a href="#" class="pbuilder_edit" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a><a href="#" class="pbuilder_drag_handle" title="Drag"><i class="fa fa-arrows" aria-hidden="true"></i></a><a href="#" class="pbuilder_clone" title="Clone"><i class="fa fa-clone" aria-hidden="true"></i></a><a class="pbuilder_copy" href="#" title="Copy"><i class="fa fa-files-o" aria-hidden="true"></i></a><a class="pbuilder_paste" href="#" title="Paste"><i class="fa fa-clipboard" aria-hidden="true"></i></a><a href="#" class="pbuilder_delete" title="Delete Element"><i class="fa fa-trash" aria-hidden="true"></i></a><a class="pbuilder_new_row_button" href="#" title="Add new row"><i class="fa fa-plus-square" aria-hidden="true"></i></a></div>';
            html = html.replace('%2$s', rowInterface);
            var columnInterface = '<div class="pbuilder_column_controls pbuilder_gradient"><a href="#" class="pbuilder_edit" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a></div><div class="pbuilder_column_inner pbuilder_droppable empty"></div><div class="pbuilder_drop_borders"><div class="pbuilder_empty_content"><div class="pbuilder_add_shortcode pbuilder_gradient">+</div><span>Add Shortcode</span></div></div>';
            html = html.replace(/%[0-9]+\$s/g, columnInterface);
            if (typeof pbuilder_items.rows == 'undefined') {
                pbuilder_items.rows = new Array();
                pbuilder_items.rowCount = 0;
                pbuilder_items.rowOrder = new Array();
                pbuilder_items.items = new Array();
				        pbuilder_items.columns = {};
            }

      			if(typeof pbuilder_items.columns[id] == 'undefined'){
      				pbuilder_items.columns[id]={};
      			}

                  var columns = new Array();
                  var count = html.match(/pbuilder_column /g);
                  for (var x = 0; x < count.length; x++) {
                      columns[x] = new Array();
      				if(typeof pbuilder_items.columns[id][x] == 'undefined'){
      					pbuilder_items.columns[id][x]={};
      				}
            }
            pbuilder_items['rows'][id] = {type: value, columns: columns};


            if ($jq('#pbuilder_wrapper').hasClass('empty')) {
                $jq('#pbuilder_wrapper').removeClass('empty');
            }
            $('.pbuilder_popup#pbuilder_add_row_popup .pbuilder_button').trigger('click');
            if ($jq('.pbuilder_row.selected').length > 0) {
                $jq(html).insertAfter('.pbuilder_row.selected');
            } else if ($jq('.pbuilder_module.selected').length > 0) {
                $jq('.pbuilder_module.selected').closest('.pbuilder_row').each(function () {
                    $jq(html).insertAfter($(this));
                });
            } else {
                $jq('#pbuilder_content').append(html);
            }
            pbuilder_items['rowOrder'] = [];
            $jq('#pbuilder_content .pbuilder_row').each(function (index) {
                pbuilder_items['rowOrder'][index] = parseInt($jq(this).attr('data-rowid'));
            });
            pbuilder_items.rowCount = $jq('#pbuilder_content .pbuilder_row').length;
            pbuilderSortableInit($jq('#pbuilder_content .pbuilder_row[data-rowid=' + id + ']'));
            pbuilderRefreshDragg($jq);
            $jq('#pbuilder_content .pbuilder_row[data-rowid=' + id + '] .pbuilder_row_controls .pbuilder_edit').trigger('click');
            $jq('#pbuilder_wrapper').trigger('refresh');
            if ($('.pbuilder_toggle').hasClass('active')) {
                $('.pbuilder_toggle').trigger('click');
            }
            //$('#pbuilder_body_frame').contents().find('html').stop(true).animate({scrollTop: $jq('#pbuilder_content .pbuilder_row[data-rowid=' + id + ']').offset().top - 150}, 1000);
        });
        $('#pbuilder_body_frame').on('drag', function () {
            
        });
        $('.pbuilder_toggle_screen').click(function () {
            $('.pbuilder_toggle_screen.active').removeClass('active');
            $(this).addClass('active');
            if ($(this).find('.icon-desktop').length > 0)
                $('#pbuilder_body_frame').css({'min-width': $(this).attr('data-width') + 'px', 'max-width': '100%', 'width': '100%'});
            else if ($(this).find('.icon-laptop').length > 0)
                $('#pbuilder_body_frame').css({'width': '100%', 'min-width': $(this).attr('data-width') + 'px', 'max-width': (parseInt($(this).prev().attr('data-width')) - 1) + 'px'});
            else
                cumtomWidth = $(this).attr('data-width') == '1200' ? '100%' : $(this).attr('data-width') + 'px';
            $('#pbuilder_body_frame').css({'width': cumtomWidth, 'min-width': '0'});
            //$('#pbuilder_body_frame').css({'width':$(this).attr('data-width')+'px', 'min-width' : '0'});
            if ($(this).attr('data-width') == '340')
            {
                $('#pbuilder_body_frame').contents().find('.optinF .field input').css({'width': '87%'});
                $('#pbuilder_body_frame').contents().find('.formErrorContent').css({'margin-left': '-73px'});
                $('#pbuilder_body_frame').contents().find('.formError .formErrorArrow').css({'width': '0 auto'});
            }
            else if ($(this).attr('data-width') == '960')
            {
                $('#pbuilder_body_frame').contents().find('.optinF .field input').css({'width': '98.7%'});
            }
            else if ($(this).attr('data-width') == '768')
            {
                $('#pbuilder_body_frame').contents().find('.optinF .field input').css({'width': '97.4%'});
            }
            else
            {
                $('#pbuilder_body_frame').contents().find('.optinF .field input').css({'width': '100%'});
            }
        });
        if ($('#pbuilder_body_frame').width() > parseInt($('.pbuilder_toggle_screen:first').attr('data-width'))) {
            $('.pbuilder_toggle_screen:first').trigger('click');
        }
        else {
            $('.pbuilder_toggle_screen').eq(1).trigger('click');
        }
        $('body').keydown(function (e) {
            var code = e.keyCode || e.which;
            if (code == '9' && $('input:focus, select:focus, textarea:focus').length <= 0) {
                e.preventDefault();
                $('.pbuilder_toggle').trigger('click');
            }
        });
        $jq('body').keydown(function (e) {
            var code = e.keyCode || e.which;
            if (code == '9' && $jq('input:focus, select:focus, textarea:focus').length <= 0) {
                e.preventDefault();
                $('.pbuilder_toggle').trigger('click');
            }
        });
        $('.pbuilder_layout').change(function () {
            var layout = $(this).val();
            if (layout != 'full-width') {
                if ($jq('.pbuilder_sidebar').length <= 0) {
                    var html = '<div class="pbuilder_sidebar pbuilder_' + layout + ' pbuilder_row" data-rowid="sidebar">' +
                            '<div class="pbuilder_row_controls"><span class="pbuilder_sidebar_label">Sidebar</span></div>' +
                            '<div class="pbuilder_column"><div class="pbuilder_column_controls pbuilder_gradient"><a href="#" class="pbuilder_edit" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a></div>' +
                            '<div class="pbuilder_droppable">';
                    html += '</div>' +
                            '<div class="pbuilder_drop_borders"><div class="pbuilder_empty_content"><div class="pbuilder_add_shortcode pbuilder_gradient">+</div><span>Add Shortcode</span></div></div>' +
                            '</div></div>';
                    $jq('#pbuilder_wrapper').prepend(html);
                    if (typeof pbuilder_items['sidebar'] == 'undefined') {
                        pbuilder_items['sidebar'] = {active: true, type: layout, items: []};
                    }
                    else {
                        pbuilder_items['sidebar']['active'] = true;
                        pbuilder_items['sidebar']['type'] = layout;

                        for (var s in pbuilder_items['sidebar']['items']) {
                            var sid = pbuilder_items['sidebar']['items'][s];
                            if (typeof sid != 'undefined') {
                                shortcode_slug = pbuilder_items['items'][sid]['slug'];
                                var moduleInterface = '<img class="pbuilder_module_loader" src="' + pbuilder_url + 'images/module-loader-new.gif" /><div class="pbuilder_module_controls pbuilder_gradient"><a href="#" class="pbuilder_edit" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a><a href="#" class="pbuilder_drag" title="Drag"><i class="fa fa-arrows" aria-hidden="true"></i></a><a href="#" class="pbuilder_clone" title="Clone"><i class="fa fa-clone" aria-hidden="true"></i></a><a class="pbuilder_copy" href="#" title="Copy"><i class="fa fa-files-o" aria-hidden="true"></i></a><a class="pbuilder_paste" href="#" title="Paste"><i class="fa fa-clipboard" aria-hidden="true"></i></a><a href="#" class="pbuilder_delete" title="Delete Element"><i class="fa fa-trash" aria-hidden="true"></i></a><a class="pbuilder_add_shortcode_column" href="#" title="Add Shortcode After Element"><i class="fa fa-plus-square" aria-hidden="true"></i></a></div>';
                                $jq('.pbuilder_sidebar .pbuilder_droppable').append('<div class="pbuilder_module" data-modid="' + sid + '" data-shortcode="' + shortcode_slug + '">' + moduleInterface + '<div class="pbuilder_module_content"></div></div>');
                                pbuilderGetShortcode(pbuilder_items['items'][sid]['f'], $jq('.pbuilder_sidebar').find('.pbuilder_module_content:last'), pbuilder_items['items'][sid]['options']);
                            }
                        }
                    }
                    pbuilderSortableInit($jq('.pbuilder_sidebar .pbuilder_column'));
                    pbuilderRefreshDragg($jq);
                }
                else {
                    pbuilder_items['sidebar']['type'] = layout;
                    $jq('.pbuilder_sidebar').attr('class', 'pbuilder_sidebar pbuilder_' + layout);
                }
            }
            else {
                pbuilder_items['sidebar']['active'] = false;
                $jq('.pbuilder_sidebar').remove();
            }
            $jq('#pbuilder_wrapper').removeClass('pbuilder_wrapper_full-width pbuilder_wrapper_one-third-right-sidebar pbuilder_wrapper_one-third-left-sidebar pbuilder_wrapper_one-fourth-left-sidebar pbuilder_wrapper_one-fourth-right-sidebar').addClass('pbuilder_wrapper_' + layout + ' pbuilder_row');
        });
        function jsonMod(key, value) {
            if (typeof (value) == "string") {
                return value.replace(/"/g, '&quot;');
            }
            if (typeof (value) == "array") {
                for (var x in value) {
                    if (typeof (value[x]) == "string") {
                        value[x] = value[x].replace(/"/g, '&quot;');
                    }
                }
            }
            return value;
        }
        $('.pbuilder_disabled').click(function (e) {
            e.preventDefault();
        });
        $('#undo').click(function () {
            undo();
            var local_counter = localStorage.getItem("counter");
            local_counter--;
            localStorage.setItem("counter", local_counter);
            var local_counter1 = localStorage.getItem("counter");
            if (local_counter1 >= 1) {
                edit_menu_html();
            }
            else
            {
                close_edit_menu();
            }
        });
        $('#redo').click(function () {
            //var oiframe = document.getElementById('pbuilder_body_frame');
            //oiframe.contentWindow.location.reload();
            redo();
            var local_counter = localStorage.getItem("counter");
            var local_counter2 = localStorage.getItem("counter_lim");
            local_counter++;
            localStorage.setItem("counter", local_counter);
            var local_counter1 = localStorage.getItem("counter");
            if (local_counter1 > 1 && local_counter1 < local_counter2) {
                edit_menu_html();
            }
            else
            {
                close_edit_menu();
                localStorage.setItem("counter", 1);
                localStorage.setItem("counter_lim", 1);
            }
        });
        function edit_menu_html() {
            if (typeof pass_mod != 'undefined' && pass_mod != null) {
                var id = parseInt(pass_mod.attr('data-modid'));
                var htm = pbuilderCreateShortcodeMenu(id, pass_mod);
                jQuery('div.mCSB_container form div.pbuilder_menu_inner').html(htm);
            }
        }
        function close_edit_menu() {
            $('.pbuilder_shortcode_menu_toggle').stop(true).animate({'right': -47}, 300);
            $('.pbuilder_shortcode_menu').stop(true).animate({right: -400}, 300, function () {
                $(this).remove();
                pbuilder_shortcode_sw = false;
            });
            $('#pbuilder_body').stop(true).animate({borderRightWidth: 0}, 300);
            var savedData = {};
            if ($jq('.pbuilder_row.child_selected').length <= 0) {
                savedData['refid'] = parseInt($jq('.pbuilder_row.selected').attr('data-rowid'));
                savedData['type'] = 'row';
            } else {
                savedData['refid'] = parseInt($jq('.pbuilder_module.selected').attr('data-modid'));
                savedData['type'] = 'module';
            }
            $(this).data('menu_toggle_options', savedData);
            $jq('.pbuilder_module_controls.pbuilder_gradient_primary').removeClass('pbuilder_gradient_primary');
            $jq('.pbuilder_row.selected, .pbuilder_module.selected').removeClass('selected');
            $jq('.pbuilder_row.child_selected').removeClass('child_selected');
        }




        $('.pbuilder_save').click(function (e) {
            e.preventDefault();

			      var codedJSON = JSON.stringify(pbuilder_items, jsonMod);
            var data = {
                action: 'pbuilder_save',
                id: post_id,
                json: codedJSON
            }

            if (typeof window.pbuilder_saveajax != 'undefined')
                window.pbuilder_saveajax.abort();
            var $this = $(this);
            $this.find('.save_loader').show();
            window.pbuilder_saveajax = $.post(ajaxurl, data, function (response) {
                $this.find('.save_loader').hide();
                window.pbuilder_changes_made = false;
            });
        });
        $('.pbuilder_false_save').click(function (e) {
            alert('You can\'t save here!');
        });
        $('.pbuilder_save_template').click(function (e) {
            e.preventDefault();
			pbuilder_close_shortcode_menu();

            var html = '<div class="pbuilder_popup pbuilder_popup_template pbuilder_controls_wrapper"><div class="pbuilder_module_controls pbuilder_gradient"><span class="pbuilder_module_name">Save template</span> <a href="#" class="pbuilder_close" title="close"></a></div><div class="pbuilder_popup_content">';
            html += '<table><tr><td><p>';
            html += 'Template name';
            html += '</p></td><td>';
            var shJson = {
                type: 'input',
                label: '',
                label_width: 0,
                control_width: 1
            }
            var ctrl = new pbuilderControl('template_name', shJson);
            html += ctrl.html();
            html += '</td></tr></table>';
            html += '<a href="#" class="pbuilder_gradient pbuilder_button pbuilder_popup_close right">Close</a><img class="pbuilder_popup_button_loader right" alt="" src="' + pbuilder_url + 'images/save-loader.gif"></img><a href="#" class="pbuilder_gradient pbuilder_button pbuilder_popup_save right">Save</a>';
            html += '</div></div><div class="pbuilder_popup_shadow"></div>';
            $('#pbuilder_body').prepend(html);
        });

        $(document).on('change','#pbuilder_select_push_through_flow_id',function (e) {
            get_lfp_flow_fields();
        });

        $(document).on('change','#pbuilder_checkbox_push_through_flow',function (e) {
            if($('#pbuilder_checkbox_push_through_flow').val()=='true'){
              get_lfp_flow_fields();
            }
        });

        function get_lfp_flow_fields(){
            if($('#pbuilder_checkbox_push_through_flow').val()=='true'){
              var data = {
                  action: 'pbuilder_lfpflow_fields',
                  flow_id: $("#pbuilder_select_push_through_flow_id").val()
              }
              window.pbuilder_popupajax = $.get(ajaxurl, data, function (response) {
                $('.pbuilder_lfpflow_fields').remove();
                $('#pbuilder_select_push_through_flow_id').parent().parent().append(response);
              });
            }
        }
		var current_revision_id=0;

		function pbuilder_close_shortcode_menu(){
			$(this).stop(true).animate({'right': -47}, 300);
            $('.pbuilder_shortcode_menu').stop(true).animate({right: -400}, 300, function () {
                $(this).remove();
                pbuilder_shortcode_sw = false;
            });
            $('#pbuilder_body').stop(true).animate({borderRightWidth: 0}, 300);
            var savedData = {};
            if ($jq('.pbuilder_row.child_selected').length <= 0) {
                savedData['refid'] = parseInt($jq('.pbuilder_row.selected').attr('data-rowid'));
                savedData['type'] = 'row';
            } else {
                savedData['refid'] = parseInt($jq('.pbuilder_module.selected').attr('data-modid'));
                savedData['type'] = 'module';
            }
            $(this).data('menu_toggle_options', savedData);
            $jq('.pbuilder_module_controls.pbuilder_gradient_primary').removeClass('pbuilder_gradient_primary');
            $jq('.pbuilder_row.selected, .pbuilder_module.selected').removeClass('selected');
            $jq('.pbuilder_row.child_selected').removeClass('child_selected');
            jQuery('#pbuilder_body_frame').contents().find('.parent_overlay').css('display', 'none');

			$('.pbuilder_popup').hide();
			$('.pbuilder_popup_shadow').remove();
		}

		$('.pbuilder_show_revisions').click(function (e) {
            e.preventDefault();
      			pbuilder_close_shortcode_menu();
            $('.pbuilder_save').trigger('click');

            var html = '<div class="pbuilder_popup pbuilder_popup_revisions pbuilder_controls_wrapper">\
      			<div class="pbuilder_popup_revisions_title">Page Revisions</div>\
      			<div class="pbuilder_popup_revisions_info">Click on a revision to load it into the current page.</div>\
      			<div class="pbuilder_popup_content pbuilder_revisions_content">'+pbuilder_popup_loader+'</div>';
      			html += '</div>';

      			$('#pbuilder_body').prepend(html);
      			var data = {
                      action: 'pbuilder_page_revisions',
      				        id: post_id
                  }
                  window.pbuilder_popupajax = $.get(ajaxurl, data, function (response) {

          				revisions_html='<ul class="pbuilder_available_revisions">';
          				for(i in response.data){
          					if(i==0){
          						current_revision_id=response.data[i].page_id;
          						revisions_html+='<li class="pbuilder_load_revision pbuilder_revision_active" id="pbuilder_revision_'+response.data[i].page_id+'"><i class="fa fa-clock-o" aria-hidden="true"></i> '+response.data[i].page_date+'</li>';
          					} else {
          						revisions_html+='<li class="pbuilder_load_revision" id="pbuilder_revision_'+response.data[i].page_id+'"><i class="fa fa-clock-o" aria-hidden="true"></i> '+response.data[i].page_date+'</li>';
          					}
          				}
          				revisions_html+='</ul><a href="#" id="pbuilder_apply_revision" style="display:none;" class="pbuilder_gradient pbuilder_button pbuilder_popup_close left">Apply</a> <a href="#" class="pbuilder_gradient pbuilder_button pbuilder_cancel_revision right">Cancel</a>';
          				$('.pbuilder_revisions_content').html(revisions_html);
      			});
        });

		// AB Tests
		var pbuilder_popup_loader='<div class="pbuilder_loader"><i class="fa fa-circle-o-notch fa-spin fa-3x fa-fw margin-bottom"></i></div>';

		$('.pbuilder_show_abtest').click(function (e) {
            e.preventDefault();

			pbuilder_close_shortcode_menu();


            var html = '<div class="pbuilder_popup pbuilder_popup_abtests pbuilder_controls_wrapper">\
			<div class="pbuilder_popup_abtest_title">A/B Test</div>\
			<div class="pbuilder_popup_content pbuilder_abtest_content">'+pbuilder_popup_loader+'</div>';
			html += '</div><div class="pbuilder_popup_shadow"></div>';

			$('#pbuilder_body').prepend(html);

			var data = {
                action: 'pbuilder_show_abtest',
				id: post_id
            }
            window.pbuilder_popupajax = $.get(ajaxurl, data, function (response) {

				revisions_html=response;
				revisions_html+='<a href="#" class="pbuilder_gradient pbuilder_button pbuilder_popup_close right">Cancel</a>';
				$('.pbuilder_abtest_content').html(revisions_html);
			});
        });

		$('.wp-editor-area').on('change',function(){
			var page_content=$('.wp-editor-area').val();
			if(page_content.search('data-track')>0 || page_content.search('so-track')>0){
			   $('#so-conversion-link-warning').hide();
			} else {
			   $('#so-conversion-link-warning').show();
			}
		  });

		  $(document).on('click','#so_enable_split_test',function(){
			$('#so_enable_split_test').hide();
			$('#so_split_test_settings').show();
		  });

		  $(document).on('click','#so_enable_split_test_submit',function(){

			  $('.so-spinner').show();

			  $('#so_enable_split_test_submit').attr('disabled','disabled');

			  $.ajax({
				type: 'POST',
				url: ajaxurl,
				data:{action:'so_setup_split_test',
					  pages:$('input[name=so-slit-pages]:checked').val(),
					  end_value:$('input[name=so-test-end-value]').val(),
					  end_type:$('select[name=so-test-end-type] option:selected').val(),
					  end_winner:$('select[name=so-test-end-winner] option:selected').val(),
					  sopost:$('input[name=so-post]').val()
					  },
				dataType: 'json'}
			  ).done(function(response) {
				if(response.success){
				  $('.so-spinner').hide();
				  location.reload();
				} else {
					alert('An Error Occured. Please reload the page and try again.');
				}
			  })
			  .fail(function(response){
				  alert('An Error Occured. Please reload the page and try again.');
			  });
		  });

		  $(document).on('click','#so_add_new_page',function(){

			  if($(this).attr('disabled')=='disabled'){
				  return;
			  }

			  $('#so_add_new_page .pbso-button-spinner-wrapper').show();
			  $('#so_add_new_page').attr('disabled','disabled');


			  $.ajax({
				type: 'POST',
				url: ajaxurl,
				data:{action:'so_add_page',sopost:$('input[name=so-post]').val() },
				dataType: 'json'}
			  ).done(function(response) {
				if(response.success){
				  location.reload();
				} else {
					alert('An Error Occured. Please reload the page and try again.');
				}
			  })
			  .fail(function(response){
				  alert('An Error Occured. Please reload the page and try again.');
			  });
		  });


		  $(document).on('click','.so_clone_page',function(){

			  if($(this).attr('disabled')=='disabled'){
				  return;
			  }

			  $(this).children('.pbso-button-spinner-wrapper').show();
			  $('.so_clone_page').attr('disabled','disabled');

			  $.ajax({
				type: 'POST',
				url: ajaxurl,
				data:{action:'so_clone_page',sopost:$('input[name=so-post]').val(),so_clone_page:this.id.replace('so_clone_','') },
				dataType: 'json'}
			  ).done(function(response) {
				if(response.success){
				  location.reload();
				} else {
					alert('An Error Occured. Please reload the page and try again.');
				}
			  })
			  .fail(function(response){
				  alert('An Error Occured. Please reload the page and try again.');
			  });
		  });

		  $(document).on('click','.so_choose_winner',function(){


			  if($(this).attr('disabled')=='disabled'){
				  return;
			  }

			  $(this).children('.pbso-button-spinner-wrapper').show();

			  $('.so_choose_winner').attr('disabled','disabled');

			  $.ajax({
				type: 'POST',
				url: ajaxurl,
				data:{action:'so_choose_winner', so_page:$('input[name=so-post]').val(), so_winner_page:this.id.replace('so_winner_','')},
				dataType: 'json'}
			  ).done(function(response) {
				if(response.success){
				  location.reload();
				} else {
				  alert('An Error Occured. Please reload the page and try again.');
				}
			  })
			  .fail(function(response){
				  alert('An Error Occured. Please reload the page and try again.');
			  });
		  });


		  $(document).on('click','.so_delete_page',function(){


			  if($(this).attr('disabled')=='disabled'){
				  return;
			  }

			  //$(this,'.pbso-button-spinner-wrapper').show();
			  $(this).children('.pbso-button-spinner-wrapper').show();


			  $('.so_delete_page').attr('disabled','disabled');

			  $.ajax({
				type: 'POST',
				url: ajaxurl,
				data:{action:'so_delete_page', current_page:post_id, pbso_live_editor:'true', so_page:$('input[name=so-post]').val(), so_delete_page:this.id.replace('so_delete_','')},
				dataType: 'json'}
			  ).done(function(response) {
				if(response.success){
				  if(response.data && response.data.redirect){
					alert('The variation you were editing was deleted. Redirecting to the first existing variation.');
					location.href=response.data.redirect;
				  } else {
					alert('Variation deleted.');
					location.reload();
				  }
				} else {
				  alert('An Error Occured. Please reload the page and try again.');
				}
			  })
			  .fail(function(response){
				  alert('An Error Occured. Please reload the page and try again.');
			  });
		  });

		  $(document).on('click','#so_update_split_test_submit',function(){
			  $('.so-spinner').show();
			  $('#so_update_split_test_submit').attr('disabled','disabled');

			  $.ajax({
				type: 'POST',
				url: ajaxurl,
				data:{action:'so_update_split_test',
					  end_value:$('input[name=so-test-end-value]').val(),
					  end_type:$('select[name=so-test-end-type] option:selected').val(),
					  end_winner:$('select[name=so-test-end-winner] option:selected').val(),
					  sopost:$('input[name=so-post]').val()
					  },
				dataType: 'json'}
			  ).done(function(response) {
				if(response.success){
				  $('.so-spinner').hide();
				  location.reload();
				} else {
				  alert('An Error Occured. Please reload the page and try again.');
				}
			  })
			  .fail(function(response){
				  alert('An Error Occured. Please reload the page and try again.');
			  });
		  });

		  $(document).on('click','#so_reset_split_test_submit',function(){
			  $('.so-spinner').show();
			  $('#so_reset_split_test_submit').attr('disabled','disabled');

			  $.ajax({
				type: 'POST',
				url: ajaxurl,
				data:{action:'so_reset_split_test',
					  sopost:$('input[name=so-post]').val()
					  },
				dataType: 'json'}
			  ).done(function(response) {
				if(response.success){
				  $('.so-spinner').hide();
				  location.reload();
				} else {
				  alert('An Error Occured. Please reload the page and try again.');
				}
			  })
			  .fail(function(response){
				  alert('An Error Occured. Please reload the page and try again.');
			  });
		  });



		// Funnel

		$(document).on('click','.pbuilder_show_funnel',function (e) {
            e.preventDefault();

			pbuilder_close_shortcode_menu();

            var html = '<div class="pbuilder_popup pbuilder_popup_abtests pbuilder_controls_wrapper">\
			<div class="pbuilder_popup_funnel_title">Funnel Editor</div>\
			<div class="pbuilder_popup_content pbuilder_funnel_content">'+pbuilder_popup_loader+'</div>';
			html += '</div><div class="pbuilder_popup_shadow"></div>';

			$('#pbuilder_body').prepend(html);

			var data = {
                action: 'pbuilder_page_funnels',
				id: post_id,
				pbso_live_editor:'true'
            }
            window.pbuilder_popupajax = $.get(ajaxurl, data, function (response) {
				revisions_html=response;
				$('.pbuilder_funnel_content').html(revisions_html);
			});
        });

		$(document).on('click','#pb_new_funnel_show_form',function (e) {
			$('#pb_new_funnel_form').show();
		});


		$(document).on('click','#pb_new_funnel_add',function (e) {


			var data = {
                action: 'pbuilder_add_funnel',
				id: post_id,
				funnel_name:$('#new_funnel_name').val(),
				pbso_live_editor:'true'
            }

			$('.pbuilder_funnel_content').html(pbuilder_popup_loader);
			window.pbuilder_popupajax = $.get(ajaxurl, data, function (response) {
				revisions_html=response;
				$('.pbuilder_funnel_content').html(revisions_html);
			});
		});

		$(document).on('click','.pb_funnel_show_pages',function (e) {

			var funnel_id=$(this).data('funnel');

			var data = {
                action: 'pbuilder_funnel_pages',
				page_id: post_id,
				funnel_id:funnel_id,
				pbso_live_editor:'true'
      }

			$('.pbuilder_funnel_content').html(pbuilder_popup_loader);
			window.pbuilder_popupajax = $.get(ajaxurl, data, function (response) {
				revisions_html=response;
				$('.pbuilder_funnel_content').html(revisions_html);

				$("#funnel_pages").sortable({
					stop: function( event, ui ) {
						$('#pb_funnel_update').show();
            window.pbuilder_changes_made = true;
					},
					handle: '.pbuilder_funnel_page_drag'
				});
    			$("#funnel_pages").disableSelection();
			});
		});

		$(document).on('click','#pb_funnel_update',function (e) {

			if($(this).attr('disabled')=='disabled'){
				return;
			}

			//$(this,'.pbso-button-spinner-wrapper').show();
			$(this).children('.pbso-button-spinner-wrapper').show();


			$('#pb_funnel_update').attr('disabled','disabled');

			var funnel_pages = $("#funnel_pages").sortable("toArray");
			var funnel_id=$(this).data('funnel');


			var data = {
          action: 'pbuilder_funnel_update',
          funnel_pages: funnel_pages,
          funnel_id:funnel_id,
          pbso_live_editor:'true'
      }

			window.pbuilder_popupajax = $.get(ajaxurl, data, function (response) {
				$('#pb_funnel_update').hide();
				$('#pb_funnel_update').children('.pbso-button-spinner-wrapper').hide();
        $('#pb_funnel_update').attr('disabled','');
			});
		});

		$(document).on('click','.pbuilder_funnel_remove_page',function(e){
			if($(this).attr('disabled')=='disabled'){
				return;
			}
			$(this).children('.pbso-button-spinner-wrapper').show();
			$('.pbuilder_funnel_remove_page').attr('disabled','disabled');

			var funnel_id=$(this).data('funnel');
			var page_id=$(this).data('page');

			var data = {
                action: 'pbuilder_funnel_remove_page',
				page_id: page_id,
				funnel_id:funnel_id,
				pbso_live_editor:'true'
            }

			window.pbuilder_popupajax = $.get(ajaxurl, data, function (response) {
				$('.pbuilder_funnel_content').html(response);
			});
		});

		$(document).on('click','.pb_funnel_delete',function(e){
			var funnel_id=$(this).data('funnel');

			var data = {
                action: 'pbuilder_funnel_delete',
				id: post_id,
				funnel_id:funnel_id,
				pbso_live_editor:'true'
            }

			$('.pbuilder_funnel_content').html(pbuilder_popup_loader);
			window.pbuilder_popupajax = $.get(ajaxurl, data, function (response) {
				revisions_html=response;
				$('.pbuilder_funnel_content').html(revisions_html);
			});
		});

		$(document).on('click','#pb_funnel_back',function(e){

			var data = {
                action: 'pbuilder_page_funnels',
				id: post_id,
				pbso_live_editor:'true'
            }

			$('.pbuilder_funnel_content').html(pbuilder_popup_loader);
            window.pbuilder_popupajax = $.get(ajaxurl, data, function (response) {
				revisions_html=response;
				$('.pbuilder_funnel_content').html(revisions_html);
			});
		});

		$(document).on('click','#pb_funnel_add_current_page',function(e){

			var data = {
              action: 'pbuilder_funnel_add_page',
      				id: post_id,
      				funnel_id:$(this).data('funnel'),
      				pbso_live_editor:'true'
          }

			$('.pbuilder_funnel_content').html(pbuilder_popup_loader);
            window.pbuilder_popupajax = $.get(ajaxurl, data, function (response) {
				revisions_html=response;
				$('.pbuilder_funnel_content').html(revisions_html);

				$("#funnel_pages").sortable({
					stop: function( event, ui ) {
						$('#pb_funnel_update').show();
            window.pbuilder_changes_made = true;
					},
					handle: '.pbuilder_funnel_page_drag'
				});
			});
		});


		$('#pbuilder_body').on('click','.pbuilder_cancel_revision',function (e) {
			pbuilder_load_revision(current_revision_id);
			if ($(this).closest('#pbuilder_add_shortcode_popup').length > 0 || $(this).closest('#pbuilder_editor_popup').length > 0) {
                $('.pbuilder_popup, #pbuilder_editor_popup, .pbuilder_popup_shadow, #pbuilder_editor_popup_shadow').hide();
            }
            else {
                $(this).closest('.pbuilder_popup').remove();
                $('.pbuilder_popup_shadow').remove();
            }
		});


		$('#pbuilder_body').on('click','.pbuilder_load_revision',function (e) {
            e.preventDefault();

			$('.pbuilder_load_revision').removeClass('pbuilder_revision_active');
			$(this).addClass('pbuilder_revision_active');

            var revision_id=this.id.replace('pbuilder_revision_','');

			if(current_revision_id != revision_id){
				$('#pbuilder_apply_revision').show();
			} else {
				$('#pbuilder_apply_revision').hide();
			}

			pbuilder_load_revision(revision_id);
        });


		function pbuilder_load_revision(revision_id){
			var data = {
                action: 'pbuilder_load_revision',
				page_id: revision_id
            }



			$.get(ajaxurl, data, function (response) {
				        response = response.split('|+break+response+|');
                var loadJson = JSON.parse(response[0].replace(/\\(.)/mg, "$1"));
                var loadHtml = response[1];
                pbuilder_items = loadJson;

                $jq('#pbuilder_wrapper').replaceWith(loadHtml);
                pbuilderFrameControls($jq(iDocument));
                pbuilderSortableInit($jq('#pbuilder_content .pbuilder_row'));
                pbuilderRefreshDragg($jq);
                $jq('.pbuilder_module').trigger('refresh');
                if ($('.pbuilder_shortcode_menu').length > 0) {
                    $('.pbuilder_shortcode_menu').remove();
                    $('#pbuilder_body').css({borderRightWidth: 0});
                }
                var add_shortcode_popupHTML = '<div id="pbuilder_add_shortcode_popup" class="pbuilder_popup">';
                for (var x in pbuilder_main_menu) {
                    pbuilder_main_menu[x]['type'] = 'shortcode-popup';
                    var newControl = new pbuilderControl(x, pbuilder_main_menu[x]);
                    add_shortcode_popupHTML += newControl.html();
                }
                add_shortcode_popupHTML += '<a href="#" class="pbuilder_gradient pbuilder_button pbuilder_popup_close right">Close</a></div>';
                $jq('#pbuilder_wrapper').trigger('refresh');
                
                pbuilder_shortcode_sw = false;
                $('.pbuilder_popup_load, .pbuilder_popup_shadow').remove();
            });
		}


        $('.pbuilder_load').click(function (e) {
            pbuilder_load_f(e);
        });

        $jq(iDocument).on('click', '.pbuilder_load', function (e) {
          pbuilder_load_f(e);
        });

        $jq(iDocument).on('click', '.pbuilder_build_new_page', function (e) {
          $jq(iDocument).find('#pbuilder_empty_buttons').hide();
          $jq(iDocument).find('.pbuilder_row_holder').show();
        });


        function pbuilder_load_f(e){
          e.preventDefault();
             pbuilder_close_shortcode_menu();

          var html = '<div class="pbuilder_popup pbuilder_popup_load pbuilder_controls_wrapper"><div class="pbuilder_module_controls pbuilder_gradient"><span class="pbuilder_module_name">Load</span> <a href="#" class="pbuilder_close" title="close"></a></div>';
          html += '<div class="pbuilder_popup_content"><img class="pbuilder_popup_loader" src="' + pbuilder_url + 'images/popup-loader.gif" /></div>';
          html += '</div><div class="pbuilder_popup_shadow"></div>';
          $('#pbuilder_body').prepend(html);
          var data = {
              action: 'pbuilder_pages',
          }
          window.pbuilder_popupajax = $.get(ajaxurl, data, function (response) {
              response = JSON.parse(response);
              var rHtml = '';
              rHtml += '<div class="pbuilder_popup_tabs"><ul><li><a href="#templates_popup_tab_content">Load template</a></li><li><a href="#pages_popup_tab_content">Load page</a></li></ul>';

              rHtml += '<div id="templates_popup_tab_content">';
              if (!$.isEmptyObject(response['templates'])) {
                rHtml += '<table><tr><td>Select the template you want to load';
                rHtml += '</td><td style="padding-top: 18px;">';
                shJson = {
                    type: 'select',
                    label: '',
                    label_width: 0,
                    control_width: 1,
                    options: [],
                    search: 'true'
                }

                  for (var x in response['templates']) {
                      shJson['options'][x] = response['templates'][x]['name'];
                  }
                  select = new pbuilderControl('loaded_templates', shJson);
                  rHtml += select.html();

                  rHtml += '</td></tr></table>';

                  rHtml+='<div class="pb_templates_list_wrapper">';
                  var template_i=0;
                  for (var x in response['templates']) {
                      if(response['templates'][x]['thumb'].length>0){
                        rHtml+='<div class="pb_templates_template" data-template="'+x+'" data-name="'+response['templates'][x]['name']+'">';
                        rHtml+='<div class="pb_templates_template_thumb"><img src="'+response['templates'][x]['thumb']+'" /></div>';
                        rHtml+='<div class="pb_templates_template_thumb">'+response['templates'][x]['name']+'</div>';
                        rHtml+='</div>';
                        template_i++;
                      }
                      if(template_i%4==0){
                        rHtml+='<div style="clear:both; margin-bottom:10px;"></div>';
                      }
                    }
                    rHtml+='<div style="clear:both; margin-bottom:10px;"></div>';
                  rHtml += '</div></div>';
              }
              else {
                  rHtml += '<p>You don\'t have any templates yet.</p>';
              }
              rHtml += '<div id="pages_popup_tab_content"><table><tr><td><p>';
              rHtml += 'Select the post you want to load';
              rHtml += '</p></td><td style="padding-top: 18px;">';
              var shJson = {
                  type: 'select',
                  label: '',
                  label_width: 0,
                  control_width: 1,
                  options: [],
                  search: 'true'
              }
              for (var x in response['pages']) {
                  shJson['options'][x] = response['pages'][x]['title'];
              }
              select = new pbuilderControl('loaded_pages', shJson);
              rHtml += select.html();
              rHtml += '</td></tr></table></div>';
              rHtml += '<a href="#" class="pbuilder_gradient pbuilder_button pbuilder_popup_close right">Close</a><img class="pbuilder_popup_button_loader right" alt="" src="' + pbuilder_url + 'images/save-loader.gif"></img><a href="#" class="pbuilder_gradient pbuilder_button pbuilder_popup_load right">Load</a>';
              $('.pbuilder_popup_content').html(rHtml);



              $(".pbuilder_popup_tabs > ul a:first").addClass("active");
              $(".pbuilder_popup_tabs > div").hide();
              $(".pbuilder_popup_tabs > div:first").show();
              $(".pbuilder_popup_tabs > ul a").click(function (e) {
                  e.preventDefault();
                  if (!$(this).hasClass('active')) {
                      $(this).closest('ul').find('a').removeClass("active");
                      $(this).addClass('active');
                      var tabId = $(this).attr('href');
                      $(this).closest('.pbuilder_popup_tabs').children('div').stop(true, true).hide();
                      $(tabId).fadeIn();

                      if (!$('.pb_templates_list_wrapper').hasClass('fmCustomScrollbar')) {
                        $('.pb_templates_list_wrapper').fmCustomScrollbar();
                      }
                  }
              });
              $(".tabs").each(function () {
                  $(this).find("a:first").trigger("click");

              });
              pbuilderRefreshControls($jq, $('.pbuilder_popup_content'));
              if (!$('.pb_templates_list_wrapper').hasClass('fmCustomScrollbar')) {
                $('.pb_templates_list_wrapper').fmCustomScrollbar();
              }
          });
        }

        $(document).on('click','.pb_templates_template',function(e){
          $('#pbuilder_select_loaded_templates').val($(this).data('template'));
          $('#templates_popup_tab_content').find('span').html($(this).data('name'));
          $('.pb_templates_template').removeClass('pb_templates_template_selected');
          $(this).addClass('pb_templates_template_selected');
        });

        $('.pbuilder_exporthtml').click(function (e) {
            e.preventDefault();
			      pbuilder_close_shortcode_menu();
            var html = '<div class="pbuilder_popup pbuilder_popup_load pbuilder_popup_load_html pbuilder_controls_wrapper"><div class="pbuilder_module_controls pbuilder_gradient"><span class="pbuilder_module_name">Load</span> <a href="#" class="pbuilder_close" title="close"></a></div>';
            html += '<div class="pbuilder_popup_content"><table><tr><td><p>Are you sure you want to export ' + the_title + ' as html?</p></td><td> <a href="#" class="pbuilder_gradient pbuilder_button pbuilder_popup_close right">Close</a><img class="pbuilder_popup_button_loader right" alt="" src="' + pbuilder_url + 'images/save-loader.gif"></img><a href="#" class="pbuilder_gradient pbuilder_button pbuilder_popup_export_html right">Export HTML</a></td></td></table></div>';
            html += '</div><div class="pbuilder_popup_shadow"></div>';
            $('#pbuilder_body').prepend(html);
//            var data = {
//                action: 'pbuilder_pages',
//            }
//            window.pbuilder_popupajax = $.get(ajaxurl, data, function (response) {
//                response = JSON.parse(response);
//                var rHtml = '';
//                rHtml += '<div class="pbuilder_popup_tabs">';
//                rHtml += '<div id="pages_popup_tab_content"><table><tr><td><p>';
//                rHtml += 'Select the post you want to export';
//                rHtml += '</p></td><td>';
//                var shJson = {
//                    type: 'select',
//                    label: '',
//                    label_width: 0,
//                    control_width: 1,
//                    options: [],
//                    search: 'true'
//                }
//                for (var x in response['pages']) {
//                    shJson['options'][x] = response['pages'][x]['title'];
//                }
//                select = new pbuilderControl('loaded_pages', shJson);
//                rHtml += select.html();
//                rHtml += '</td></tr></table></div>';
//                rHtml += '<div id="templates_popup_tab_content">';
//                if (!$.isEmptyObject(response['templates'])) {
//                    rHtml += '<table><tr><td><p>Select the template you want to export';
//                    rHtml += '</p></td><td>';
//                    shJson = {
//                        type: 'select',
//                        label: '',
//                        label_width: 0,
//                        control_width: 1,
//                        options: [],
//                        search: 'true'
//                    }
//                    for (var x in response['templates']) {
//                        shJson['options'][x] = response['templates'][x];
//                    }
//                    select = new pbuilderControl('loaded_templates', shJson);
//                    rHtml += select.html();
//                    rHtml += '</td></tr></table></div>';
//                }
//                else {
//                    rHtml += '<p>You don\'t have any templates yet.</p>';
//                }
//                rHtml += '</div><a href="#" class="pbuilder_gradient pbuilder_button pbuilder_popup_close right">Close</a><img class="pbuilder_popup_button_loader right" alt="" src="' + pbuilder_url + 'images/save-loader.gif"></img><a href="#" class="pbuilder_gradient pbuilder_button pbuilder_popup_export_html right">Export HTML</a>';
//
//                $('.pbuilder_popup_content').html(rHtml);
//                $(".pbuilder_popup_tabs > ul a:first").addClass("active");
//                $(".pbuilder_popup_tabs > div").hide();
//                $(".pbuilder_popup_tabs > div:first").show();
//                $(".pbuilder_popup_tabs > ul a").click(function (e) {
//                    e.preventDefault();
//                    if (!$(this).hasClass('active')) {
//                        $(this).closest('ul').find('a').removeClass("active");
//                        $(this).addClass('active');
//                        var tabId = $(this).attr('href');
//                        $(this).closest('.pbuilder_popup_tabs').children('div').stop(true, true).hide();
//                        $(tabId).fadeIn();
//                    }
//                });
//                $(".tabs").each(function () {
//                    $(this).find("a:first").trigger("click");
//                });
//                pbuilderRefreshControls($jq, $('.pbuilder_popup_content'));
//            });
        });
        $('.pbuilder_export').click(function (e) {
            e.preventDefault();
			pbuilder_close_shortcode_menu();
            var html = '<div class="pbuilder_popup pbuilder_popup_load pbuilder_controls_wrapper" style="height: 110px;"><div class="pbuilder_module_controls pbuilder_gradient"><span class="pbuilder_module_name">Load</span> <a href="#" class="pbuilder_close" title="close"></a></div>';
            html += '<div class="pbuilder_popup_content"><img class="pbuilder_popup_loader" style="margin-top:50px" src="' + pbuilder_url + 'images/popup-loader.gif" /></div>';
            html += '</div><div class="pbuilder_popup_shadow"></div>';
            $('#pbuilder_body').prepend(html);
            var data = {
                action: 'pbuilder_export',
            }
            window.pbuilder_popupajax = $.get(ajaxurl, data, function (response) {
                response = JSON.parse(response);
                var rHtml = '';
                //rHtml += '<div class="pbuilder_popup_tabs"><ul><li><a href="#pages_popup_tab_content">Load page</a></li><li><a href="#templates_popup_tab_content">Load template</a></li></ul>';
                rHtml += '<div id="templates_popup_tab_content">\
                <input type="file" name="file_to_export" id="file_to_export" style="display:none;" accept="application/java" />';
                if (!$.isEmptyObject(response['templates'])) {
                    rHtml += '<table><tr><td><p>Select the template you want to export';
                    rHtml += '</p></td><td>';
                    shJson = {
                        type: 'select',
                        label: '',
                        label_width: 0,
                        control_width: 1,
                        options: [],
                        search: 'true'
                    }
                    for (var x in response['templates']) {
                        shJson['options'][x] = response['templates'][x];
                    }
                    select = new pbuilderControl('loaded_templates', shJson);
                    rHtml += select.html();
                    rHtml += '</td></tr></table></div>';
                }
                else {
                    rHtml += '<p>You don\'t have any templates yet.</p>';
                }
                rHtml += '</div>\
                    <a href="#" class="pbuilder_gradient pbuilder_button pbuilder_popup_close right">Close</a>\
                    <img class="pbuilder_popup_button_loader right" alt="" src="' + pbuilder_url + 'images/save-loader.gif"></img>\
                    <a href="#" class="pbuilder_gradient pbuilder_button pbuilder_popup_export right">Export</a>';
                $('.pbuilder_popup_content').html(rHtml);
                pbuilderRefreshControls($jq, $('.pbuilder_popup_content'));
            });
        });

        $('.pbuilder_import').click(function (e) {
            e.preventDefault();
			pbuilder_close_shortcode_menu()

            var html = '\
                <div class="pbuilder_popup pbuilder_popup_load pbuilder_controls_wrapper" style="width: 570px; height: 320px;">\
                    <a href="#" class="pbuilder_gradient pbuilder_button pbuilder_popup_close right" style="font-size: 10px;line-height: 10px;margin: -5px -3px 0 0;padding: 2px;">X</a>\
                    <div class="pbuilder_popup_content">\
                        <div id="templates_popup_tab_content">\
                            <div id="uploader" style="margin-top: 10px;">\
                                <p>Your browser doesn\'t have Flash, Silverlight or HTML5 support.</p>\
                            </div>\
                        </div>\
                    </div>\
                </div>\
                <div class="pbuilder_popup_shadow"></div>';
            $('#pbuilder_body').prepend(html);
            $("#uploader", '.pbuilder_popup_content').plupload({
                runtimes: 'html5,flash,silverlight,html4',
                url: ajaxurl,
                max_file_size: '32mb',
                multipart_params: {
                    action: 'pbuilder_import',
                },
                filters: [
                    {title: "Zip files", extensions: "zip"}
                ],
                rename: true,
                sortable: true,
                dragdrop: true,
                views: {
                    list: true,
                    active: 'list'
                },
                flash_swf_url: pbuilder_url + 'js/plupload/Moxie.swf',
                silverlight_xap_url: pbuilder_url + 'js/plupload/Moxie.xap',
                preinit: {
                    UploadFile: function (up, file) {
                        
                    }
                },
                init: {
                    BeforeUpload: function (up, file) {
                        
                    },
                }
            });
            pbuilderRefreshControls($jq, $('.pbuilder_popup_content'));
        });
        $(document).on('click', '.pbuilder_popup .pbuilder_close, .pbuilder_popup_close', function (e) {
            if ($(this).closest('#pbuilder_add_shortcode_popup').length > 0 || $(this).closest('#pbuilder_editor_popup').length > 0) {
                $('.pbuilder_popup, #pbuilder_editor_popup, .pbuilder_popup_shadow, #pbuilder_editor_popup_shadow').hide();
            }
            else {
                $(this).closest('.pbuilder_popup').remove();
                $('.pbuilder_popup_shadow').remove();
            }
        });
        $(document).on('click', '.pbuilder_popup .pbuilder_popup_load', function (e) {
            e.preventDefault();
            $(this).animate({paddingRight: 30, marginRight: -10}, 200).prev('.pbuilder_popup_button_loader').animate({opacity: 1, marginRight: 10}, 200);
            var $popC = $(this).closest('.pbuilder_popup_content');
            var loadIndex = $popC.find('.pbuilder_control:visible #pbuilder_select_loaded_pages, .pbuilder_control:visible #pbuilder_select_loaded_templates').val();
            var data = {
                action: 'pbuilder_page_content',
                id: loadIndex,
				post_id:post_id
            }
            $.get(ajaxurl, data, function (response) {
                response = response.split('|+break+response+|');
                var loadJson = JSON.parse(response[0].replace(/\\(.)/mg, "$1"));
                var loadHtml = response[1];
                pbuilder_items = loadJson;

                $jq('#pbuilder_wrapper').replaceWith(loadHtml);
                pbuilderFrameControls($jq(iDocument));
                pbuilderSortableInit($jq('#pbuilder_content .pbuilder_row'));
                pbuilderRefreshDragg($jq);
                $jq('.pbuilder_module').trigger('refresh');
                if ($('.pbuilder_shortcode_menu').length > 0) {
                    $('.pbuilder_shortcode_menu').remove();
                    $('#pbuilder_body').css({borderRightWidth: 0});
                }
                var add_shortcode_popupHTML = '<div id="pbuilder_add_shortcode_popup" class="pbuilder_popup">';
                for (var x in pbuilder_main_menu) {
                    pbuilder_main_menu[x]['type'] = 'shortcode-popup';
                    var newControl = new pbuilderControl(x, pbuilder_main_menu[x]);
                    add_shortcode_popupHTML += newControl.html();
                }
                add_shortcode_popupHTML += '<a href="#" class="pbuilder_gradient pbuilder_button pbuilder_popup_close right">Close</a></div>';
                $jq('#pbuilder_wrapper').trigger('refresh');
                
                pbuilder_shortcode_sw = false;
                $('.pbuilder_popup_load, .pbuilder_popup_shadow').remove();
				window.location.reload();
            });
        });
        $(document).on('click', '.pbuilder_popup .pbuilder_popup_export_html', function (e) {
            e.preventDefault();
            $(this).animate({paddingRight: 30, marginRight: -10}, 200).prev('.pbuilder_popup_button_loader').animate({opacity: 1, marginRight: 10}, 200);
            var $popC = $(this).closest('.pbuilder_popup_content');
            var loadIndex = $popC.find('.pbuilder_control:visible #pbuilder_select_loaded_pages, .pbuilder_control:visible #pbuilder_select_loaded_templates').val();
            var data = {
                action: 'pbuilder_export_html',
                id: post_id
            }
            $.get(ajaxurl, data, function (response) {
                var json_data = JSON.parse(response);
                if (json_data.result == "success") {
                    var iWindow = $('#pbuilder_body_frame')[0].contentWindow;
                    $(iWindow).unbind('beforeunload');
                    window.location.href = json_data.fileurl;
                    $(iWindow).on('beforeunload', function (e) {
                        var message = 'Are you sure you want to leave the page? Any unsaved data will be lost.';
                        e.returnValue = message;
                        return message;
                    });
                    //window.open(json_data.fileurl);
                }
                $('.pbuilder_popup_load, .pbuilder_popup_shadow').remove();
            });
        });
        $(document).on('click', '.pbuilder_popup .pbuilder_popup_export', function (e) {
            e.preventDefault();
            $(this).animate({paddingRight: 30, marginRight: -10}, 200).prev('.pbuilder_popup_button_loader').animate({opacity: 1, marginRight: 10}, 200);
            var $popC = $(this).closest('.pbuilder_popup_content');
            var loadIndex = $popC.find('.pbuilder_control:visible #pbuilder_select_loaded_templates').val();
            var data = {
                action: 'pbuilder_export_template', //pbuilder_page_content
                id: loadIndex
            }
            $.get(ajaxurl, data, function (response) {
                var json_data = JSON.parse(response);
                if (json_data.result == "success") {
                    var iWindow = $('#pbuilder_body_frame')[0].contentWindow;
                    $(iWindow).unbind('beforeunload');
                    window.location.href = json_data.fileurl;
                    $(iWindow).on('beforeunload', function (e) {
                        var message = 'Are you sure you want to leave the page? Any unsaved data will be lost.';
                        e.returnValue = message;
                        return message;
                    });
                    //window.open(json_data.fileurl);
                }
                $('.pbuilder_popup_load, .pbuilder_popup_shadow').remove();
            });
        });
        $(document).on('click', '.pbuilder_popup .pbuilder_popup_save', function (e) {
            e.preventDefault();
            $(this).animate({paddingRight: 30, marginRight: -10}, 200).prev('.pbuilder_popup_button_loader').animate({opacity: 1, marginRight: 10}, 200);
            var $popC = $(this).closest('.pbuilder_popup_content');
            var tmplName = $popC.find('#pbuilder_input_template_name').val();

			var itemsString = JSON.stringify(pbuilder_items, jsonMod);
			itemsString=itemsString.replace(/null/g, '@@@');
            var data = {
                action: 'pbuilder_template_save',
                name: tmplName,
				post_id: post_id,
                items: itemsString
            }
            $.post(ajaxurl, data, function (response) {
                $('.pbuilder_popup, .pbuilder_popup_shadow').remove();
            });
        });
        /* Add new row button */
        $jq(iDocument).on('click', '.pbuilder_new_row', function (e) {
            e.preventDefault();
            var $holder = $jq(this).parent();
            var buttonHeight = $holder.children('.pbuilder_new_row').height() + parseInt($holder.children('.pbuilder_new_row').css('padding-top')) + parseInt($holder.children('.pbuilder_new_row').css('padding-bottom'));
            var innerHeight = $holder.children('.pbuilder_row_holder_inner').height() + parseInt($holder.children('.pbuilder_row_holder_inner').css('padding-top')) + parseInt($holder.children('.pbuilder_row_holder_inner').css('padding-bottom'));
            if (!$jq(this).hasClass('active')) {
                $jq(this).addClass('active pbuilder_gradient_primary').removeClass('pbuilder_gradient');
                $holder.stop(true).animate({height: (buttonHeight + innerHeight + 2) + 'px'}, 300);
            }
            else {
                $jq(this).removeClass('active').addClass('pbuilder_gradient').removeClass('pbuilder_gradient_primary');
                $holder.stop(true).animate({height: (buttonHeight + 2) + 'px'}, 300, function () {
                    $jq(this).trigger('refresh');
                });
            }
        });

		$jq(iDocument).on('click', '.pbuilder_new_row_button', function (e) {
            e.preventDefault();
      			$jq('div').remove( ".pbuilder_new_row_inline" );
      		  $jq(this).parent().append('<div class="pbuilder_new_row_inline">Select columns: <br />'+rows_columns+'</div>');
    });

    /* Row button click */
    $jq(iDocument).on('click', '.pbuilder_row_button', function (e) {
            e.preventDefault();
            window.pbuilder_changes_made = true;
      			var insert_after = false;
      			if( typeof $jq(this).parent().parent().parent().data('rowid') !== 'undefined' ){
      				var insert_after=$jq(this).parent().parent().parent().data('rowid');
      			}

            var value = parseInt($jq(this).attr('href').substr(1));
            var html = pbuilder_rows[value]['html'];
            var id = 0;
            while ($jq('#pbuilder_content .pbuilder_row[data-rowid=' + id + ']').length > 0)
                id++;
            html = html.replace('%1$s', id + '');
            var rowInterface = '<div class="pbuilder_row_controls pbuilder_gradient"><a href="#" class="pbuilder_edit" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a><a href="#" class="pbuilder_drag_handle" title="Drag"><i class="fa fa-arrows" aria-hidden="true"></i></a><a href="#" class="pbuilder_clone" title="Clone"><i class="fa fa-clone" aria-hidden="true"></i></a><a class="pbuilder_copy" href="#" title="Copy"><i class="fa fa-files-o" aria-hidden="true"></i></a><a class="pbuilder_paste" href="#" title="Paste"><i class="fa fa-clipboard" aria-hidden="true"></i></a><a href="#" class="pbuilder_delete" title="Delete Element"><i class="fa fa-trash" aria-hidden="true"></i></a><a class="pbuilder_new_row_button" href="#" title="Add new row"><i class="fa fa-plus-square" aria-hidden="true"></i></a></div>';
            html = html.replace('%2$s', rowInterface);
            var columnInterface = '<div class="pbuilder_column_controls pbuilder_gradient"><a href="#" class="pbuilder_edit" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a></div><div class="pbuilder_column_inner pbuilder_droppable empty"></div><div class="pbuilder_drop_borders"><div class="pbuilder_empty_content"><div class="pbuilder_add_shortcode pbuilder_gradient">+</div><span>Add Shortcode</span></div></div>';
            html = html.replace(/%[0-9]+\$s/g, columnInterface);
            if (typeof pbuilder_items.rows == 'undefined') {
                pbuilder_items.rows = new Array();
                pbuilder_items.rowCount = 0;
                pbuilder_items.rowOrder = new Array();
                pbuilder_items.items = new Array();
				        pbuilder_items.columns = {};
            }

      			if (typeof pbuilder_items.columns == 'undefined') {
      				pbuilder_items.columns={};
      			}

      			if(typeof pbuilder_items.columns[id] == 'undefined'){
      				pbuilder_items.columns[id]={};
      			}

            var columns = new Array();
            var count = html.match(/pbuilder_column /g);
            for (var x = 0; x < count.length; x++) {
            columns[x] = new Array();

              if(typeof pbuilder_items.columns[id][x] == 'undefined'){
      					pbuilder_items.columns[id][x]={};
                pbuilder_items.columns[id][x].options={};
      				}
            }

			     pbuilder_items['rows'][id] = {type: value, columns: columns};

			   

            pbuilder_items['rows'][id] = {type: value, columns: columns};
            if ($jq('#pbuilder_wrapper').hasClass('empty')) {
                $jq('#pbuilder_wrapper').removeClass('empty');
            }

      			$jq('div').remove( ".pbuilder_new_row_inline" );
      			if(insert_after !== false){
      				$jq('*[data-rowid="'+insert_after+'"]').after(html);
      			} else {
      				$jq('.pbuilder_new_row').trigger('click');
              $jq('#pbuilder_content').append(html);
      			}

            checkHTML();
            pbuilder_items['rowOrder'] = [];
            $jq('#pbuilder_content .pbuilder_row').each(function (index) {
                pbuilder_items['rowOrder'][index] = parseInt($jq(this).attr('data-rowid'));
            });
            pbuilder_items.rowCount = $jq('#pbuilder_content .pbuilder_row').length;

      			if(insert_after !== false){
      				pbuilderSortableInit($jq('#pbuilder_content *[data-rowid="'+id+'"]'));
      				$jq('#pbuilder_content *[data-rowid="'+id+'"] .pbuilder_row_controls .pbuilder_edit').trigger('click');
      			} else {
      				pbuilderSortableInit($jq('#pbuilder_content .pbuilder_row:last'));
      				$jq('#pbuilder_content .pbuilder_row:last .pbuilder_row_controls .pbuilder_edit').trigger('click');
      			}

            pbuilderRefreshDragg($jq);
            $jq('#pbuilder_wrapper').trigger('refresh');
        });
        /* Row controls */
        $jq(iDocument).on('mouseenter', '.pbuilder_row', function (e) {
            //$jq('.pbuilder_row.selected .pbuilder_row_controls:first').hide();
            $jq(this).find('.pbuilder_row_controls:first').addClass('visible');

        });

        $jq(iDocument).on('mouseleave', '.pbuilder_row', function (e) {
            $jq(this).find('.pbuilder_row_controls:first').removeClass('visible');
            //$jq('.pbuilder_row.selected .pbuilder_row_controls:first').show();
        });


		$jq(iDocument).on('mouseenter', '.pbuilder_column', function (e) {
            //$jq('.pbuilder_row.selected .pbuilder_row_controls:first').hide();
            $jq(this).find('.pbuilder_column_controls:first').addClass('visible');

        });

        $jq(iDocument).on('mouseleave', '.pbuilder_column', function (e) {
            $jq(this).find('.pbuilder_column_controls:first').removeClass('visible');
            //$jq('.pbuilder_row.selected .pbuilder_row_controls:first').show();
        });


        $jq(iDocument).on('click', '.pbuilder_row_controls .pbuilder_drag_handle', function (e) {
            e.preventDefault();

        });
        $jq(iDocument).on('click', '.pbuilder_row_controls .pbuilder_delete', function (e) {
            e.preventDefault();
            if (!confirm("Are you sure you want to delete this row?"))
                return;
            var $parent = $jq(this).closest('.pbuilder_row');
            var id = parseInt($parent.attr('data-rowid'));
            delete pbuilder_items['columns'][id];
            var found = false;
            if ($('.pbuilder_shortcode_menu').hasClass('pbuilder_rowedit_menu') && $('.pbuilder_shortcode_menu').attr('data-modid') == id) {
                $('.pbuilder_shortcode_menu_toggle').stop(true).animate({'right': -47}, 300);
                $('.pbuilder_shortcode_menu').animate({right: -300}, 300, function () {
                    $(this).remove();
                    pbuilder_shortcode_sw = false;
                });
                $('#pbuilder_body').stop(true).animate({borderRightWidth: 0}, 300);
            }
            $parent.find('.pbuilder_module .pbuilder_delete').attr('row-delete', 'true');
            $parent.find('.pbuilder_module .pbuilder_delete').trigger('click');
            $parent.remove();
            pbuilder_items['rowOrder'] = [];
            $jq('#pbuilder_content .pbuilder_row').each(function (index) {
                pbuilder_items['rowOrder'][index] = parseInt($jq(this).attr('data-rowid'));
            });

            pbuilder_items.rowCount = $jq('#pbuilder_content .pbuilder_row').length;
            $jq('#pbuilder_wrapper').trigger('refresh');
            checkHTML();
        });
        $jq(iDocument).on('click', '.pbuilder_row_controls .pbuilder_clone', function (e) {
            e.preventDefault();
            var $parent = $jq(this).closest('[data-rowid]');
            var id = parseInt($parent.attr('data-rowid'));
            var newId = 0;
            while ($jq('.pbuilder_row[data-rowid="' + newId + '"]').length > 0) {
                newId++;
            }
            var found = false;
            var i = pbuilder_items.rowCount;
            var idReplace = {};
            while (!found) {
                if (pbuilder_items['rowOrder'][i] == id) {
                    found = true;
                    pbuilder_items['rowOrder'][i + 1] = newId;
                    pbuilder_items['rows'][newId] = $.extend(true, {}, pbuilder_items['rows'][id]);
                    pbuilder_items['rows'][newId]['columns'] = [];
                    var ind = 0;
                    for (var x in pbuilder_items['rows'][id]['columns']) {
                        pbuilder_items['rows'][newId]['columns'][x] = [];
                        for (var y in pbuilder_items['rows'][id]['columns'][x]) {
                            var itemId = pbuilder_items['rows'][id]['columns'][x][y];
                            if (typeof itemId != 'undefined') {
                                while (typeof pbuilder_items['items'][ind] != 'undefined') {
                                    ind++;
                                }
                                pbuilder_items['items'][ind] = {};
                                pbuilder_items['items'][ind]['f'] = pbuilder_items['items'][itemId]['f'];
                                pbuilder_items['items'][ind]['slug'] = pbuilder_items['items'][itemId]['slug'];
                                pbuilder_items['items'][ind]['options'] = $.extend(true, {}, pbuilder_items['items'][itemId]['options']);
                                pbuilder_items['rows'][newId]['columns'][x][y] = ind;
                                idReplace[itemId] = ind;
                            }
                        }
                    }
                } else {
                    pbuilder_items['rowOrder'][i] = pbuilder_items['rowOrder'][i - 1];
                }
                i--;
            }
            $parent.clone().insertAfter($parent);
            pbuilder_items['columns'][newId]=pbuilder_items['columns'][id];
            $parent.next().attr('data-rowid', newId);
            $parent.next().find('.pbuilder_module').each(function (ind) {
                $jq(this).attr('data-modid', idReplace[parseInt($jq(this).attr('data-modid'))]);
                var id = parseInt($(this).attr('data-modid'));
                var $module = $jq('.pbuilder_module[data-modid=' + id + ']:first');
                var f = pbuilder_items['items'][id]['f'];
                var holder = $module.find('.pbuilder_module_content:first');
                var options = pbuilder_items['items'][id]['options'];
                pbuilderGetShortcode(f, holder, options);

            });
            $parent.next().find('.pbuilder_gradient_primary').removeClass('pbuilder_gradient_primary');
            $parent.next().removeClass('selected');
            pbuilderSortableInit($parent.next());
            pbuilder_items.rowCount++;
        });


        $jq(iDocument).on('click', '.pbuilder_row_controls .pbuilder_copy', function (e) {
            e.preventDefault();
            var $row = $jq(this).closest('[data-rowid]');
            var rowid = parseInt($row.attr('data-rowid'));
            //var holder = $module.find('.pbuilder_module_content:first');
            var copiedoptions = {};
            copiedoptions["row"] = $.extend(true, {}, pbuilder_items['rows'][rowid]);
            copiedoptions['columns'] = [];
            copiedoptions['row_columns'] = pbuilder_items['columns'][rowid];
            var ind = 0;
            for (var x in pbuilder_items['rows'][rowid]['columns']) {
                copiedoptions['columns'][x] = [];
                for (var y in pbuilder_items['rows'][rowid]['columns'][x]) {
                    var itemId = pbuilder_items['rows'][rowid]['columns'][x][y];
                    if (typeof itemId != 'undefined') {
                        copiedoptions['columns'][x][y] = {};
                        copiedoptions['columns'][x][y]['f'] = pbuilder_items['items'][itemId]['f'];
                        copiedoptions['columns'][x][y]['slug'] = pbuilder_items['items'][itemId]['slug'];
                        copiedoptions['columns'][x][y]['options'] = $.extend(true, {}, pbuilder_items['items'][itemId]['options']);
                        copiedoptions['columns'][x][y]['itemId'] = itemId;
                        copiedoptions['columns'][x][y]['ind'] = ind;
                        ind++;
                    }
                }
            }
            var copiedtext = $('<div>').append($row.clone()).html();
            copiedtext = copiedtext.replace('data-rowid="' + rowid + '"', 'data-rowid="%rowid"');
            //holder.closest('.pbuilder_module').find('.pbuilder_module_loader').show();
            var $pbuilder_module_loader = $('<img class="pbuilder_module_loader" src="' + pbuilder_url + 'images/module-loader-new.gif" />');
            $row.append($pbuilder_module_loader);
            $pbuilder_module_loader.show();
            var data = {
                action: 'pbuilder_copy',
                copiedtype: 'row',
                copiedoptions: JSON.stringify(copiedoptions),
                copiedtext: copiedtext
            }
            if (typeof window.pbuilder_shajax[rowid] != 'undefined')
                window.pbuilder_shajax[rowid].abort();
            	window.pbuilder_shajax[rowid] = $.post(ajaxurl, data, function (response) {
                $pbuilder_module_loader.remove();
            });
        });
        $jq(iDocument).on('click', '.pbuilder_row_controls .pbuilder_paste', function (e) {
            e.preventDefault();
            var $row = $jq(this).closest('[data-rowid]');
            var rowid = parseInt($row.attr('data-rowid'));
            var $pbuilder_module_loader = $('<img id="pbuilder_module_loader_' + rowid + '" class="pbuilder_module_loader" src="' + pbuilder_url + 'images/module-loader-new.gif" />');
            $row.append($pbuilder_module_loader);
            $pbuilder_module_loader.show();
            var data = {
                action: 'pbuilder_paste',
                rowid: rowid,
                modid: 0,
                copiedtype: 'row',
            }
            if (typeof window.pbuilder_shajax[rowid] != 'undefined')
                window.pbuilder_shajax[rowid].abort();
            window.pbuilder_shajax[rowid] = $.post(ajaxurl, data, function (response) {
                var json_data = JSON.parse(response);
                var copiedoptions = JSON.parse(json_data.copiedoptions);
                var copiedtext = json_data.copiedtext;
                var $row = $jq('.pbuilder_row[data-rowid=' + json_data.rowid + ']');
                var id = parseInt($row.attr('data-rowid'));
                var rowid = parseInt(json_data.rowid);
                var newId = 0;
                while ($jq('.pbuilder_row[data-rowid="' + newId + '"]').length > 0)
                    newId++;
                var found = false;
                var i = pbuilder_items.rowCount;
                var idReplace = {};
                while (!found) {
                    if (pbuilder_items['rowOrder'][i] == id) {
                        found = true;
                        pbuilder_items['rowOrder'][i] = newId;
                        pbuilder_items['rowOrder'][i + 1] = id;
                        pbuilder_items['rows'][newId] = copiedoptions['row'];
                        pbuilder_items['rows'][newId]['columns'] = [];
                        var ind = 0;
                        for (var x in copiedoptions['columns']) {
                            pbuilder_items['rows'][newId]['columns'][x] = [];
                            for (var y in copiedoptions['columns'][x]) {
                                var moduleoptions = copiedoptions['columns'][x][y];
                                if (typeof moduleoptions != 'undefined' && moduleoptions != null) {
                                    while (typeof pbuilder_items['items'][ind] != 'undefined') {
                                        ind++;
                                    }
                                    pbuilder_items['items'][ind] = {};
                                    pbuilder_items['items'][ind]['f'] = moduleoptions['f'];
                                    pbuilder_items['items'][ind]['slug'] = moduleoptions['slug'];
                                    pbuilder_items['items'][ind]['options'] = $.extend(true, {}, moduleoptions['options']);
                                    pbuilder_items['rows'][newId]['columns'][x][y] = ind;
                                    idReplace[moduleoptions['itemId']] = ind;
                                }
                            }
                        }
                    } else {
                        pbuilder_items['rowOrder'][i] = pbuilder_items['rowOrder'][i - 1];
                    }
                    i--;
                }
                pbuilder_items['columns'][newId]=copiedoptions['row_columns'];
                copiedtext = copiedtext.replace('data-rowid="%rowid"', 'data-rowid="' + newId + '"');
                $row.before(copiedtext);
                $pbuilder_module_loader.remove();
                var $newrow = $jq('.pbuilder_row[data-rowid=' + newId + ']');
                $newrow.find('.pbuilder_module').each(function (ind) {
                    $jq(this).attr('data-modid', idReplace[parseInt($jq(this).attr('data-modid'))]);
                    var id = parseInt($(this).attr('data-modid'));
                    var $module = $jq('.pbuilder_module[data-modid=' + id + ']:first');
                    var f = pbuilder_items['items'][id]['f'];
                    var holder = $module.find('.pbuilder_module_content:first');
                    var options = pbuilder_items['items'][id]['options'];
                    pbuilderGetShortcode(f, holder, options);

                });
                $newrow.find('.pbuilder_gradient_primary').removeClass('pbuilder_gradient_primary');
                $newrow.removeClass('selected');
                pbuilderSortableInit($newrow);
                pbuilder_items.rowCount++;
            });
            /*$parent.clone().insertAfter($parent);
             $parent.next().attr('data-rowid',newId);
             */
        });


        $jq(iDocument).on('click', '.pbuilder_row_controls .pbuilder_edit', function (e) {
            e.preventDefault();
            
            $controls = $jq(this).closest('.pbuilder_row_controls');
            $row = $controls.closest('.pbuilder_row');
            var id = parseInt($row.attr('data-rowid'));
            if (pbuilder_shortcode_sw) {
                var $menu = $('.pbuilder_shortcode_menu');
                if (!$menu.hasClass('pbuilder_rowedit_menu') || parseInt($menu.attr('data-modid')) != id) {
                    $menu.addClass('pbuilder_rowedit_menu');
                    $menu.attr('data-modid', id);
					          $menu.attr('data-columnid','');
                    if (parseInt($menu.css('right')) != 0) {
                        $menu.stop(true).animate({right: 0}, 300);
                        $('#pbuilder_body').stop(true).animate({borderRightWidth: 320}, 300);
                    }
                    $jq('.pbuilder_module_controls.pbuilder_gradient_primary').removeClass('pbuilder_gradient_primary');
                    $jq('.pbuilder_row.selected, .pbuilder_module.selected').removeClass('selected');
                    $jq('.pbuilder_row.child_selected').removeClass('child_selected');
                    $jq('.pbuilder_column.selected, .pbuilder_module.selected').removeClass('selected');
                    $jq('.pbuilder_column.child_selected').removeClass('child_selected');

					$row.addClass('selected');
                    $menu.find('.pbuilder_menu_inner').stop(true).animate({opacity: 0}, 200, function () {
                        var shHtml = pbuilderCreateRowMenu(id, $row);
                        $(this).html(shHtml).animate({opacity: 1}, 300);
                        
                        pbuilderHideControls($('false'), true);
                        pbuilderRefreshControls($jq, $menu);
                        
                        $('#pbuilder_border_left_width').hide();
                        $('#pbuilder_border_left_style').hide();
                        $('#pbuilder_border_left_color').hide();
                        $('#pbuilder_border_right_width').hide();
                        $('#pbuilder_border_right_style').hide();
                        $('#pbuilder_border_right_color').hide();
                    });
                }
                
            }
            else {
                pbuilder_shortcode_sw = true;
                $row.addClass('selected');
                var html = ($('.pbuilder_shortcode_menu_toggle').length <= 0 ? '<div class="pbuilder_shortcode_menu_toggle pbuilder_gradient">Close</div>' : '') + '<div style="left:auto; right:-250px;" class="pbuilder_shortcode_menu pbuilder_rowedit_menu pbuilder_controls_wrapper" data-modid="' + id + '"><form autocomplete="off"><div class="pbuilder_menu_inner" id="pbuilder_menu_inner">';
                html += pbuilderCreateRowMenu(id, $row);
                html += '</div></form></div>';
                $('body').append(html);
                var $menu = $('.pbuilder_shortcode_menu');
                    
                pbuilderHideControls($('false'), true);
                pbuilderRefreshControls($jq, $menu);
                $menu.stop(true).animate({right: 0}, 300);
                $('.pbuilder_shortcode_menu_toggle').stop(true).animate({'right': 354}, 300);
                $('#pbuilder_body').stop(true).animate({borderRightWidth: 400}, 300);
            }
            
            

        });

		function pbuilder_rebuild_columns(){
			if (typeof pbuilder_items['columns'] == 'undefined'){
				pbuilder_items['columns']={};
			}


			for(r in pbuilder_items['rows']){
				if (typeof pbuilder_items['columns'][r] == 'undefined'){
					pbuilder_items['columns'][r]={};
				}

				for(c in pbuilder_items['rows'][r]['columns']){
					if (typeof pbuilder_items['columns'][r][c] == 'undefined'){
					 pbuilder_items['columns'][r][c] = {};
					}
					if (typeof pbuilder_items['columns'][r][c]['options'] == 'undefined'){
					  pbuilder_items['columns'][r][c]['options'] = {};
					}
				}
			}
		}

        $jq(iDocument).on('click', '.pbuilder_column_controls .pbuilder_edit', function (e) {
            e.preventDefault();
            $controls = $jq(this).closest('.pbuilder_column_controls');
            $row = $controls.closest('.pbuilder_row');
            $column = $controls.closest('.pbuilder_column');
			      var id = parseInt($column.attr('data-colnumber'));
            var rowid = parseInt($row.attr('data-rowid'));

      			if(typeof pbuilder_items['columns'] == 'undefined'){
      				pbuilder_rebuild_columns();
      			}

            if(typeof pbuilder_items['columns'][rowid] == 'undefined'){
              pbuilder_items['columns'][rowid]={};
              pbuilder_items['columns'][rowid][id]={};
              pbuilder_items['columns'][rowid][id]['options']={};
            }

      			if (pbuilder_shortcode_sw) {
                      var $menu = $('.pbuilder_shortcode_menu');
                      if (!$menu.hasClass('pbuilder_columnedit_menu') || parseInt($menu.attr('data-modid')) != rowid  || parseInt($menu.attr('data-columnid')) != id) {
      					          $menu.addClass('pbuilder_columnedit_menu');
                          $menu.removeClass('pbuilder_rowedit_menu');
                          $menu.attr('data-columnid', id);
                          $menu.attr('data-modid', rowid);
                          if (parseInt($menu.css('right')) != 0) {
                              $menu.stop(true).animate({right: 0}, 300);
                              $('#pbuilder_body').stop(true).animate({borderRightWidth: 320}, 300);
                          }
                          $jq('.pbuilder_module_controls.pbuilder_gradient_primary').removeClass('pbuilder_gradient_primary');
                          $jq('.pbuilder_column.selected, .pbuilder_module.selected').removeClass('selected');
                          $jq('.pbuilder_column.child_selected').removeClass('child_selected');

      					$jq('.pbuilder_row.selected, .pbuilder_module.selected').removeClass('selected');
                    $jq('.pbuilder_row.child_selected').removeClass('child_selected');
                    $column.addClass('selected');
                    $menu.find('.pbuilder_menu_inner').stop(true).animate({opacity: 0}, 200, function () {
                        var shHtml = pbuilderCreateColumnMenu(rowid, id, $column);
                        $(this).html(shHtml).animate({opacity: 1}, 300);
                        pbuilderHideControls($('false'), true);
                        pbuilderRefreshControls($jq, $menu);

                    });
                }
            }
            else {
                pbuilder_shortcode_sw = true;
                $column.addClass('selected');
                var html = ($('.pbuilder_shortcode_menu_toggle').length <= 0 ? '<div class="pbuilder_shortcode_menu_toggle pbuilder_gradient">Close</div>' : '') + '<div style="left:auto; right:-250px;" class="pbuilder_shortcode_menu pbuilder_columnedit_menu pbuilder_controls_wrapper" data-columnid="' + id + '" data-modid="' + rowid + '"><form autocomplete="off"><div class="pbuilder_menu_inner" id="pbuilder_menu_inner">';
                html += pbuilderCreateColumnMenu(rowid, id, $column);
                html += '</div></form></div>';
                $('body').append(html);
                var $menu = $('.pbuilder_shortcode_menu');
                pbuilderHideControls($('false'), true);
                pbuilderRefreshControls($jq, $menu);
                $menu.stop(true).animate({right: 0}, 300);
                $('.pbuilder_shortcode_menu_toggle').stop(true).animate({'right': 354}, 300);
                $('#pbuilder_body').stop(true).animate({borderRightWidth: 400}, 300);
            }

        });



        /* Module controls */
        var edit_menu_show = function (shortcode) {
            if (shortcode == "optin") {
                var customfields = Object.keys(pbuilder_items['items'][id]['options']).filter(function (key) {
                    return (key.indexOf("customfield") > -1 && key.indexOf("customfieldlabel") <= -1 && key.indexOf("customfieldtype") <= -1 && key.indexOf("customfieldrequired") <= -1 && key.indexOf("customfielderror") <= -1 && key != "addcustomfield" && key != "customfieldsdiv" && key != "customfields");
                })
                if (customfields.length > 0) {
                    for (var index in customfields) {
                        var customfield = customfields[index];
                        var ind = customfield.replace("customfield", "");
                        var stdlabel = pbuilder_items['items'][id]['options']["customfieldlabel" + ind];
                        var stdtext = pbuilder_items['items'][id]['options'][customfield];
                        var fieldtype = pbuilder_items['items'][id]['options']["customfieldtype" + ind];
                        pbuilderAddCustomField(ind, stdlabel, stdtext, fieldtype);
                    }
                }
            }
            
        }
        var pass_mod = null;
        var moduleDeleteFlag = false;
        $jq(iDocument).on('dblclick', '.pbuilder_module', function (e) {
          localStorage.setItem("counter", 1);

          e.preventDefault();
          $controls = $jq(this).find('.pbuilder_module_controls');
          $module = $jq(this);
          $row = $module.closest('.pbuilder_row');
          pass_mod = $module;
          $this = $jq(this);
          var id = parseInt($module.attr('data-modid'));
          var shortcode = $module.attr('data-shortcode');
          if (pbuilder_shortcode_sw) {
              var $menu = $('.pbuilder_shortcode_menu');

              if ($menu.hasClass('pbuilder_rowedit_menu') || $menu.hasClass('pbuilder_columnedit_menu') || parseInt($menu.attr('data-modid')) != id) {
                  $menu.removeClass('pbuilder_rowedit_menu');
                  $menu.removeClass('pbuilder_columnedit_menu');

                  $jq('.pbuilder_row.selected, .pbuilder_module.selected').removeClass('selected');
                  $jq('.pbuilder_row.child_selected').removeClass('child_selected');
                  $row.addClass('child_selected');
                  $menu.attr('data-modid', id).attr('data-shortcode', shortcode);
                  if ($menu.css('right') != '0px') {
                      $menu.stop(true).animate({right: 0}, 300);
                      $('#pbuilder_body').stop(true).animate({borderRightWidth: 320}, 300);
                  }
                  $jq('.pbuilder_module_controls.pbuilder_gradient_primary').removeClass('pbuilder_gradient_primary');
                  $controls.addClass('pbuilder_gradient_primary');
                  $module.addClass('selected');
                  $menu.find('.pbuilder_menu_inner').stop(true).animate({opacity: 0}, 200, function () {
                      var shHtml = pbuilderCreateShortcodeMenu(id, $module);
                      $(this).html(shHtml).animate({opacity: 1}, 300);
                      
            
                      if (shortcode == "optin") {
                          var customfields = Object.keys(pbuilder_items['items'][id]['options']).filter(function (key) {
                              return (key.indexOf("customfield") > -1 && key.indexOf("customfieldlabel") <= -1 && key.indexOf("customfieldtype") <= -1 && key.indexOf("customfieldrequired") <= -1 && key.indexOf("customfielderror") <= -1 && key != "addcustomfield" && key != "customfieldsdiv" && key != "customfields");
                          })
                          if (customfields.length > 0) {
                              for (var index in customfields) {
                                  var customfield = customfields[index];
                                  var ind = customfield.replace("customfield", "");
                                  var stdlabel = pbuilder_items['items'][id]['options']["customfieldlabel" + ind];
                                  var stdtext = pbuilder_items['items'][id]['options'][customfield];
                                  var fieldtype = pbuilder_items['items'][id]['options']["customfieldtype" + ind];
                                  pbuilderAddCustomField(ind, stdlabel, stdtext, fieldtype);
                              }
                          }
                          var hiddenfields = Object.keys(pbuilder_items['items'][id]['options']).filter(function (key) {
                              return (key.indexOf("hiddenfield") > -1 && key.indexOf("hiddenfieldname") <= -1 && key.indexOf("hiddenfieldtype") <= -1 && key != "addhiddenfield" && key != "hiddenfieldsdiv" && key != "hiddenfields");
                          })
                          if (hiddenfields.length > 0) {
                              for (var index in hiddenfields) {
                                  var hiddenfield = hiddenfields[index];
                                  var ind = hiddenfield.replace("hiddenfield", "");
                                  var stdname = pbuilder_items['items'][id]['options']["hiddenfieldname" + ind];
                                  var stdvalue = pbuilder_items['items'][id]['options'][hiddenfield];
                                  var fieldtype = pbuilder_items['items'][id]['options']["hiddenfieldtype" + ind];
                                  pbuilderAddHiddenField(ind, stdname, stdvalue, fieldtype);
                              }
                          }
                      }
                      pbuilderHideControls($('false'), true);
                      pbuilderRefreshControls($jq, $menu);
                      $this.trigger("after_menu_created", [$jq, iDocument, $menu]);

                  });
              }
          } else {
              pbuilder_shortcode_sw = true;
              $row.addClass('child_selected');
              $module.addClass('selected');
              $controls.addClass('pbuilder_gradient_primary');
              var html = ($('.pbuilder_shortcode_menu_toggle').length <= 0 ? '<div class="pbuilder_shortcode_menu_toggle pbuilder_gradient">Close</div>' : '') + '<div style="left:auto; right:-250px;" class="pbuilder_shortcode_menu pbuilder_controls_wrapper" data-modid="' + id + '" data-shortcode="' + shortcode + '"><form autocomplete="off"><div id="pbuilder_menu_inner" class="pbuilder_menu_inner">';
              html += pbuilderCreateShortcodeMenu(id, $module);
              html += '</div></form></div>';
              //$('#customfieldsdiv', html).html('');
              $('body').append(html);
              
              if (shortcode == "optin") {
                  var customfields = Object.keys(pbuilder_items['items'][id]['options']).filter(function (key) {
                      return (key.indexOf("customfield") > -1 && key.indexOf("customfieldlabel") <= -1 && key.indexOf("customfieldtype") <= -1 && key.indexOf("customfieldrequired") <= -1 && key.indexOf("customfielderror") <= -1 && key != "addcustomfield" && key != "customfieldsdiv" && key != "customfields");
                  })
                  if (customfields.length > 0) {
                      for (var index in customfields) {
                          var customfield = customfields[index];
                          var ind = customfield.replace("customfield", "");
                          var stdlabel = pbuilder_items['items'][id]['options']["customfieldlabel" + ind];
                          var stdtext = pbuilder_items['items'][id]['options'][customfield];
                          var fieldtype = pbuilder_items['items'][id]['options']["customfieldtype" + ind];
                          pbuilderAddCustomField(ind, stdlabel, stdtext, fieldtype);
                      }
                  }
                  var hiddenfields = Object.keys(pbuilder_items['items'][id]['options']).filter(function (key) {
                      return (key.indexOf("hiddenfield") > -1 && key.indexOf("hiddenfieldname") <= -1 && key.indexOf("hiddenfieldtype") <= -1 && key != "addhiddenfield" && key != "hiddenfieldsdiv" && key != "hiddenfields");
                  })
                  if (hiddenfields.length > 0) {
                      for (var index in hiddenfields) {
                          var hiddenfield = hiddenfields[index];
                          var ind = hiddenfield.replace("hiddenfield", "");
                          var stdname = pbuilder_items['items'][id]['options']["hiddenfieldname" + ind];
                          var stdvalue = pbuilder_items['items'][id]['options'][hiddenfield];
                          var fieldtype = pbuilder_items['items'][id]['options']["hiddenfieldtype" + ind];
                          pbuilderAddHiddenField(ind, stdname, stdvalue, fieldtype);
                          //pbuilderAddHiddenField(ind, pbuilder_items['items'][id]['options'][hiddenfield]);
                      }
                  }
              }
              var $menu = $('.pbuilder_shortcode_menu');
              pbuilderHideControls($('false'), true);
              pbuilderRefreshControls($jq, $menu);
              $menu.stop(true).animate({right: 0}, 300);
              $('.pbuilder_shortcode_menu_toggle').stop(true).animate({'right': 354}, 300);
              $('#pbuilder_body').stop(true).animate({borderRightWidth: 400}, 300);
              if (shortcode == "optin") {
                  $menu.find("#pbuilder_textarea_formcode").trigger("keyup");
              }
              $jq(this).trigger("after_menu_created", [$jq, iDocument, $menu]);
              
          }
          
          get_lfp_flow_fields();
        });

        $jq(iDocument).on('click', '.pbuilder_module_controls .pbuilder_edit', function (e) {
          window.pbuilder_shopify_products = [];
          localStorage.setItem("counter", 1);        
          e.preventDefault();
          $controls = $jq(this).closest('.pbuilder_module_controls');
          $module = $controls.closest('.pbuilder_module');
          $row = $module.closest('.pbuilder_row');
          pass_mod = $module;
          $this = $jq(this);
          
          var id = parseInt($module.attr('data-modid'));
          var shortcode = $module.attr('data-shortcode');
          if (pbuilder_shortcode_sw) {
            
              var $menu = $('.pbuilder_shortcode_menu');

              if ($menu.hasClass('pbuilder_rowedit_menu') || $menu.hasClass('pbuilder_columnedit_menu') || parseInt($menu.attr('data-modid')) != id) {
                  $menu.removeClass('pbuilder_rowedit_menu');
                  $menu.removeClass('pbuilder_columnedit_menu');

                  $jq('.pbuilder_row.selected, .pbuilder_module.selected').removeClass('selected');
                  $jq('.pbuilder_row.child_selected').removeClass('child_selected');
                  $row.addClass('child_selected');
                  $menu.attr('data-modid', id).attr('data-shortcode', shortcode);
                  if ($menu.css('right') != '0px') {
                      $menu.stop(true).animate({right: 0}, 300);
                      $('#pbuilder_body').stop(true).animate({borderRightWidth: 320}, 300);
                  }
                  
                  $jq('.pbuilder_module_controls.pbuilder_gradient_primary').removeClass('pbuilder_gradient_primary');
                  $controls.addClass('pbuilder_gradient_primary');
                  $module.addClass('selected');
                  
                  $menu.find('.pbuilder_menu_inner').stop(true).animate({opacity: 0}, 200, function () {
                      var shHtml = pbuilderCreateShortcodeMenu(id, $module);
                      $(this).html(shHtml).animate({opacity: 1}, 300);
                      if (shortcode == "optin") {
                          var customfields = Object.keys(pbuilder_items['items'][id]['options']).filter(function (key) {
                              return (key.indexOf("customfield") > -1 && key.indexOf("customfieldlabel") <= -1 && key.indexOf("customfieldtype") <= -1 && key.indexOf("customfieldrequired") <= -1 && key.indexOf("customfielderror") <= -1 && key != "addcustomfield" && key != "customfieldsdiv" && key != "customfields");
                          })
                          if (customfields.length > 0) {
                              for (var index in customfields) {
                                  var customfield = customfields[index];
                                  var ind = customfield.replace("customfield", "");
                                  var stdlabel = pbuilder_items['items'][id]['options']["customfieldlabel" + ind];
                                  var stdtext = pbuilder_items['items'][id]['options'][customfield];
                                  var fieldtype = pbuilder_items['items'][id]['options']["customfieldtype" + ind];
                                  pbuilderAddCustomField(ind, stdlabel, stdtext, fieldtype);
                              }
                          }
                          var hiddenfields = Object.keys(pbuilder_items['items'][id]['options']).filter(function (key) {
                              return (key.indexOf("hiddenfield") > -1 && key.indexOf("hiddenfieldname") <= -1 && key.indexOf("hiddenfieldtype") <= -1 && key != "addhiddenfield" && key != "hiddenfieldsdiv" && key != "hiddenfields");
                          })
                          if (hiddenfields.length > 0) {
                              for (var index in hiddenfields) {
                                  var hiddenfield = hiddenfields[index];
                                  var ind = hiddenfield.replace("hiddenfield", "");
                                  var stdname = pbuilder_items['items'][id]['options']["hiddenfieldname" + ind];
                                  var stdvalue = pbuilder_items['items'][id]['options'][hiddenfield];
                                  var fieldtype = pbuilder_items['items'][id]['options']["hiddenfieldtype" + ind];
                                  pbuilderAddHiddenField(ind, stdname, stdvalue, fieldtype);
                              }
                          }
                      }
                      pbuilderHideControls($('false'), true);
                      pbuilderRefreshControls($jq, $menu);
                      if (shortcode == "optin" || shortcode == "overlay") {
                          $menu.find("#pbuilder_textarea_formcode").trigger("keyup");
                      }
                      
                      $this.trigger("after_menu_created", [$jq, iDocument, $menu]);
                      
                      if (shortcode == "shopify") {
                        pbuilder_setup_shopify();
                      }
                      
                      if (shortcode == "shopify_grid") {
                        pbuilder_setup_shopify_grid();
                      }

                  });
              }
              
          } else {
              
              pbuilder_shortcode_sw = true;
              $row.addClass('child_selected');
              $module.addClass('selected');
              $controls.addClass('pbuilder_gradient_primary');
              var html = ($('.pbuilder_shortcode_menu_toggle').length <= 0 ? '<div class="pbuilder_shortcode_menu_toggle pbuilder_gradient">Close</div>' : '') + '<div style="left:auto; right:-250px;" class="pbuilder_shortcode_menu pbuilder_controls_wrapper" data-modid="' + id + '" data-shortcode="' + shortcode + '"><form autocomplete="off"><div id="pbuilder_menu_inner" class="pbuilder_menu_inner">';
              html += pbuilderCreateShortcodeMenu(id, $module);
              html += '</div></form></div>';
              //$('#customfieldsdiv', html).html('');
              $('body').append(html);
              
              if (shortcode == "optin") {
                  var customfields = Object.keys(pbuilder_items['items'][id]['options']).filter(function (key) {
                      return (key.indexOf("customfield") > -1 && key.indexOf("customfieldlabel") <= -1 && key.indexOf("customfieldtype") <= -1 && key.indexOf("customfieldrequired") <= -1 && key.indexOf("customfielderror") <= -1 && key != "addcustomfield" && key != "customfieldsdiv" && key != "customfields");
                  })
                  if (customfields.length > 0) {
                      for (var index in customfields) {
                          var customfield = customfields[index];
                          var ind = customfield.replace("customfield", "");
                          var stdlabel = pbuilder_items['items'][id]['options']["customfieldlabel" + ind];
                          var stdtext = pbuilder_items['items'][id]['options'][customfield];
                          var fieldtype = pbuilder_items['items'][id]['options']["customfieldtype" + ind];
                          pbuilderAddCustomField(ind, stdlabel, stdtext, fieldtype);
                      }
                  }
                  var hiddenfields = Object.keys(pbuilder_items['items'][id]['options']).filter(function (key) {
                      return (key.indexOf("hiddenfield") > -1 && key.indexOf("hiddenfieldname") <= -1 && key.indexOf("hiddenfieldtype") <= -1 && key != "addhiddenfield" && key != "hiddenfieldsdiv" && key != "hiddenfields");
                  })
                  if (hiddenfields.length > 0) {
                      for (var index in hiddenfields) {
                          var hiddenfield = hiddenfields[index];
                          var ind = hiddenfield.replace("hiddenfield", "");
                          var stdname = pbuilder_items['items'][id]['options']["hiddenfieldname" + ind];
                          var stdvalue = pbuilder_items['items'][id]['options'][hiddenfield];
                          var fieldtype = pbuilder_items['items'][id]['options']["hiddenfieldtype" + ind];
                          pbuilderAddHiddenField(ind, stdname, stdvalue, fieldtype);
                          //pbuilderAddHiddenField(ind, pbuilder_items['items'][id]['options'][hiddenfield]);
                      }
                  }
              }
              
              var $menu = $('.pbuilder_shortcode_menu');
              pbuilderHideControls($('false'), true);
              pbuilderRefreshControls($jq, $menu);
              $menu.stop(true).animate({right: 0}, 300);
              $('.pbuilder_shortcode_menu_toggle').stop(true).animate({'right': 354}, 300);
              $('#pbuilder_body').stop(true).animate({borderRightWidth: 400}, 300);
              if (shortcode == "optin" || shortcode == "overlay") {
                  $menu.find("#pbuilder_textarea_formcode").trigger("keyup");
              }
              $jq(this).trigger("after_menu_created", [$jq, iDocument, $menu]);
              
              if (shortcode == "shopify") {
                pbuilder_setup_shopify();
              }
              
              if (shortcode == "shopify_grid") {
                
                pbuilder_setup_shopify_grid();
              }
          }          
          
          get_lfp_flow_fields();
        });
        
        window.pbuilder_shopify_products = [];
        window.pbuilder_shopify_product_urls = [];
        window.pbuilder_shopify_products_loaded = 0;
        window.pbuilder_shopify_products_all = [];
        
        function pbuilder_setup_shopify_grid(){
          $('#pbuilder_input_shopify_products_data').parent().parent().hide();
          $('#pbuilder_input_shopify_page_url').after('<div id="pbuilder_shopify_fetch_url_grid" class="pbuilder_gradient">Fetch</div>');  
          $('#pbuilder_input_shopify_products_data').parent().parent().after('<div id="pbuilder_shopify_grid_products"></div>');  
          
          try{
            window.pbuilder_shopify_products = JSON.parse($('#pbuilder_input_shopify_products_data').val());
          } catch(e){
          }
          
          if(!$.isEmptyObject(window.pbuilder_shopify_products)){
            pbuilder_show_shopify_products();
          }
        }
        
        function pbuilder_setup_shopify(){
          $('#pbuilder_input_shopify_product_images').hide();
            $('#pbuilder_input_shopify_page_url').after('<div id="pbuilder_shopify_fetch_url" class="pbuilder_gradient">Fetch</div>');
            
            
            try{
              var shopify_images = JSON.parse($('#pbuilder_input_shopify_product_images').val());
            } catch(e){
              var shopify_images = [];
            }
          
            var shopify_images_html = '';                
            for(i in shopify_images){
               shopify_images_html+='<span class="pbuilder_shopify_product_image_wrapper_sb"><img class="pbuilder_shopify_product_image_sb';
                 var image_src_arr = shopify_images[i].split('###');
                 if(image_src_arr.length>1){
                    shopify_images_html+=' pbuilder_shopify_product_grid_image_selected ';
                 }
                 shopify_images_html+='" data-imageid="'+i+'" src="'+shopify_images[i]+'" /></span>';              
            }
                
            $('#pbuilder_input_shopify_product_images').after('<div id="pbuilder_shopify_product_images">'+shopify_images_html+'</div>'); 
        }
        
        $(document).on('click','#pbuilder_shopify_fetch_url',function(){
            $('#pbuilder_shopify_fetch_url').hide();
            var html = '<div id="pbuilder_shopify_loader_popup" class="pbuilder_popup pbuilder_popup_shopify pbuilder_controls_wrapper">';
            html += '<div class="pbuilder_popup_content">';
            
            html += pbuilder_popup_loader + '<br /><div id="pbuilder_shopify_loader_status">Loading Product ...';
            
            html += '</div></div><div class="pbuilder_popup_shadow"></div>';
            $('#pbuilder_body').prepend(html);
            
            $.ajax({
              type: 'POST',
              url: ajaxurl,
              data:{
                  action:'pbuilder_fetch_shopify_single',
                  shopify_url:$('#pbuilder_input_shopify_page_url').val()
              },
              dataType: 'json'}
              ).done(function(response) {
                $('#pbuilder_input_shopify_product_name').val(response.data.name);
                $('#pbuilder_textarea_shopify_product_description').val(response.data.description);
                $('#pbuilder_input_shopify_product_price').val(response.data.currency+response.data.price);
                var pbuilder_shopify_images = [];
                
                var shopify_images_html = '';                
                for(i in response.data.images){
                   shopify_images_html+='<span class="pbuilder_shopify_product_image_wrapper_sb"><img class="pbuilder_shopify_product_image_sb';
                   var image_src_arr = response.data.images[i].split('###');
                   if(image_src_arr.length>1){
                      shopify_images_html+=' pbuilder_shopify_product_grid_image_selected ';
                   }
                   shopify_images_html+='" data-imageid="'+i+'" src="'+response.data.images[i]+'" /></span>';              
                }
                
                $('#pbuilder_input_shopify_page_url').val();
                
                $('#pbuilder_input_shopify_product_images').val(JSON.stringify(response.data.images));
                $('#pbuilder_shopify_product_images').html(shopify_images_html);
                
                $('#pbuilder_input_shopify_product_name').trigger('keyup');
                $('#pbuilder_textarea_shopify_product_description').trigger('keyup');
                $('#pbuilder_input_shopify_product_price').trigger('keyup');
                $('#pbuilder_input_shopify_product_images').trigger('keyup');
                
                $('#pbuilder_shopify_loader_popup').remove();
                $('#pbuilder_shopify_fetch_url').show();
             });
        });
        
        
        
        
        $(document).on('click','#pbuilder_shopify_fetch_url_grid',function(){
            $('#pbuilder_shopify_fetch_url_grid').hide();
            var html = '<div id="pbuilder_shopify_loader_popup" class="pbuilder_popup pbuilder_popup_shopify pbuilder_controls_wrapper">';
            html += '<div class="pbuilder_popup_content">';
            
            html += pbuilder_popup_loader + '<br /><div id="pbuilder_shopify_loader_status">Loading Products ...';
            
            html += '</div></div><div class="pbuilder_popup_shadow"></div>';
            $('#pbuilder_body').prepend(html);
            
            $.ajax({
              type: 'POST',
              url: ajaxurl,
              data:{
                  action:'pbuilder_fetch_shopify_grid',
                  shopify_url:$('#pbuilder_input_shopify_page_url').val()
              },
              dataType: 'json'}
              ).done(function(response) {
                total_products = 0;
                for(i in response.data){
                  window.pbuilder_shopify_product_urls[i]=response.data[i];
                  total_products++; 
                }
                if(total_products>0){
                  window.pbuilder_shopify_products = [];
                  window.pbuilder_shopify_products_loaded = 0;
                  load_shopify_product_grid();
                } else {
                  $('#pbuilder_shopify_loader_status').parent().find('.pbuilder_loader').hide();
                  $('#pbuilder_shopify_fetch_url_grid').show();
                  $('#pbuilder_shopify_loader_status').html('No products found <br /><a href="#" class="pbuilder_gradient pbuilder_button pbuilder_popup_close right">Close</a>');
                }
             });             
        });
        
        function load_shopify_product_grid(){
           $.ajax({
              type: 'POST',
              url: ajaxurl,
              data:{
                  action:'pbuilder_fetch_shopify_grid_product',
                  shopify_url:window.pbuilder_shopify_product_urls[window.pbuilder_shopify_products_loaded]
              },
              dataType: 'json'}
              ).done(function(response) {
                
                if(typeof response.data !== 'undefined' && typeof response.data.name !== 'undefined'){                  
                  window.pbuilder_shopify_products[window.pbuilder_shopify_products_loaded] = response.data;
                  $('#pbuilder_shopify_loader_status').html('Loading Product '+(window.pbuilder_shopify_products_loaded+1)+'/'+window.pbuilder_shopify_product_urls.length );
                  window.pbuilder_shopify_products_loaded++;
                  if(window.pbuilder_shopify_products_loaded < window.pbuilder_shopify_product_urls.length){
                    load_shopify_product_grid();
                  } else {
                    pbuilder_show_shopify_products(); 
                    $('#pbuilder_shopify_fetch_url_grid').show();
                  }
                }                
                
             });  
        }
        
        function pbuilder_show_shopify_products(){
           var product_controls_html='';
           for(product in window.pbuilder_shopify_products){
               product_controls_html+='<div class="pbuilder_shopify_grid_product">';
               var product_number = Number(product)+1;
               product_controls_html+='<div class="pbuilder_shopify_grid_product_title">Product '+product_number+'</div><div data-productid="product" class="pbuilder_shopify_grid_delete">Remove</div>';
              
               
               var control_data = {};
               control_data.type = 'input';
               control_data.std = window.pbuilder_shopify_products[product].url;
               control_data.label = 'Product URL:';
               control_data['label_width']=0.3;
               control_data['control_width']=0.7;               
               var newControl = new pbuilderControl('product_'+product+'_url', control_data);
               cntHtml = newControl.html();
               product_controls_html+=cntHtml;
               
               var control_data = {};
               control_data.type = 'input';
               control_data.std = window.pbuilder_shopify_products[product].name;
               control_data.label = 'Product Name:';
               control_data['label_width']=0.3;
               control_data['control_width']=0.7;               
               var newControl = new pbuilderControl('product_'+product+'_name', control_data);
               cntHtml = newControl.html();
               product_controls_html+=cntHtml;
               
               var control_data = {};
               control_data.type = 'input';
               if(typeof window.pbuilder_shopify_products[product].currency !== 'undefined'){
                control_data.std = window.pbuilder_shopify_products[product].currency+window.pbuilder_shopify_products[product].price;
               } else {
                control_data.std = window.pbuilder_shopify_products[product].price;
               }
               control_data.label = 'Product Price:';
               control_data['label_width']=0.3;
               control_data['control_width']=0.7;               
               var newControl = new pbuilderControl('product_'+product+'_price', control_data);
               cntHtml = newControl.html();
               product_controls_html+=cntHtml;
               
               var control_data = {};
               control_data.type = 'textarea';
               control_data.std = window.pbuilder_shopify_products[product].description;
               control_data.label = 'Product Description:';
               control_data['label_width']=0.3;
               control_data['control_width']=0.7;               
               var newControl = new pbuilderControl('product_'+product+'_description', control_data);
               cntHtml = newControl.html();
               product_controls_html+=cntHtml;
               
               var control_data = {};
               control_data.type = 'input';
               control_data.std = window.pbuilder_shopify_products[product].images;
               control_data.label = 'Product Images:';
               control_data['label_width']=0.3;
               control_data['control_width']=0.7;    
               control_data['class']='pbuilder_shopify_image_input';    
               var newControl = new pbuilderControl('product_'+product+'_images', control_data);
               cntHtml = newControl.html();
               product_controls_html+=cntHtml;
               
               product_controls_html+='<div id="pbuilder_shopify_product_images">';               
               for(i in window.pbuilder_shopify_products[product].images){
                 product_controls_html+='<span class="pbuilder_shopify_product_image_wrapper_sb"><img class="pbuilder_shopify_product_grid_image';
                 var image_src_arr = window.pbuilder_shopify_products[product].images[i].split('###');
                 if(image_src_arr.length>1){
                    product_controls_html+=' pbuilder_shopify_product_grid_image_selected ';
                 }
                 product_controls_html+='" data-productid="'+product+'" data-imageid="'+i+'" src="'+window.pbuilder_shopify_products[product].images[i]+'" /></span>';              
               }
               product_controls_html+='</div>'; 
               
               product_controls_html+='<div class="clearfix"></div></div>';
           }
           $('#pbuilder_shopify_grid_products').html(product_controls_html);
           $('.pbuilder_shortcode_menu').fmCustomScrollbar('update');
           $('#pbuilder_shopify_loader_popup').remove();
           pbuilder_read_shopify_product_grid(); 
           pbuilder_shopify_products_loaded = 0;   
           
           $('#pbuilder_shopify_grid_products').find('input,textarea').each(function(index, element) {
            $(this).on('keyup',function(){
              pbuilder_read_shopify_product_grid();
            });
           });
           
        }
        
        $(document).on('click','.pbuilder_shopify_grid_delete',function(){
          $(this).parent().remove();
          pbuilder_read_shopify_product_grid();
        });
        
        function pbuilder_read_shopify_product_grid(){
          window.pbuilder_shopify_products = {};
          
          $('#pbuilder_shopify_grid_products').find('input,textarea').each(function(index, element) {
            
            var element_data = $(this).attr('id').split('product_');
             element_data = element_data[1].split('_');
             if(typeof window.pbuilder_shopify_products[element_data[0]] == 'undefined'){
               window.pbuilder_shopify_products[element_data[0]]={};
             }
             if(element_data[1] == 'images'){
                try {
                  window.pbuilder_shopify_products[element_data[0]][element_data[1]]=$(this).val().split(',');
                } catch(e){
                  window.pbuilder_shopify_products[element_data[0]][element_data[1]]='';
                }
             } else {
               window.pbuilder_shopify_products[element_data[0]][element_data[1]]=$(this).val();
             }
          });          
          
          $('#pbuilder_input_shopify_products_data').val(JSON.stringify(pbuilder_shopify_products));
          $(document).find("#pbuilder_input_shopify_products_data").trigger("keyup");          
        }
        
        $(document).on('click','.pbuilder_shopify_product_image_sb',function(){
            var pbuilder_shopify_images = [];
            var selectedimageid = $(this).data('imageid');
            
            $(document).find('.pbuilder_shopify_product_image_sb').each(function(index, element) {
              $(this).removeClass('pbuilder_shopify_product_grid_image_selected');  
              var image_src_arr = $(this).attr('src').split('###');
              if( selectedimageid == $(this).data('imageid')){
                pbuilder_shopify_images.push(image_src_arr[0]+'###'+$(this).data('imageid'));  
              } else {
                pbuilder_shopify_images.push(image_src_arr[0]);  
              }              
            });
            
            $('#pbuilder_input_shopify_product_images').val(JSON.stringify(pbuilder_shopify_images));
            $('#pbuilder_input_shopify_product_images').trigger('keyup');
            
            $(this).addClass('pbuilder_shopify_product_grid_image_selected'); 
        });
        
        $(document).on('click','.pbuilder_shopify_product_grid_image',function(){
            /*
            $imagecontainer = $(this).parent().parent();
            $(this).parent().remove();
            var productid = $(this).data('productid');
            var pbuilder_shopify_images = [];
            
            $imagecontainer.find('.pbuilder_shopify_product_grid_image').each(function(index, element) {
              pbuilder_shopify_images.push($(this).attr('src'));              
            });
            
            
            $('#pbuilder_input_product_'+productid+'_images').val(pbuilder_shopify_images.join(','));
            $('#pbuilder_input_product_'+productid+'_images').trigger('keyup');
            */
            $imagecontainer = $(this).parent().parent();
            var productid = $(this).data('productid');
            var pbuilder_shopify_images = [];
            var selectedimageid = $(this).data('imageid');
            
            $imagecontainer.find('.pbuilder_shopify_product_grid_image').each(function(index, element) {
              $(this).removeClass('pbuilder_shopify_product_grid_image_selected');  
              var image_src_arr = $(this).attr('src').split('###');
              if( selectedimageid == $(this).data('imageid')){
                pbuilder_shopify_images.push(image_src_arr[0]+'###'+$(this).data('imageid'));  
              } else {
                pbuilder_shopify_images.push(image_src_arr[0]);  
              }
            });
            
            $('#pbuilder_input_product_'+productid+'_images').val(pbuilder_shopify_images.join(','));
            $('#pbuilder_input_product_'+productid+'_images').trigger('keyup');
            $(this).addClass('pbuilder_shopify_product_grid_image_selected');  
            
            pbuilder_read_shopify_product_grid();
        });
        
        $jq(iDocument).on('click', '.pbuilder_module_controls .pbuilder_drag', function (e) {
		         e.preventDefault();
        });
        $jq(iDocument).on('click', '.pbuilder_module_controls .pbuilder_delete', function (e) {
            e.preventDefault();
            
            if ($jq(this).attr('row-delete') == undefined)
                if (!confirm("Are you sure you want to delete this element?"))
                    return;
            moduleDeleteFlag = true;
            var $module = $jq(this).parent().parent();
            var modid = parseInt($module.attr('data-modid'));
            var $column = $module.parent().parent();
            var rowid = $column.closest('[data-rowid]').attr('data-rowid');
            $module.remove();
            if ($('.pbuilder_shortcode_menu').attr('data-modid') == modid) {
                $('.pbuilder_shortcode_menu_toggle').stop(true).animate({'right': -47}, 300);
                $('.pbuilder_shortcode_menu').animate({right: -300}, 300, function () {
                    $(this).remove();
                    pbuilder_shortcode_sw = false;
                });
                $('#pbuilder_body').stop(true).animate({borderRightWidth: 0}, 300);
            }
            if (rowid != 'sidebar') {
                rowid = parseInt(rowid);
                var colnum = parseInt($column.attr('data-colnumber'));
                pbuilder_items['rows'][rowid]['columns'][colnum] = [];
                $column.find('.pbuilder_module').each(function (index) {
                    pbuilder_items['rows'][rowid]['columns'][colnum][index] = parseInt($jq(this).attr('data-modid'));
                });
                delete pbuilder_items['items'][modid];
                if ($column.find('.pbuilder_module').length == 0) {
                    $column.addClass('empty');
                }
            }
            else {
                pbuilder_items['sidebar']['items'] = [];
                $column.find('.pbuilder_module').each(function (index) {
                    pbuilder_items['sidebar']['items'][index] = parseInt($jq(this).attr('data-modid'));
                });
                delete pbuilder_items['items'][modid];
            }
            $jq('#pbuilder_wrapper').trigger('refresh');
            checkHTML();
        });
        $jq(iDocument).on('click', '.pbuilder_module_controls .pbuilder_clone', function (e) {
            e.preventDefault();
            var $module = $jq(this).parent().parent();
            var $clone = $module.clone();
            var modid = parseInt($module.attr('data-modid'));
            var $column = $module.parent().parent();
            var colnum = parseInt($column.attr('data-colnumber'));
            var rowid = $column.closest('[data-rowid]').attr('data-rowid');
            var newid = 0;
            while (typeof pbuilder_items['items'][newid] != 'undefined')
                newid++;
            pbuilder_items['items'][newid] = {};
            pbuilder_items['items'][newid]['f'] = pbuilder_items['items'][modid]['f'];
            pbuilder_items['items'][newid]['slug'] = pbuilder_items['items'][modid]['slug'];
            pbuilder_items['items'][newid]['options'] = $.extend(true, {}, pbuilder_items['items'][modid]['options']);
            $clone.insertAfter($module);
            $module.next().attr('data-modid', newid);
            /*	$module.next().find('canvas').each(function(index){
             pbuilderCloneCanvas($(this)[0], $module.find('canvas').eq(index)[0]);
             });
             setTimeout(function(){$module.next().trigger('refresh').find('.pbuilder_module_controls .pbuilder_edit').trigger('click');}, 1);*/
            var $module = $jq('.pbuilder_module[data-modid=' + newid + ']:first');
            var f = pbuilder_items['items'][newid]['f'];
            var holder = $module.find('.pbuilder_module_content:first');
            var options = pbuilder_items['items'][newid]['options'];
            pbuilderGetShortcode(f, holder, options);

            if (rowid != 'sidebar') {
                rowid = parseInt(rowid);
                pbuilder_items['rows'][rowid]['columns'][colnum] = [];
                $column.find('.pbuilder_module').each(function (index) {
                    pbuilder_items['rows'][rowid]['columns'][colnum][index] = parseInt($jq(this).attr('data-modid'));
                });
            }
            else {
                pbuilder_items['sidebar']['items'] = [];
                $column.find('.pbuilder_module').each(function (index) {
                    pbuilder_items['sidebar']['items'][index] = parseInt($jq(this).attr('data-modid'));
                });
            }
        });
        $jq(iDocument).on('click', '.pbuilder_module_controls .pbuilder_copy', function (e) {
            e.preventDefault();
            var $module = $jq(this).parent().parent();
            var $clone = $module.clone();
            var modid = parseInt($module.attr('data-modid'));
            var $column = $module.parent().parent();
            var colnum = parseInt($column.attr('data-colnumber'));
            var rowid = $column.closest('[data-rowid]').attr('data-rowid');
            var holder = $module.find('.pbuilder_module_content:first');
            var copiedoptions = {};
            copiedoptions['f'] = pbuilder_items['items'][modid]['f'];
            copiedoptions['slug'] = pbuilder_items['items'][modid]['slug'];
            copiedoptions['options'] = $.extend(true, {}, pbuilder_items['items'][modid]['options']);
            var copiedtext = $('<div>').append($clone).html();
            copiedtext = copiedtext.replace('data-modid="' + modid + '"', 'data-modid="%modid"');
            holder.closest('.pbuilder_module').find('.pbuilder_module_loader').show();
            var data = {
                action: 'pbuilder_copy',
                copiedtype: 'module',
                copiedoptions: JSON.stringify(copiedoptions),
                copiedtext: copiedtext
            }
            if (typeof window.pbuilder_shajax[modid] != 'undefined')
                window.pbuilder_shajax[modid].abort();
            window.pbuilder_shajax[modid] = $.post(ajaxurl, data, function (response) {
                //var json_data = JSON.parse(response);
                //var copiedoptions = JSON.parse(json_data.copiedoptions);
                holder.closest('.pbuilder_module').trigger('refresh');
                holder.closest('.pbuilder_module').find('.pbuilder_module_loader').hide();
            });
        });
        $jq(iDocument).on('click', '.pbuilder_module_controls .pbuilder_paste', function (e) {
            e.preventDefault();
            var $module = $jq(this).parent().parent();
            var modid = parseInt($module.attr('data-modid'));
            var $column = $module.parent().parent();
            var colnum = parseInt($column.attr('data-colnumber'));
            var rowid = $column.closest('[data-rowid]').attr('data-rowid');
            var holder = $module.find('.pbuilder_module_content:first');
            holder.closest('.pbuilder_module').find('.pbuilder_module_loader').show();
            var data = {
                action: 'pbuilder_paste',
                rowid: rowid,
                modid: modid,
                copiedtype: 'module',
            };
            if (typeof window.pbuilder_shajax[modid] != 'undefined')
                window.pbuilder_shajax[modid].abort();
            window.pbuilder_shajax[modid] = $.post(ajaxurl, data, function (response) {
                var json_data = JSON.parse(response);
                var copiedoptions = JSON.parse(json_data.copiedoptions);
                var copiedtext = json_data.copiedtext;
                var $module = $jq('.pbuilder_module[data-modid=' + json_data.modid + ']:first');
                var modid = parseInt($module.attr('data-modid'));
                var $column = $module.parent().parent();
                var colnum = parseInt($column.attr('data-colnumber'));
                var rowid = $column.closest('[data-rowid]').attr('data-rowid');
                var holder = $module.find('.pbuilder_module_content:first');
                var newid = 0;
                while (typeof pbuilder_items['items'][newid] != 'undefined')
                    newid++;
                pbuilder_items['items'][newid] = {};
                pbuilder_items['items'][newid]['f'] = copiedoptions['f'];
                pbuilder_items['items'][newid]['slug'] = copiedoptions['slug'];
                pbuilder_items['items'][newid]['options'] = $.extend(true, {}, copiedoptions['options']);
                copiedtext = copiedtext.replace('data-modid="%modid"', 'data-modid="' + newid + '"');
                $module.before(copiedtext);
                holder.closest('.pbuilder_module').trigger('refresh');
                holder.closest('.pbuilder_module').find('.pbuilder_module_loader').hide();
                var $module = $jq('.pbuilder_module[data-modid=' + newid + ']:first');
                var f = pbuilder_items['items'][newid]['f'];
                var holder = $module.find('.pbuilder_module_content:first');
                var options = pbuilder_items['items'][newid]['options'];
                pbuilderGetShortcode(f, holder, options);
                if (rowid != 'sidebar') {
                    rowid = parseInt(rowid);
                    pbuilder_items['rows'][rowid]['columns'][colnum] = [];
                    $column.find('.pbuilder_module').each(function (index) {
                        pbuilder_items['rows'][rowid]['columns'][colnum][index] = parseInt($jq(this).attr('data-modid'));
                    });
                }
                else {
                    pbuilder_items['sidebar']['items'] = [];
                    $column.find('.pbuilder_module').each(function (index) {
                        pbuilder_items['sidebar']['items'][index] = parseInt($jq(this).attr('data-modid'));
                    });
                }
                holder.closest('.pbuilder_module').trigger('refresh');
            });
        });
        /* Shortcode select control */
        $(document).on('mouseenter', '.pbuilder_select', function () {
            $(this).data('hover', true);
        });
        $(document).on('mouseleave', '.pbuilder_select', function () {
            $(this).data('hover', false);
        });
        $(document).on('click', '.pbuilder_select span, .pbuilder_select .drop_button', function (e) {
            e.preventDefault();
            $parent = $(this).parent();
            if (!$parent.hasClass('active')) {
                $parent.addClass('active').find('ul, input').show();
            }
            else {
                $parent.removeClass('active').find('ul, input').hide();
            }
            pbuilderRefreshControls($jq, $(this).closest('.pbuilder_control'));
        });
        $(document).on('click', '.pbuilder_select ul a', function (e) {
            e.preventDefault();
            var $parent = $(this).closest('.pbuilder_select');
            var multi = $parent.hasClass('pbuilder_select_multi');
            var $select = $('[name=' + $parent.attr('data-name') + ']');
            if (!multi || typeof window.shiftKey == 'undefined' || window.shiftKey == false) {
                if ($parent.attr('data-name') == 'pcolor') {
                    var pcolor = $(this).attr('data-value');
                    var selectedsrc = '';
                    $('[data-name=pname] ul li').each(function () {
                        var src = $('a img', this).attr('src');
                        var url = src.substr(0, src.lastIndexOf("/"));
                        var pname = $('a', this).attr('data-value');
                        var ext = ".png";
                        src = url + "/" + pname + pcolor + ext;
                        $('a img', this).attr('src', src)
                        if ($('a', this).hasClass('selected'))
                            selectedsrc = src;
                    });
                    $('.pre-done-preview:eq(0)').attr('src', selectedsrc);
                }
                $select.val($(this).attr('data-value'));
                $parent.find('span').html($(this).html());
                $parent.removeClass('active').find('ul, input').hide();
                $parent.find('ul a.selected').removeClass('selected');
                $(this).addClass('selected');
            }
            else {
                var multiVal = $select.val();
                var multiHtml = $parent.find('span').html();
                if (!$(this).hasClass('selected')) {
                    $(this).addClass('selected');
                    if (multiVal != '') {
                        multiVal += ',';
                        multiHtml += ',';
                    }
                    multiVal += $(this).attr('data-value');
                    multiHtml += $(this).html();
                }
                else {
                    $(this).removeClass('selected');
                    var multiSplitHtml = multiHtml.split(',');
                    var multiSplitVal = multiVal.split(',');
                    multiHtml = '';
                    multiVal = '';
                    var flag = 0;
                    for (var x in multiSplitVal) {
                        if (multiSplitVal[x] != $(this).attr('data-value')) {
                            if (x != 0 && flag != 1) {
                                multiVal += ',';
                                multiHtml += ',';
                            }
                            multiVal += multiSplitVal[x];
                            multiHtml += multiSplitHtml[x];
                            flag = 0;
                        }
                        else if (x == 0) {
                            flag = 1;
                        }
                    }
                    //multiVal +=	$(this).attr('data-value');
                    //multiHtml += $(this).html();
                }
                $select.val(multiVal);
                $parent.find('span').html(multiHtml);
            }
            $select.trigger('change');
            pbuilderContolChange($jq, $select);
            var StickToTopDiv = jQuery('#pbuilder_body_frame').contents().find('.pbuilder_row_stick_top');
            HeightOfTop = StickToTopDiv.height();
            if (HeightOfTop > 0) {
                // var ele = document.createElement("div");
                // ele.setAttribute("class","stick-top-div");
                // document.body.appendChild(div);
                var height_of_stick_div = StickToTopDiv.height();
                var StickToTopDivAn = jQuery('#pbuilder_body_frame').contents().find('.stick-top-div');
                StickToTopDivAn.css("height", 0+"px");
            }
        });
        $('body').keydown(function (e) {
            var code = e.keyCode || e.which;
            if (e.ctrlKey) {
                window.shiftKey = true;
            }
        });
        $('body').keyup(function (e) {
            var code = e.keyCode || e.which;
            if (code == 17) {
                window.shiftKey = false;
            }
        });
        $('body').click(function () {
            $('.pbuilder_select.active').each(function () {
                if (!$(this).data('hover')) {
                    $(this).removeClass('active').find('ul, input').hide();
                }
            });
        });
        $(document).on('keyup', '.pbuilder_select input', function () {
            var inValue = $(this).val();
            if (inValue == '') {
                $(this).closest('.pbuilder_select').find('ul li').show();
            }
            else {
                $(this).closest('.pbuilder_select').find('ul li').each(function () {
                    if ($(this).html().toLowerCase().search(inValue.toLowerCase()) > -1) {
                        $(this).show();
                    }
                    else {
                        $(this).hide();
                    }
                });
            }
            $(this).closest('.pbuilder_select').find('ul').fmCustomScrollbar('update');
        });
        /* Shortcode input/textarea control */
        $(document).on('click', '.pbuilder_input_wrapper label', function () {
            var $input = $(this).parent().find('input');
            var val = $input.val();
            $input.trigger('focus').val('').val(val);
        })
        $(document).on('keyup', '.pbuilder_shortcode_menu input, .pbuilder_shortcode_menu textarea', function () {
            pbuilderContolChange($jq, $(this), 500);
        }).on("change", function () {
            $(this).trigger("keyup")
        });

        function resize_wpeditor_iframe(){
          $('#pbuilder_editor_popup').css('width',window.innerWidth-400);
          $('#pbuilder_editor_popup').css('height',window.innerHeight-$('.pbuilder_header').height());
          $('#pbuilder_editor_popup').css('top',$('.pbuilder_header').height());
          $('#pbuilder_editor_popup_inner').css('height',window.innerHeight-$('.pbuilder_header').height()-60);
          var iframeHeight = window.innerHeight-$('.pbuilder_header').height()-230;
          $('#pbuilder_editor_ifr_style').remove();
          $('#pbuilder_editor_ifr').after('<style id="pbuilder_editor_ifr_style">#pbuilder_editor_ifr{height:'+iframeHeight+'px !important;}</style>');
        }

        var $pbuilder_editor_textarea;
        $(document).on('click', '.pbuilder_wp_editor_button', function (e) {
            e.preventDefault();
            $pbuilder_editor_textarea = $(this).siblings('.pbuilder_textarea');
            //$(".pbuilder_popup_edit_submit").html("SAVE CONTENT").css({"color":"#00c100"});
            $('#pbuilder_editor_popup, #pbuilder_editor_popup_shadow').show();
            $('#pbuilder_editor-tmce').trigger('click');
            resize_wpeditor_iframe();

            var content = $pbuilder_editor_textarea.val();//.replace(/\n/g, "<br/>");
            if (typeof tinymce.editors[0] != 'undefined') {
                tinymce.editors[0].setContent(content);
            } else if (typeof tinymce.editors[1] != 'undefined') {
                tinymce.editors[1].setContent(content);
            } else {
                tinymce.editors.pbuilder_editor.setContent(content);
            }
//			tinymce.editors.pbuilder_editor.execCommand("mceRepaint");
        });
        $jq(iDocument).on('click', '.pbuilder_column.empty .pbuilder_droppable', function () {
            $this = $jq(this);
            if ($jq(this).children('div').length <= 0) {
                close_edit_menu();
                $column = $this.closest('.pbuilder_column');
                $row = $column.closest('.pbuilder_row');
                $('#pbuilder_add_shortcode_popup').data('row', $row).data('column', $column).fadeIn();
                $('#pbuilder_editor_popup_shadow').show();
            }
        });

		$jq(iDocument).on('click', '.pbuilder_add_shortcode_column', function (e) {
			e.preventDefault();
            $this = $jq(this);
            close_edit_menu();
            if ($jq(this).children('div').length <= 0) {
                $column = $this.closest('.pbuilder_column');
                $row = $column.closest('.pbuilder_row');
                $('#pbuilder_add_shortcode_popup').data('row', $row).data('column', $column).fadeIn();
                $('#pbuilder_editor_popup_shadow').show();
            }

        });

        $('#pbuilder_select_pbuilder_add_shortcode_group').on('change', function () {
            var $popup = $('#pbuilder_add_shortcode_popup');
            $popup.find('.pbuilder_shortcode_group').hide();
			if($(this).val() == 'All'){
			  $('#pbuilder_add_shortcode_popup').addClass('pbuilder_add_shortcode_popup_all');
			} else {
				$('#pbuilder_add_shortcode_popup').removeClass('pbuilder_add_shortcode_popup_all');
			}
            $popup.find('.pbuilder_shortcode_group[data-group="' + $(this).val() + '"]').show();
        });
        $('.pbuilder_shortcode_block').on('click', function () {
            var $popup = $('#pbuilder_add_shortcode_popup');
            var $fbCol = $popup.data('column');
            var $row = $popup.data('row');
            var shortcode_slug = $(this).attr('data-shortcode');
            var sid = 0;
            while (typeof pbuilder_items['items'][sid] != 'undefined') {
                sid++;
            }
            pbuilder_items['items'][sid] = {};
            pbuilder_items['items'][sid]['f'] = pbuilder_shortcodes[shortcode_slug]['function'];
            pbuilder_items['items'][sid]['slug'] = shortcode_slug;
            pbuilder_items['items'][sid]['options'] = {};
            for (var x in pbuilder_shortcodes[shortcode_slug]['options']) {
                if (pbuilder_shortcodes[shortcode_slug]['options'][x]['type'] == 'sortable') {
                    pbuilder_items['items'][sid]['options'][x] = $.extend(true, {}, pbuilder_shortcodes[shortcode_slug]['options'][x]['std']);
                }
                else if (pbuilder_shortcodes[shortcode_slug]['options'][x]['type'] == 'collapsible') {
                    for (var y in pbuilder_shortcodes[shortcode_slug]['options'][x]['options']) {
                        if (pbuilder_shortcodes[shortcode_slug]['options'][x]['options'][y]['type'] == 'sortable') {
                            pbuilder_items['items'][sid]['options'][y] = $.extend(true, {}, pbuilder_shortcodes[shortcode_slug]['options'][x]['options'][y]['std']);
                        }
                        else if (typeof pbuilder_shortcodes[shortcode_slug]['options'][x]['options'][y]['std'] != 'undefined') {
                            pbuilder_items['items'][sid]['options'][y] = pbuilder_shortcodes[shortcode_slug]['options'][x]['options'][y]['std'];
                        }
                        else {
                            pbuilder_items['items'][sid]['options'][y] = '';
                        }
                    }
                }
                else if (typeof pbuilder_shortcodes[shortcode_slug]['options'][x]['std'] != 'undefined') {
                    pbuilder_items['items'][sid]['options'][x] = pbuilder_shortcodes[shortcode_slug]['options'][x]['std'];
                }
                else {
                    pbuilder_items['items'][sid]['options'][x] = '';
                }
            }
            var html = '<div class="pbuilder_module" style="z-index:2" data-modid="' + sid + '" data-shortcode="' + shortcode_slug + '">';
            html += '<img class="pbuilder_module_loader" src="' + pbuilder_url + 'images/module-loader-new.gif" /><div class="pbuilder_module_controls pbuilder_gradient"><a href="#" class="pbuilder_edit" title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a><a href="#" class="pbuilder_drag" title="Drag"><i class="fa fa-arrows" aria-hidden="true"></i></a><a href="#" class="pbuilder_clone" title="Clone"><i class="fa fa-clone" aria-hidden="true"></i></a><a class="pbuilder_copy" href="#" title="Copy"><i class="fa fa-files-o" aria-hidden="true"></i></a><a class="pbuilder_paste" href="#" title="Paste"><i class="fa fa-clipboard" aria-hidden="true"></i></a><a href="#" class="pbuilder_delete" title="Delete Element"><i class="fa fa-trash" aria-hidden="true"></i></a><a class="pbuilder_add_shortcode_column" href="#" title="Add Shortcode After Element"><i class="fa fa-plus-square" aria-hidden="true"></i></a></div>';
            html += '<div class="pbuilder_module_content">';
            html += '</div>';
            html += '</div>';
            $fbCol.find('.pbuilder_droppable:first').append(html);
            var $item = $fbCol.find('.pbuilder_droppable:first').children('.pbuilder_module:last');
            pbuilderGetShortcode(pbuilder_items['items'][sid]['f'], $item.find('.pbuilder_module_content'), pbuilder_items['items'][sid]['options']);
            $item.find('.pbuilder_edit').trigger('click');
            counter = 0;
            // update data
            $fbCol.find('.pbuilder_droppable:first').parent().removeClass('empty');
            var ind = parseInt($fbCol.attr('data-colnumber'));
            var rowId = $row.attr('data-rowid');
            if (rowId != 'sidebar') {
                rowId = parseInt(rowId);
                pbuilder_items['rows'][rowId]['columns'][ind] = new Array();
                $fbCol.find('.pbuilder_module').each(function (index) {
                    pbuilder_items['rows'][rowId]['columns'][ind][index] = parseInt($(this).attr('data-modid'));
                });
            }
            else {
                if (typeof pbuilder_items['sidebar'] == 'undefined')
                    pbuilder_items['sidebar'] = {}
                pbuilder_items['sidebar']['items'] = [];
                $fbCol.find('.pbuilder_module').each(function (index) {
                    pbuilder_items['sidebar']['items'][index] = parseInt($(this).attr('data-modid'));
                });
            }
            $popup.hide();
            $('.pbuilder_popup_shadow, #pbuilder_editor_popup_shadow').hide();
        });
        $(document).on('click', '.pbuilder_popup_edit_submit', function (e) {
            e.preventDefault();
            $('#pbuilder_editor_popup, #pbuilder_editor_popup_shadow').hide();
            var content = tinymce.activeEditor.getContent();//tinymce.activeEditor.getContent({format : 'text'})
            var p_count = content.split(/<p/g).length - 1;
            //content = content.replace(/<br\/>/g, "\n");
            if (p_count <= 1)
                content = content.replace(/<p>/, "").replace(/<\/p>$/, "");
            $pbuilder_editor_textarea.val(content).trigger('keyup');
        });
        /* Shortcode checkbox control */
        $(document).on('click', '.pbuilder_checkbox', function () {
            var $input = $(this).parent().find('.pbuilder_checkbox_input').hide();

            if ($(this).hasClass('active')) {
                $input.val('false').trigger('change');
                $(this).removeClass('active');
        				if(this.id && this.id=='pbuilder_border_advanced'){
        					$(this).parent().parent().find('.pbuilder_border_style_simple').show();
        					$(this).parent().parent().find('.pbuilder_border_style').hide();
        				}
            }
            else {
                $input.val('true').trigger('change');
                $(this).addClass('active');
        				if(this.id && this.id=='pbuilder_border_advanced'){
        					$(this).parent().parent().find('.pbuilder_border_style_simple').hide();
        					$(this).parent().parent().find('.pbuilder_border_style').show();
        				}
            }
            pbuilderContolChange($jq, $input);
        });
        /* Shortcode icon control */
        $(document).on('click', '.pbuilder_icon_pick', function (e) {
            e.preventDefault();
            var $drop = $(this).parent().find('.pbuilder_icon_dropdown');
            if (!$(this).hasClass('active')) {
                $(this).addClass('active');
                $drop.show().addClass('active').fmCustomScrollbar('update');
                $(this).parent().find('.pbuilder_icon_drop_arrow').show();
            }
            else {
                $(this).removeClass('active');
                $drop.hide().removeClass('active');
                $(this).parent().find('.pbuilder_icon_drop_arrow').hide();
            }
            pbuilderRefreshControls($jq, $(this).closest('.pbuilder_control'));
        });
        $(document).on('click', '.pbuilder_icon_dropdown a', function (e) {
            e.preventDefault();
            var $parent = $(this).closest('.pbuilder_control');
            var $input = $parent.find('input:first');
            var val = $(this).attr('href');
            $input.val(val);
            $parent.find('.pbuilder_icon_holder i:first').attr('class', val + ' frb_icon ' + val.substr(0, 2));
            pbuilderContolChange($jq, $input);
        });
        $(document).on('click', '.pbuilder_icon_tab', function (e) {
            e.preventDefault();
            if (!$(this).hasClass('active')) {
                var $parent = $(this).closest('.pbuilder_control');
                var tabid = $(this).attr('data-tabid');
                $parent.find('.pbuilder_icon_tab.active, .pbuilder_icon_noicon.active').removeClass('active');
                $(this).addClass('active');
                $parent.find('.pbuilder_icon_dropdown_content.active').removeClass('active');
                $parent.find('.pbuilder_icon_dropdown_content[data-tabid=' + tabid + ']').addClass('active');
                pbuilderRefreshControls($jq, $parent);
            }
        });
        $(document).on('click', '.pbuilder_icon_noicon', function (e) {
            e.preventDefault();
            if (!$(this).hasClass('active')) {
                var $parent = $(this).closest('.pbuilder_control');
                var $input = $parent.find('input:first');
                var tabid = $(this).attr('data-tabid');
                $input.val('no-icon')
                $parent.find('.pbuilder_icon_holder i:first').attr('class', 'frb_icon no-icon');
                $parent.find('.pbuilder_icon_tab.active').removeClass('active');
                $(this).addClass('active');
                $parent.find('.pbuilder_icon_dropdown_content.active').removeClass('active');
                pbuilderRefreshControls($jq, $parent);
                pbuilderContolChange($jq, $input);
            }
        });
        $(document).on('mouseenter', '.pbuilder_icon_dropdown, .pbuilder_icon_pick', function () {
            $(this).data('hover', true);
        });
        $(document).on('mouseleave', '.pbuilder_icon_dropdown, .pbuilder_icon_pick', function () {
            $(this).data('hover', false);
        });
        $('body').click(function () {
            $('.pbuilder_icon_dropdown.active').each(function () {
                if (!$(this).data('hover') && !$(this).parent().find('.pbuilder_icon_pick').data('hover')) {
                    $(this).removeClass('active').hide();
                    $(this).parent().find('.pbuilder_icon_drop_arrow').hide();
                    $(this).parent().find('.pbuilder_icon_pick').removeClass('active');
                }
            });
        });
        /*	Shortcode media select control	*/
        $(document).on('click', '.pbuilder_media_select_button', function (e) {
            e.preventDefault();
            var media, $this = $(this);
            if (typeof media !== 'undefined') {
                media.open();
            } else {                
                media = wp.media({
                  title: 'Select images to display',
                  button: {
                    text: 'Select images'
                  },
                  multiple: 'add'
                });
            }
            media.on('select', function () {
                var attachment = media.state().get('selection');
                media.close();
                var out = '';
                attachment.map(function (att) {
                    out = out + ',' + att.id;
                });
                while (out.substr(0, 1) === ',') {
                    out = out.substr(1);
                }
                $this.siblings('.pbuilder_media_select_input').find('input').val(out).trigger('keyup');
            });
            media.on('open', function () {
                var attachment, sel = media.state().get('selection');
                preselect = $this.siblings('.pbuilder_media_select_input').find('input').val().split(',');
                preselect.forEach(function (id) {
                    attachment = wp.media.attachment(id);
                    attachment.fetch();
                    sel.add(attachment ? [attachment] : []);
                });
            });
            media.open();
        });
        /* Shortcode image control */
        var thickboxId = '';
        $(document).on('click', '.pbuilder_image_button', function (e) {
            e.preventDefault();
            var frame, $this = $(this);
            if (typeof frame != 'undefined') {
                frame.open();
            } else {
                frame = wp.media({
                    button: {
                        close: false
                    }
                });
            }
            frame.on('select', function () {
                var attachment = frame.state().get('selection').first();
                frame.close();
                $this.siblings('.pbuilder_image_input').find('input').val(attachment.attributes.url).trigger('keyup').siblings('span').html('');
            });
            frame.open();
        });
//		var thickboxId =  '';
//		$(document).on('click','.pbuilder_image_button', function(e) {
//			e.preventDefault();
//			thickboxId = '#'+ $(this).attr('data-input') + '_holder';
//			formfield = $(this).attr('data-input');
//			var mediaurl = ajaxurl.substr(0,ajaxurl.indexOf('admin-ajax'))+'media-upload.php';
//			tb_show('', mediaurl+ '?type=image&amp;width=620&amp;height=420&amp;TB_iframe=true');
//			return false;
//		});
        $(document).on('click', '.pbuilder_image_input span', function () {
            $(this).hide();
            $(this).parent().find('input').focus();
        });
        $(document).on('focusout', '.pbuilder_image_input input', function () {
            if ($(this).val() == '') {
                $(this).parent().find('span').show();
            }
        });
        $(document).on('keyup', '.pbuilder_image_input input', function () {
            thickboxId = '#' + $(this).attr('id') + '_holder';
            imgurl = $(this).val();
            var ww = $(thickboxId).width();
            var hh = $(thickboxId).height();
            if ($(thickboxId).hasClass('pbuilder_background_holder')) {
                $(thickboxId).css('background', 'url(' + imgurl + ') repeat');
            }
            else {
                $(thickboxId).html('<img style="max-width:' + ww + 'px; max-height:' + hh + 'px;" src="' + imgurl + '" alt="" />');
            }
            pbuilderContolChange($jq, $(this));
        });
        window.send_to_editor = function (html) {
            if (typeof formfield != 'undefined') {
                var img_pos = html.indexOf('<img');
                if (img_pos > 0)
                    html = html.substring(img_pos);
                img_pos = html.indexOf('>');
                if (img_pos > 0)
                    html = html.substring(0, img_pos + 1);
                while (html.indexOf('\\"') > - 1)
                    html = html.replace('\\"', '"');
                var $jhtml = $(html);
                var imgurl = $jhtml.attr('src');
                $('#' + formfield).parent().find('span').hide();
                $('#' + formfield).val(imgurl);
                var ww = $(thickboxId).width();
                var hh = $(thickboxId).height();
                if ($(thickboxId).hasClass('pbuilder_background_holder')) {
                    $(thickboxId).css('background', 'url(' + imgurl + ') repeat');
                }
                else {
                    $(thickboxId).html('<img style="max-width:' + ww + 'px; max-height:' + hh + 'px;" src="' + imgurl + '" alt="" />');
                }
                tb_remove();
                pbuilderContolChange($jq, $('#' + formfield));
            }
            else {
                // mce
                tinymce.editors[0].execCommand('mceInsertContent', false, html);
                tinymce.editors[1].execCommand('mceInsertContent', false, html);
                tinymce.editors.pbuilder_editor.execCommand('mceInsertContent', false, html);
            }
        }
//				Zoom toggle
        $('.pbuilder_toggle_zoom_trigger').click(function () {
            $(this).toggleClass('active');
            if ($(this).hasClass('active')) {
                $('#pbuilder_body_frame').css({'transform': 'scale(0.5,0.5)', '-webkit-transform': 'scale(0.5,0.5)', 'height': '200%', 'top': '-50%'});
            } else {
                $('#pbuilder_body_frame').css({'transform': 'scale(1,1)', '-webkit-transform': 'scale(1,1)', 'height': '100%', 'top': '0'});
            }
        });
        /* Shortcode sortable control */
        $(document).on('click', '.pbuilder_sortable_add', function (e) {
            e.preventDefault();
            var html = '';
            var name = $(this).closest('.pbuilder_sortable_holder').attr('data-name');
            var item_name = $(this).closest('.pbuilder_sortable_holder').attr('data-iname');
            var $smenu = $(this).parent().parent();
            while (!$smenu.hasClass('pbuilder_shortcode_menu'))
                $smenu = $smenu.parent();
            var itemId = parseInt($smenu.attr('data-modid'));
            var itemSh = $smenu.attr('data-shortcode');
            var shortcodeJSON = {};
            if (typeof pbuilder_shortcodes[itemSh]['options'][name] == 'undefined') {
                for (var x in pbuilder_shortcodes[itemSh]['options']) {
                    if (typeof pbuilder_shortcodes[itemSh]['options'][x]['options'] != 'undefined' && typeof pbuilder_shortcodes[itemSh]['options'][x]['options'][name] != 'undefined') {
                        shortcodeJSON = $.extend(true, {}, pbuilder_shortcodes[itemSh]['options'][x]['options'][name]);
                    }
                }
            }
            else {
                shortcodeJSON = $.extend(true, {}, pbuilder_shortcodes[itemSh]['options'][name]);
            }
            if (typeof pbuilder_items['items'][itemId]['options'][name]['items'] == 'undefined') {
                pbuilder_items['items'][itemId]['options'][name]['items'] = {};
                pbuilder_items['items'][itemId]['options'][name]['order'] = {};
            }
            var count = 0;
            while (typeof pbuilder_items['items'][itemId]['options'][name]['items'][count] != 'undefined' && pbuilder_items['items'][itemId]['options'][name]['items'][count] != '')
                count++;
            var pos = 0;
            while (typeof pbuilder_items['items'][itemId]['options'][name]['order'][pos] != 'undefined')
                pos++;
            pbuilder_items['items'][itemId]['options'][name]['order'][pos] = count;
            html += '<div class="pbuilder_sortable_item pbuilder_collapsible" data-sortid="' + count + '" data-sortname="' + name + '"><div class="pbuilder_gradient pbuilder_sortable_handle pbuilder_collapsible_header">' + item_name + ' ' + count + ' - <span class="pbuilder_sortable_delete">delete</span>, <span class="pbuilder_sortable_clone">clone</span><span class="pbuilder_collapse_trigger">+</span></div><div class="pbuilder_collapsible_content">';
            pbuilder_items['items'][itemId]['options'][name]['items'][count] = {};
            for (var x in shortcodeJSON['options']) {
                var newControl = new pbuilderControl('fsort-' + count + '-' + x, shortcodeJSON['options'][x]);
                html += newControl.html();
                pbuilder_items['items'][itemId]['options'][name]['items'][count][x] = (typeof shortcodeJSON['options'][x]['std'] != 'undefined' ? shortcodeJSON['options'][x]['std'] : '');
            }
            html += '<div style="clear:both;"></div></div></div>';
            $(this).parent().find('.pbuilder_sortable').append(html);
            pbuilderRefreshControls($jq, $(this).parent());
            pbuilderHideControls($('false'), true, $(this).parent().find('.pbuilder_sortable_item'));
            $('.pbuilder_shortcode_menu').trigger('fchange');
        });
        //		Sortable Clone
        $(document).on('click', '.pbuilder_sortable_clone', function () {
            var $sortitem = $(this).parent().parent();
            var id = parseInt($sortitem.attr('data-sortid'));
            var name = $sortitem.attr('data-sortname');
            var itemId = parseInt($('.pbuilder_shortcode_menu').attr('data-modid'));
            var $sortable = $sortitem.parent();
            var newId = 0;
            while (typeof pbuilder_items['items'][itemId]['options'][name]['items'][newId] != 'undefined' && pbuilder_items['items'][itemId]['options'][name]['items'][newId] != '')
                newId++;
            pbuilder_items['items'][itemId]['options'][name]['items'][newId] = {};
            pbuilder_items['items'][itemId]['options'][name]['items'][newId] = $.extend(pbuilder_items['items'][itemId]['options'][name]['items'][newId], pbuilder_items['items'][itemId]['options'][name]['items'][id]);
            //		regenerate controls
            var html = '';
            var item_name = $(this).closest('.pbuilder_sortable_holder').attr('data-iname');
            var itemSh = $('.pbuilder_shortcode_menu').attr('data-shortcode');
            var shortcodeJSON = {};
            if (typeof pbuilder_shortcodes[itemSh]['options'][name] == 'undefined') {
                for (var x in pbuilder_shortcodes[itemSh]['options']) {
                    if (typeof pbuilder_shortcodes[itemSh]['options'][x]['options'] != 'undefined' && typeof pbuilder_shortcodes[itemSh]['options'][x]['options'][name] != 'undefined') {
                        shortcodeJSON = $.extend(true, {}, pbuilder_shortcodes[itemSh]['options'][x]['options'][name]);
                    }
                }
            }
            else {
                shortcodeJSON = $.extend(true, {}, pbuilder_shortcodes[itemSh]['options'][name]);
            }
            html += '<div class="pbuilder_sortable_item pbuilder_collapsible" data-sortid="' + newId + '" data-sortname="' + name + '"><div class="pbuilder_gradient pbuilder_sortable_handle pbuilder_collapsible_header">' + item_name + ' ' + newId + ' - <span class="pbuilder_sortable_delete">delete</span>, <span class="pbuilder_sortable_clone">clone</span><span class="pbuilder_collapse_trigger">+</span></div><div class="pbuilder_collapsible_content">';
            var bay = shortcodeJSON['options'];
            for (var x in bay) {
                bay[x]['std'] = pbuilder_items['items'][itemId]['options'][name]['items'][newId][x];
                var newControl = new pbuilderControl('fsort-' + newId + '-' + x, bay[x]);
                html += newControl.html();
            }
            html += '<div style="clear:both;"></div></div></div>';
            $(html).insertAfter($sortitem);
            // 		recalculate order
            var orderStr = '';
            for (key in pbuilder_items['items'][itemId]['options'][name]['order']) {
                orderStr += (pbuilder_items['items'][itemId]['options'][name]['order'][key] + 'ord,');
            }
            orderStr = orderStr.split(id + 'ord');
            orderStr[0] = orderStr[0] + id + 'ord,';
            orderStr[1] = newId + orderStr[1];
            var orderStrFin = [];
            orderStrFin = (orderStr[0] + orderStr[1]).substr(0, orderStr[0].length + orderStr[1].length - 1).split('ord,');
            var tempObj = {};
            for (i = 0; i < orderStrFin.length; i++) {
                tempObj['"' + i + '"'] = parseInt(orderStrFin[i].replace('"', ''));
            }
            pbuilder_items['items'][itemId]['options'][name]['order'] = tempObj;
            //		trigger refreshes
            pbuilderRefreshControls($jq, $(this).parent());
            pbuilderHideControls($('false'), true, $(this).closest().find('.pbuilder_sortable_item'));
            $('.pbuilder_shortcode_menu').trigger('fchange');
        });
        $(document).on('click', '.pbuilder_sortable_delete', function () {
            var $sortitem = $(this).parent().parent();
            var id = parseInt($sortitem.attr('data-sortid'));
            var name = $sortitem.attr('data-sortname');
            var itemId = parseInt($('.pbuilder_shortcode_menu').attr('data-modid'));
            var $sortable = $sortitem.parent();
            $sortitem.remove();
            delete pbuilder_items['items'][itemId]['options'][name]['items'][id];
            delete pbuilder_items['items'][itemId]['options'][name]['order'];
            pbuilder_items['items'][itemId]['options'][name]['order'] = {};
            $sortable.children('.pbuilder_sortable_item').each(function (index) {
                pbuilder_items['items'][itemId]['options'][name]['order'][index] = parseInt($(this).attr('data-sortid'));
            });

            $('.pbuilder_shortcode_menu').trigger('fchange');
        });
        /* Shortcode collapsible control */
        $(document).on('click', '.pbuilder_collapse_trigger', function () {
            var $content = $(this).parent().parent().children('.pbuilder_collapsible_content');
            if (!$(this).hasClass('active')) {
                $(this).html('-').addClass('active');
                $content.show();
            }
            else {
                $(this).html('+').removeClass('active');
                $content.hide();
            }
            pbuilderRefreshControls($jq, $(this).closest('.pbuilder_control'));
        });
        /* Shortcode colorpicker control */
        $(document).on('click', '.pbuilder_color_display', function () {
            var $ctrl = $(this).closest('.pbuilder_color_wrapper');
            $ctrl.find('.pbuilder_colorpicker').css('margin-left', -$ctrl.position().left + 10).addClass('active').show();

            setTimeout(function () {
                pbuilderRefreshControls($jq, $(this).parent().find('.pbuilder_color'))
            }, 10);
            $(this).parent().find('.pbuilder_color').trigger('focus');
        });
        $(document).on('mouseenter', '.pbuilder_color_wrapper', function () {
            $(this).find('.pbuilder_colorpicker').data('hover', true);
        });
        $(document).on('mouseleave', '.pbuilder_color_wrapper', function () {
            $(this).find('.pbuilder_colorpicker').data('hover', false);
        });
        $(document).on('mouseenter', '.pbuilder_colorpicker, .pbuilder_number_bar_wrapper', function () {
            $(this).closest('.pbuilder_control').find('.pbuilder_number_button').data('hover', true);
        });
        $(document).on('mouseleave', '.pbuilder_colorpicker, .pbuilder_number_bar_wrapper', function () {
            $(this).closest('.pbuilder_control').find('.pbuilder_number_button').data('hover', false);
        });
        $('body').click(function () {
            $('.pbuilder_colorpicker.active').each(function () {
                if (!$(this).data('hover')) {
                    $(this).removeClass('active').hide();
                    pbuilderRefreshControls($jq, $('false'));
                }
            });
            $('.pbuilder_number_button.active').each(function () {
                if (!$(this).data('hover')) {
                    $(this).removeClass('active').closest('.pbuilder_control').find('.pbuilder_number_bar_wrapper').hide();
                    pbuilderRefreshControls($jq, $('false'));
                }
            });
        });
        $jq('body').on('mouseup', function () {
            $('body').trigger('mouseup');
        });
        $jq('body').on('click', function () {
            $('body').trigger('click');
        });
        /* Shortcode number control */
        $(document).on('keyup', '.pbuilder_number_amount', function () {
            var $this = $(this);
            $this.closest('.pbuilder_control').find('.pbuilder_number_bar').slider('value', parseInt($this.val()));
        });
        $(document).on('click', '.pbuilder_number_button', function () {
            var $this = $(this);
            if (!$(this).hasClass('active')) {
                $(this).addClass('active');
                var $ctrl = $this.closest('.pbuilder_control');
                $ctrl.find('.pbuilder_number_bar_wrapper').css('margin-left', -$ctrl.position().left + 10).show();
            }
            else {
                $(this).removeClass('active');
                $this.closest('.pbuilder_control').find('.pbuilder_number_bar_wrapper').hide();
            }
        });
        /* Shortcode change */
        $(document).on('fchange', '.pbuilder_shortcode_menu', function () {
            window.pbuilder_changes_made = true;
            if ($('.pbuilder_shortcode_menu:first').hasClass('pbuilder_rowedit_menu')) {
                var id = parseInt($(this).attr('data-modid'));
                var $row = $jq('.pbuilder_row[data-rowid=' + id + ']:first');
                var options = pbuilder_items['rows'][id]['options'];

                var margin_positions=['top','right','bottom','left'];
                for(p in margin_positions){
                  options['mp_margin_'+margin_positions[p]]=$('input[name=mp_margin_'+margin_positions[p]).val();
                  options['mp_padding_'+margin_positions[p]]=$('input[name=mp_padding_'+margin_positions[p]).val();
                }
        
                options['margin_padding']=options['mp_margin_top']+'|'+options['mp_margin_right']+'|'+options['mp_margin_bottom']+'|'+options['mp_margin_left']+'|'+options['mp_padding_top']+'|'+options['mp_padding_right']+'|'+options['mp_padding_bottom']+'|'+options['mp_padding_left'];
                
                
                if(typeof options['border'] === 'undefined' && typeof options['border_color'] !== 'undefined'){
                  options['border']='false|'+options['border_width']+'|'+options['border_style']+'|'+options['border_color']+'|0|solid|#000000|0|solid|#000000|0|solid|#000000|0|solid|#000000';
                } else {
                  if(typeof options['pbuilder_border_advanced'] === 'undefined') options['pbuilder_border_advanced']='false';
                  var border_positions=['simple','top','right','bottom','left'];
                  options['pbuilder_border_advanced']=$('input[name=pbuilder_border_advanced]').val();
                  for(p in border_positions){
                    options['mp_border_'+border_positions[p]+'_width']=$('input[name=mp_border_'+border_positions[p]+'_width]').val();
                    options['mp_border_'+border_positions[p]+'_style']=$('input[name=mp_border_'+border_positions[p]+'_style]').val();
                    options['mp_border_'+border_positions[p]+'_color']=$('input[name=mp_border_'+border_positions[p]+'_color]').val();
                  }
  
                  options['border']=options['pbuilder_border_advanced']+'|'+options['mp_border_simple_width']+'|'+options['mp_border_simple_style']+'|'+options['mp_border_simple_color']+'|'+options['mp_border_top_width']+'|'+options['mp_border_top_style']+'|'+ options['mp_border_top_color']+'|'+options['mp_border_right_width']+'|'+options['mp_border_right_style']+'|'+options['mp_border_right_color']+'|'+options['mp_border_bottom_width']+'|'+options['mp_border_bottom_style']+'|'+options['mp_border_bottom_color']+'|'+options['mp_border_left_width']+'|'+options['mp_border_left_style']+'|'+options['mp_border_left_color'];
                }
                
                pbuilder_items['rows'][id]['options']['border']=options['border'];
                pbuilder_items['rows'][id]['options']['margin_padding']=options['margin_padding'];
                pbuilderRowChange($row, options);
             } else if ($('.pbuilder_shortcode_menu:first').hasClass('pbuilder_columnedit_menu')) {
                var row = parseInt($(this).attr('data-modid'));
                var column = parseInt($(this).attr('data-columnid'));
                var $column = $jq('.pbuilder_row[data-rowid=' + row + '] .pbuilder_column[data-colnumber=' + column + ']');
                var options = pbuilder_items['columns'][row][column]['options'];
        
                var margin_positions=['top','right','bottom','left'];
                for(p in margin_positions){
                  options['mp_margin_'+margin_positions[p]]=$('input[name=mp_margin_'+margin_positions[p]).val();
                  options['mp_padding_'+margin_positions[p]]=$('input[name=mp_padding_'+margin_positions[p]).val();
                }
        
                options['margin_padding']=options['mp_margin_top']+'|'+options['mp_margin_right']+'|'+options['mp_margin_bottom']+'|'+options['mp_margin_left']+'|'+options['mp_padding_top']+'|'+options['mp_padding_right']+'|'+options['mp_padding_bottom']+'|'+options['mp_padding_left'];
                
                if(typeof options['border'] === 'undefined' && typeof options['border_color'] !== 'undefined'){
                  options['border']='false|'+options['border_width']+'|'+options['border_style']+'|'+options['border_color']+'|0|solid|#000000|0|solid|#000000|0|solid|#000000|0|solid|#000000';
                } else {
                  if(typeof options['pbuilder_border_advanced'] === 'undefined') options['pbuilder_border_advanced']='false';
                  var border_positions=['simple','top','right','bottom','left'];
                  options['pbuilder_border_advanced']=$('input[name=pbuilder_border_advanced]').val();
                  for(p in border_positions){
                    options['mp_border_'+border_positions[p]+'_width']=$('input[name=mp_border_'+border_positions[p]+'_width]').val();
                    options['mp_border_'+border_positions[p]+'_style']=$('input[name=mp_border_'+border_positions[p]+'_style]').val();
                    options['mp_border_'+border_positions[p]+'_color']=$('input[name=mp_border_'+border_positions[p]+'_color]').val();
                  }
  
                  options['border']=options['pbuilder_border_advanced']+'|'+options['mp_border_simple_width']+'|'+options['mp_border_simple_style']+'|'+options['mp_border_simple_color']+'|'+options['mp_border_top_width']+'|'+options['mp_border_top_style']+'|'+ options['mp_border_top_color']+'|'+options['mp_border_right_width']+'|'+options['mp_border_right_style']+'|'+options['mp_border_right_color']+'|'+options['mp_border_bottom_width']+'|'+options['mp_border_bottom_style']+'|'+options['mp_border_bottom_color']+'|'+options['mp_border_left_width']+'|'+options['mp_border_left_style']+'|'+options['mp_border_left_color'];
                }
                
                pbuilder_items['columns'][row][column]['options']['border']=options['border'];
                pbuilder_items['columns'][row][column]['options']['margin_padding']=options['margin_padding'];
        
                pbuilderColumnChange($column, options);
            } else {
                var id = parseInt($(this).attr('data-modid'));
                var $module = $jq('.pbuilder_module[data-modid=' + id + ']:first');
                var f = pbuilder_items['items'][id]['f'];
                var holder = $module.find('.pbuilder_module_content:first');
                var options = pbuilder_items['items'][id]['options'];


                var margin_positions=['top','right','bottom','left'];
                for(p in margin_positions){
                  options['mp_margin_'+margin_positions[p]]=$('input[name=mp_margin_'+margin_positions[p]).val();
                  options['mp_padding_'+margin_positions[p]]=$('input[name=mp_padding_'+margin_positions[p]).val();
                }
        
                options['margin_padding']=options['mp_margin_top']+'|'+options['mp_margin_right']+'|'+options['mp_margin_bottom']+'|'+options['mp_margin_left']+'|'+options['mp_padding_top']+'|'+options['mp_padding_right']+'|'+options['mp_padding_bottom']+'|'+options['mp_padding_left'];
                pbuilder_items['items'][id]['options']['margin_padding']=options['margin_padding'];


                if(typeof options['border'] !== 'undefined'){

                  if(typeof options['pbuilder_border_advanced'] === 'undefined') options['pbuilder_border_advanced']='false';

                  var border_positions=['simple','top','right','bottom','left'];
                  options['pbuilder_border_advanced']=$('input[name=pbuilder_border_advanced]').val();
                  for(p in border_positions){
                    options['mp_border_'+border_positions[p]+'_width']=$('input[name=mp_border_'+border_positions[p]+'_width]').val();
                    options['mp_border_'+border_positions[p]+'_style']=$('input[name=mp_border_'+border_positions[p]+'_style]').val();
                    options['mp_border_'+border_positions[p]+'_color']=$('input[name=mp_border_'+border_positions[p]+'_color]').val();
                  }

                  options['border']=options['pbuilder_border_advanced']+'|'+options['mp_border_simple_width']+'|'+options['mp_border_simple_style']+'|'+options['mp_border_simple_color']+'|'+options['mp_border_top_width']+'|'+options['mp_border_top_style']+'|'+ options['mp_border_top_color']+'|'+options['mp_border_right_width']+'|'+options['mp_border_right_style']+'|'+options['mp_border_right_color']+'|'+options['mp_border_bottom_width']+'|'+options['mp_border_bottom_style']+'|'+options['mp_border_bottom_color']+'|'+options['mp_border_left_width']+'|'+options['mp_border_left_style']+'|'+options['mp_border_left_color'];
                  pbuilder_items['items'][id]['options']['border']=options['border'];
                }


                pbuilderGetShortcode(f, holder, options);
            }

        });
        $(document).on('click', '.ui-draggable', function (e) {
            e.preventDefault();
        });

        var scrollwhiledragging;
        var scrollwhiledraggingdirection='none';
            $(iDocument).on('mousedown', '.pbuilder_drag_handle, .pbuilder_drag', function (e) {
          e.preventDefault();
                $(iDocument).data('FRBdragHandleSwitch', true);
          scrollwhiledragging=setInterval(function(){
             current = jQuery(iDocument).scrollTop();
                   if(typeof scrollwhiledraggingspeed !== 'undefined') {
              $(iDocument).scrollTop(current - scrollwhiledraggingspeed);
             }
          }, 50);


            if ($('#pbuilder_body_frame').contents().find('body:first > .frb_drag_placeholder_element').length > 0) {
                $jq('.frb_drag_placeholder_element:first').css('display', 'block');

            } else {

                $jq('body:first').append('<div class="frb_drag_placeholder_element"></div>');


            }
            if ($(this).hasClass('pbuilder_drag_handle')) {
                $jq('.frb_drag_placeholder_element:first').html('"Row ID = ' + $(this).closest('.pbuilder_row').attr('data-rowid') + '"');
            } else {
                $jq('.frb_drag_placeholder_element:first').html('"' + $(this).closest('.pbuilder_module').attr('data-shortcode') + '"');
            }
            var xy = FRBpointerEventToXY(e);
            $jq('.frb_drag_placeholder_element:first').css({'left': xy.x, 'top': xy.y});

        });

        $(iDocument).on('mouseup', function (e) {
            $(iDocument).data('FRBdragHandleSwitch', false);
			clearInterval(scrollwhiledragging);

            $jq('.frb_drag_placeholder_element:first').css('display', 'none');
            //$(iDocument).off('mousemove');
        });


        $(iDocument).on('mousemove', 'body', function (e) {
          if ($(iDocument).data('FRBdragHandleSwitch') !== true) {
			return;
          }


          if (typeof e.originalEvent.y == 'undefined') {
            mouse_y = e.originalEvent.clientY;
          } else {
            mouse_y = e.originalEvent.clientY;
          }



          bottom_gap = 100;

		  if (mouse_y > ($(document).height() - bottom_gap)) {
			scrollwhiledraggingspeed=($(document).height() - bottom_gap) - mouse_y;
          } else if (mouse_y < bottom_gap) {
			scrollwhiledraggingspeed=bottom_gap-mouse_y;
          } else {
			 scrollwhiledraggingspeed=0;
		  }


        }); // mousemove
    }
    var undo = function () {
        var curID = 0;
        if (typeof localStorage.getItem('curID') != 'undefined' && localStorage.getItem('curID') != null) {
            curID = localStorage.getItem('curID');
        } else if (typeof localStorage.getItem('lastID') != 'undefined' && localStorage.getItem('lastID') != null) {
            curID = localStorage.getItem('lastID');
        }
        if (curID > 2) {
            curID--;
            var cla = document.getElementById("fa_undo");
            cla.style.color = '#ffffff';
            var items = localStorage.getItem(curID);
            var json_items = JSON.parse(localStorage.getItem("json_" + curID));
            $('#pbuilder_body_frame').contents().find('#pbuilder_wrapper').html(items);
            pbuilder_items = json_items;
            localStorage.setItem('curID', curID);
        }
        else {
            var cla = document.getElementById("fa_undo");
            cla.style.color = '#555';
            var claa = document.getElementById("fa_redo");
            claa.style.color = '#ffffff';
        }
    }
    var redo = function () {
        curID = localStorage.getItem('curID');
        if (curID <= localStorage.getItem('lastID')) {
            curID++;
            var cla = document.getElementById("fa_undo");
            cla.style.color = '#ffffff';
            var items = localStorage.getItem(curID);
            var json_items = JSON.parse(localStorage.getItem("json_" + curID));
            var it = localStorage.getItem(curID);
            if (it != null) {
                pbuilder_items = json_items;
                $('#pbuilder_body_frame').contents().find('#pbuilder_wrapper').html(it);
                //$('#pbuilder_body_frame').contentWindow.location.reload(true);
                localStorage.setItem('curID', curID);
            }
            else {
                var claa = document.getElementById("fa_redo");
                claa.style.color = '#555';
            }
        }
    }
    var checkHTML = function () {
        var mii = jQuery.now();
        var lockTime = localStorage.getItem('lockTime', mii + 1000);
        if (mii > lockTime) {
            localStorage.setItem('lockTime', mii + 1000);
            var lastID = 0;
            if (typeof localStorage.getItem('lastID') != 'undefined' && localStorage.getItem('lastID') != null) {
                lastID = localStorage.getItem('lastID');
            }
            var curID = 0;
            if (typeof localStorage.getItem('curID') != 'undefined' && localStorage.getItem('curID') != null) {
                curID = localStorage.getItem('curID');
            }
            if (curID < lastID) {
                for (d = curID--; d <= lastID; d++) {
                    localStorage.removeItem(d);
                }
                lastID = curID;
            }
            var iframe = document.getElementById('pbuilder_body_frame');
            var innerDoc = iframe.contentDocument || iframe.contentWindow.document;
            if (innerDoc != null && innerDoc.getElementById('pbuilder_wrapper') != null)
            {
                var frame = innerDoc.getElementById('pbuilder_wrapper').innerHTML;
                lastID++;
                curID++;
                localStorage.setItem(lastID, frame);
                localStorage.setItem('curID', curID);
                localStorage.setItem('lastID', lastID);
                var codedJSON = JSON.stringify(pbuilder_items, jsonMod);
                localStorage.setItem('json_' + lastID, codedJSON);
            }
        }
        pbuilderRefreshControls();
    }
    var FRBpointerEventToXY = function (e) {
        var out = {x: 0, y: 0};
        if (e.type == 'touchstart' || e.type == 'touchmove' || e.type == 'touchend' || e.type == 'touchcancel') {
            var touch = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];
            out.x = touch.pageX;
            out.y = touch.pageY;
        } else if (e.type == 'mousedown' || e.type == 'mouseup' || e.type == 'mousemove' || e.type == 'mouseover' || e.type == 'mouseout' || e.type == 'mouseenter' || e.type == 'mouseleave') {
            out.x = e.pageX;
            out.y = e.pageY;
        } else if (e.type == 'MSPointerDown' || e.type == 'MSPointerMove' || e.type == 'MSPointerUp') {
            var touch = e.originalEvent;
            out.x = touch.pageX;
            out.y = touch.pageY;
        }
        return out;
    };
})(jQuery);
var customvalues = {};
var hiddenvalues = {};
function pbuilderRemoveCustomField(ele, ind, modid) {
    var formcode = typeof jQuery(ele).attr("form-code") != "undefined";
    if (formcode) {
        if (typeof customvalues[modid] == "undefined")
            customvalues[modid] = {};
        customvalues[modid]['customfield' + ind] = pbuilder_items['items'][modid]['options']['customfield' + ind];
        customvalues[modid]['customfieldlabel' + ind] = pbuilder_items['items'][modid]['options']['customfieldlabel' + ind];
        customvalues[modid]['customfieldtype' + ind] = pbuilder_items['items'][modid]['options']['customfieldtype' + ind];
        customvalues[modid]['customfieldrequired' + ind] = pbuilder_items['items'][modid]['options']['customfieldrequired' + ind];
        customvalues[modid]['customfielderror' + ind] = pbuilder_items['items'][modid]['options']['customfielderror' + ind];
    }
    //pbuilder_hideifs['parents']['optin']['disablename']["customfield"+ind] = null;
    pbuilder_hideifs['parents']['optin']['formstyle']["customfield" + ind] = null;
    pbuilder_hideifs['parents']['optin']['customfields']["customfield" + ind] = null;
    pbuilder_hideifs['children']['optin']["customfield" + ind] = null;
    jQuery(ele).parent().parent().remove();
    delete pbuilder_items['items'][modid]['options']['customfield' + ind];
    delete pbuilder_items['items'][modid]['options']['customfieldlabel' + ind];
    delete pbuilder_items['items'][modid]['options']['customfieldtype' + ind];
    delete pbuilder_items['items'][modid]['options']['customfieldrequired' + ind];
    delete pbuilder_items['items'][modid]['options']['customfielderror' + ind];
    if (!formcode) {
        jQuery("#pbuilder_input_formurl").trigger("keyup");
    } else {
        if (typeof customvalues[modid] == "undefined")
            customvalues[modid] = {};
    }
}
function pbuilderRemoveHiddenField(ele, ind, modid) {
    var formcode = typeof jQuery(ele).attr("form-code") != "undefined";
    if (formcode) {
        if (typeof hiddenvalues[modid] == "undefined")
            hiddenvalues[modid] = {};
        hiddenvalues[modid]['hiddenfield' + ind] = pbuilder_items['items'][modid]['options']['hiddenfield' + ind];
        hiddenvalues[modid]['hiddenfieldname' + ind] = pbuilder_items['items'][modid]['options']['hiddenfieldname' + ind];
        hiddenvalues[modid]['hiddenfieldtype' + ind] = pbuilder_items['items'][modid]['options']['hiddenfieldtype' + ind];
    }
    //pbuilder_hideifs['parents']['optin']['disablename']["hiddenfield"+ind] = null;
    pbuilder_hideifs['parents']['optin']['formstyle']["hiddenfield" + ind] = null;
    pbuilder_hideifs['parents']['optin']['hiddenfields']["hiddenfield" + ind] = null;
    pbuilder_hideifs['children']['optin']["hiddenfield" + ind] = null;
    jQuery(ele).parent().parent().remove();
    delete pbuilder_items['items'][modid]['options']['hiddenfield' + ind];
    delete pbuilder_items['items'][modid]['options']['hiddenfieldname' + ind];
    delete pbuilder_items['items'][modid]['options']['hiddenfieldtype' + ind];
    if (!formcode)
        jQuery("#pbuilder_input_formurl").trigger("keyup")
}
function setcookie(name, value, days) {
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        var expires = "; expires=" + date.toGMTString();
    }
    else
        var expires = "";
    document.cookie = name + "=" + value + expires + "; path=/";
}
function getcookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ')
            c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) == 0)
            return c.substring(nameEQ.length, c.length);
    }
    return null;
}
function removecookie(name) {
    setcookie(name, "", -1);
}
jQuery('#pbuilder_body_frame').load(function () {
    var StickToTopDiv = jQuery('#pbuilder_body_frame').contents().find('.pbuilder_row_stick_top');
    var StickToTopDivAn = jQuery('#pbuilder_body_frame').contents().find('.stick-top-div');
    var StickToBottomDiv = jQuery('#pbuilder_body_frame').contents().find('.pbuilder_row_stick_bottom');
    if (StickToTopDiv.length > 0) {
        var height_of_stick_div = StickToTopDiv.height();
        StickToTopDivAn.css("height", height_of_stick_div+"px");
        var $IsAdmin = jQuery('#pbuilder_body_frame').contents().find('#wpadminbar');
        if ($IsAdmin.length) {
            StickToTopDiv.css('margin-top', $IsAdmin.height() + 'px');
        }
        jQuery('body').bind("DOMSubtreeModified", FixTop(StickToTopDiv));
    } else {
        jQuery('#pbuilder_body_frame').contents().find('.pb_fix_top').remove();
    }
   
});
