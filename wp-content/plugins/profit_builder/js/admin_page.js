(function ($) {
    $(document).ready(function () {
        pbuilderControlsInit();
        pbuilderRefreshControls();

    });

    var keyTimeout;
    /*
     function pbuilderContolChange($control, timeout = false) {
     var name = $control.attr('name');
     var modid = parseInt($('.pbuilder_shortcode_menu').attr('data-modid'));
     var $module = $('.pbuilder_module[data-modid='+modid+']');
     if(name.search('fsort') == -1) {
     pbuilder_items['items'][modid]['options'][name] = $control.val();
     }
     else {
     var subname = name.substr(name.search('-')+1);
     name = subname.substr(subname.search('-')+1);
     var sortid = parseInt(subname.substr(0,subname.search('-')));
     var $parent = $control.parent();
     while(!$parent.hasClass('pbuilder_sortable_item')) $parent = $parent.parent();
     sortname = $parent.attr('data-sortname');
     pbuilder_items['items'][modid]['options'][sortname]['items'][sortid][name] = $control.val();
     }
     if(timeout) {
     window.clearTimeout(keyTimeout);
     keyTimeout = window.setTimeout(function(){
     $('.pbuilder_shortcode_menu').trigger('fchange');
     },1000);
     }
     else {
     $('.pbuilder_shortcode_menu').trigger('fchange');
     }

     pbuilderRefreshControls();
     }*/

    /*  Refresh Profit Builder Controls  */

    function pbuilderRefreshControls() {


        /* UI slider for number controles */
        $(".pbuilder_number_bar").each(function () {
            if (!$(this).hasClass('ui-slider')) {
                var min = parseInt($(this).attr('data-min'));
                var max = parseInt($(this).attr('data-max'));
                var std = parseInt($(this).attr('data-std'));
                var unit = $(this).attr('data-unit');

                $(this).slider({
                    min: min,
                    max: max,
                    value: std,
                    range: "min",
                    slide: function (event, ui) {
                        $(this).closest('.pbuilder_control').find(".pbuilder_number_amount").val(ui.value + ' ' + unit);
                    },
                    change: function (event, ui) {
                        var $input = $(this).parent().find(".pbuilder_number_amount");
                        //pbuilderContolChange($input);

                    }
                });
            }
        });

        /* Sortable init on new controles */
        $('.pbuilder_sortable').each(function () {
            if (!$(this).hasClass('ui-sortable')) {
                $(this).sortable({
                    items: '.pbuilder_sortable_item',
                    handle: '.pbuilder_sortable_handle',
                    stop: function (event, ui) {
                        var name = $(this).parent().attr('data-name');
                        var itemId = parseInt($('.pbuilder_shortcode_menu').attr('data-modid'));
                        pbuilder_items['items'][itemId]['options'][name]['order'] = {};
                        $(this).children('.pbuilder_sortable_item').each(function (index) {
                            pbuilder_items['items'][itemId]['options'][name]['order'][index] = parseInt($(this).attr('data-sortid'));
                        });
                        $('.pbuilder_shortcode_menu').trigger('fchange');
                    }
                });
            }
        });




        var modid = parseInt($('.pbuilder_shortcode_menu').attr('data-modid'));
        var $module = $('.pbuilder_module[data-modid=' + modid + ']');
        $('.pbuilder_hidable').each(function () {
            var hideBool = false;
            var hideName = $(this).find('.pbuilder_hidable_control').attr('name');

            var hideArr = hideIfs[hideName];
            for (var x in hideArr) {
                var $hideObj = $('.pbuilder_controls_wrapper').find('[name=' + x + ']');
                if (hideArr[x].indexOf($hideObj.val()) != -1) {
                    hideBool = true;
                    break;
                }
            }
            if (hideBool)
                $(this).hide();
            else
                $(this).show();
        });

        /* mCustomScrollbar when new items are created */
		/* The scroll bar will be added when opening the dropdown to reduce page load time - line 668 below */
		/*
        $('.pbuilder_select ul').each(function () {
			if (!$(this).hasClass('fmCustomScrollbar')) {
				$(this).fmCustomScrollbar({mouseWheelPixels: 150});
			}
			else {
				$(this).fmCustomScrollbar('update');
			}
		});*/
    }

    /*  Activate Profit Builder Controls  */
    function pbuilderControlsInit() {
        var ctime;
        /* Shortcode color control */
        $('.pbuilder_color').each(function () {
            var $this = $(this);
            $(this).parent().find('.pbuilder_color_display span').css('background', $(this).val());
            $(this).fbiris({
                width: 228,
                target: $(this).parent().find('.pbuilder_colorpicker'),
                palettes: ['', '#1abc9c', '#16a085', '#3498db', '#2980b9', '#9b59b6', '#8e44ad', '#34495e', '#2c3e50', '#e67e22', '#d35400', '#e74c3c', '#c0392b', '#ecf0f1', '#bdc3c7', '#ffffff', '#000000'],
                change: function (event, ui) {
                    var $thisChange = $(this);
                    $(this).parent().find('.pbuilder_color_display span').css('background-color', ui.color.toString());
                }
            });

        });
        /* Shortcode select control */

        $(document).on('mouseenter', '.pbuilder_select', function () {
            $(this).data('hover', true);
        });

        $(document).on('mouseleave', '.pbuilder_select', function () {
            $(this).data('hover', false);
        });



        $(document).on('click', '.template_button_install', function (e) {
            console.log('Install Template '+ajaxurl);
            e.preventDefault();
            var $this = $(this);
            if ($this.hasClass("template_installed"))
                return;

            var url = escape($this.attr("data-zip"));
            var data = {
                action: 'pbuilder_admin_template_install',
                url: url,
                template_name: escape($this.attr("template-name")),
                template_version: escape($this.attr("template-version")),
                template_description: escape($this.attr("template-description")),
                template_category: escape($this.attr("template-category")),
                template_group: escape($this.attr("template-group")),
            }

            if (typeof window.pbuilder_saveajax != 'undefined') {
                window.pbuilder_saveajax.abort();
                $('.inProcess').next().stop(true).show().animate({'opacity': '0'});
                $('.inProcess').stop(true).animate({paddingRight: 20, paddingLeft: 20}, 200).removeClass('inProcess');
            }
            $this.stop(true).animate({paddingRight: 28, paddingLeft: 15}, 200).addClass('inProcess');
            $this.next().css({"float": "left"}).stop(true).show().animate({'opacity': '1'});
            window.pbuilder_saveajax = $.post(ajaxurl, data, function (response) {
                var json_data = JSON.parse(response);

                if (json_data.result == "success") {
                    AddTemplate(json_data.template.tempid, json_data.template.tempname);
                    //console.log(json_data.template);
                    //$this.removeClass("template_button_install");
                    $this.addClass("template_installed");
                    $this.html('Installed<img src="' + pbuilder_url + 'images/icons/check-green1.png" class="template_button_installed_check"  />');
                    $this.next().next(".template_button_update").show();
                }
                //console.log(response);
                $this.stop(true).animate({paddingRight: 20, paddingLeft: 20}, 200);
                $this.next().stop(true).show().animate({'opacity': '0'});
            });
        });

        $(document).on('click', '.template_button_update', function (e) {
            e.preventDefault();
            var $this = $(this);
            var url = escape($this.attr("data-zip"));
            var data = {
                action: 'pbuilder_admin_template_install',
                url: url,
                template_name: escape($this.attr("template-name")),
                template_version: escape($this.attr("template-version")),
                template_description: escape($this.attr("template-description")),
                template_category: escape($this.attr("template-category")),
                template_group: escape($this.attr("template-group")),
            }

            if (typeof window.pbuilder_saveajax != 'undefined') {
                window.pbuilder_saveajax.abort();
                $('.inProcess').next().stop(true).show().animate({'opacity': '0'});
                $('.inProcess').stop(true).animate({paddingRight: 20, paddingLeft: 20}, 200).removeClass('inProcess');
            }
            $this.stop(true).animate({paddingRight: 28, paddingLeft: 15}, 200).addClass('inProcess');
            $this.next().css({"float": "left"}).stop(true).show().animate({'opacity': '1'});
            window.pbuilder_saveajax = $.post(ajaxurl, data, function (response) {
                var json_data = JSON.parse(response);
                if (json_data.result == "success") {
                    $this.parent().find(".template_ver").html($this.attr("template-version"))
                }
                $this.stop(true).animate({paddingRight: 20, paddingLeft: 20}, 200);
                $this.next().stop(true).show().animate({'opacity': '0'}).remove();
                $this.remove();
            });
        });

        $(document).on('click', '.pbuilder_select span, .pbuilder_select .drop_button', function (e) {
            e.preventDefault();
            $parent = $(this).parent();
            if (!$parent.hasClass('active')) {
                $parent.addClass('active').find('ul, input').show();
            }
            else {
                $parent.removeClass('active').find('ul, input').val('').hide();
            }
            pbuilderRefreshControls();
        });
        $(document).on('click', '.pbuilder_select ul a', function (e) {
            e.preventDefault();
            var $parent = $(this).closest('.pbuilder_select');
            var $select = $('select[name=' + $parent.attr('data-name') + ']');
            $select.val($(this).attr('data-value')).change();
            $parent.find('span').html($(this).html());
            $parent.removeClass('active').find('ul, input').val('').hide();
            $parent.find('ul a.selected').removeClass('selected');
            $(this).addClass('selected');
            pbuilderRefreshControls();
            /*
             pbuilderContolChange($select);
             */
        });

        $(document).on('keyup', '.pbuilder_select input', function () {
            var inValue = $(this).val();
            if (inValue == '') {
                $(this).closest('.pbuilder_select').find('ul li').show();
            }
            else {
                $(this).closest('.pbuilder_select').find('ul li').each(function () {
                    if ($(this).html().toLowerCase().search(inValue) > -1) {
                        $(this).show();
                    }
                    else {
                        $(this).hide();
                    }
                });
            }
            $(this).closest('.pbuilder_select').find('ul').fmCustomScrollbar('update');
        });
        $('body').click(function () {
            $('.pbuilder_select.active').each(function () {
                if (!$(this).data('hover')) {
                    $(this).removeClass('active').find('ul, input').val('').hide();
                }
            });
        });



        $(document).on('change', '.pbuilder_font_select select', function () {
            var name = $(this).attr('name');
            name = name.substr(0, name.length - 12);
            var $style = $('[name="' + name + '_font_style"]');
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
                            newOptions[variant] = variant.replace('+', ' ');
                        }
                    }
                }
            }

            $styleMCSB.empty();
            $style.empty(); // remove old options
            $styleSpan.empty();
            var firstOpt = true;
            $.each(newOptions, function (key, value) {
                $style.append($('<option value="' + key + '">' + value + '</option>'));
                $styleMCSB.append($('<li><a' + (firstOpt ? ' class="selected"' : '') + ' data-value="' + key + '">' + value + '</a></li>'));
                if (firstOpt) {
                    $styleSpan.html(value);
                    firstOpt = false;
                }
            });
            $styleUl.fmCustomScrollbar('update');
        });

        $(document).on('click', '.pbuilder_save', function (e) {
            e.preventDefault();
            var json = {};
            $('.pbuilder_controls_wrapper input[name], .pbuilder_controls_wrapper select[name], .pbuilder_controls_wrapper textarea[name]').each(function () {
                json[$(this).attr('name')] = $(this).val();
            });
            var data = {
                action: 'pbuilder_admin_save',
                json: json
            }
            var $this = $(this);
            if (typeof window.pbuilder_saveajax != 'undefined') {
                window.pbuilder_saveajax.abort();
                $('.inProcess').next().stop(true).show().animate({'opacity': '0'});
                $('.inProcess').stop(true).animate({paddingRight: 20, paddingLeft: 20}, 200).removeClass('inProcess');
            }
            $this.stop(true).animate({paddingRight: 28, paddingLeft: 15}, 200).addClass('inProcess');
            $this.next().stop(true).show().animate({'opacity': '1'});
            window.pbuilder_saveajax = $.post(ajaxurl, data, function (response) {
                if (json.hasOwnProperty("imscpbiw_consumer_key")) {
                    /*
                     * Change by Asim Ashraf - Devbatch
                     * refresh page after GoToWebinar key save
                     * Date: 03-02-2015
                     */
                    location.reload();
                    console.log(json.imscpbiw_consumer_key);
                }
                console.log(response);
                $this.stop(true).animate({paddingRight: 20, paddingLeft: 20}, 200);
                $this.next().stop(true).show().animate({'opacity': '0'});
            });
        });

        $(document).on('click', '.pbuilder_page_template_remove', function (e) {
            e.preventDefault();

            if ($("#pbuilder_select_pb_page_templates option").length <= 0)
                return;

            var id = $("div[data-name=pb_page_templates] li a.selected").attr("data-value");
            if (typeof id == "undefined")
                id = $("div[data-name=pb_page_templates] li a").eq(0).attr("data-value");

            var data = {
                action: 'pbuilder_remove_template',
                id: id
            }
            var $this = $(this);
            if (typeof window.pbuilder_saveajax != 'undefined') {
                window.pbuilder_saveajax.abort();
                $('.inProcess').next().stop(true).show().animate({'opacity': '0'});
                $('.inProcess').stop(true).animate({paddingRight: 20, paddingLeft: 20}, 200).removeClass('inProcess');
            }
            $this.stop(true).animate({paddingRight: 28, paddingLeft: 15}, 200).addClass('inProcess');
            $this.next().css({"float": "left"}).stop(true).show().animate({'opacity': '1'});
            window.pbuilder_saveajax = $.post(ajaxurl, data, function (response) {
                response = JSON.parse(response);
                if (response.result == "success") {
                    $("div[data-name=pb_page_templates] li a[data-value=" + id + "]").remove();
                    $("#pbuilder_select_pb_page_templates option[value=" + id + "]").remove();
                    $("div[data-name=pb_page_templates] span").eq(0).html("");
                    //if($("#pbuilder_select_pb_page_templates option").length <= 0)
                    //$("div[data-name=pb_page_templates]").html('<div class="clear"></div>');
                    //window.location.href = window.location.href;
                }
                $this.stop(true).animate({paddingRight: 20, paddingLeft: 20}, 200);
                $this.next().stop(true).show().animate({'opacity': '0'});
            });
        });

        $(document).on('click', '.pbuilder_page_template_export', function (e) {
            e.preventDefault();

            if ($("#pbuilder_select_pb_page_templates option").length <= 0)
                return;

            var id = $("div[data-name=pb_page_templates] li a.selected").attr("data-value");
            if (typeof id == "undefined")
                id = $("div[data-name=pb_page_templates] li a").eq(0).attr("data-value");

            var data = {
                action: 'pbuilder_export_template',
                id: id
            }
            var $this = $(this);
            if (typeof window.pbuilder_saveajax != 'undefined') {
                window.pbuilder_saveajax.abort();
                $('.inProcess').next().stop(true).show().animate({'opacity': '0'});
                $('.inProcess').stop(true).animate({paddingRight: 20, paddingLeft: 20}, 200).removeClass('inProcess');
            }
            $this.stop(true).animate({paddingRight: 28, paddingLeft: 15}, 200).addClass('inProcess');
            $this.next().css({"float": "left"}).stop(true).show().animate({'opacity': '1'});
            window.pbuilder_saveajax = $.get(ajaxurl, data, function (response) {
                var json_data = JSON.parse(response);
                if (json_data.result == "success") {
                    window.location.href = json_data.fileurl;
                    //window.open(json_data.fileurl);
                }
                $this.stop(true).animate({paddingRight: 20, paddingLeft: 20}, 200);
                $this.next().stop(true).show().animate({'opacity': '0'});
            });
        });
        /* Shortcode input/textarea control

         $('.pbuilder_shortcode_menu input, .pbuilder_shortcode_menu textarea').live('keyup', function(){
         pbuilderContolChange($(this), true);
         });
         */

        /* Shortcode checkbox control */
        $(document).on('click', '.pbuilder_checkbox', function () {
            var $input = $(this).parent().find('.pbuilder_checkbox_input');
            if ($(this).hasClass('active')) {
                $input.val('false');
                $(this).removeClass('active');
            }
            else {
                $input.val('true');
                $(this).addClass('active');
            }
            // pbuilderContolChange($input);

        });

        /* Shortcode icon control */

        $(document).on('click', '.pbuilder_icon_left', function (e) {
            e.preventDefault();
            var $input = $(this).parent().find('input');
            var val = parseInt($input.attr('data-current'));
            if (val == parseInt($input.attr('data-min')))
                val = pbuilder_icons.length - 1;
            else
                val--;
            $input.val(pbuilder_icons[val]).attr('data-current', val);
            $(this).parent().find('.pbuilder_icon_holder i').attr('class', pbuilder_icons[val] + ' fawesome');
            //pbuilderContolChange($input, true);
        });

        $(document).on('click', '.pbuilder_icon_right', function (e) {
            e.preventDefault();
            var $input = $(this).parent().find('input');
            var val = parseInt($input.attr('data-current'));
            if (val == pbuilder_icons.length - 1)
                val = parseInt($input.attr('data-min'));
            else
                val++;
            $input.val(pbuilder_icons[val]).attr('data-current', val);
            $(this).parent().find('.pbuilder_icon_holder i').attr('class', pbuilder_icons[val] + ' fawesome');
            //pbuilderContolChange($input, true);
        });

        $(document).on('click', '.pbuilder_icon_pick', function (e) {
            e.preventDefault();
            var $drop = $(this).parent().find('.pbuilder_icon_dropdown');
            if ($(this).hasClass('pbuilder_gradient')) {
                $(this).removeClass('pbuilder_gradient').addClass('pbuilder_gradient_primary');
                $drop.show().addClass('active').fmCustomScrollbar('update');
                $(this).parent().find('.pbuilder_icon_drop_arrow').show();
            }
            else {
                $(this).addClass('pbuilder_gradient').removeClass('pbuilder_gradient_primary');
                $drop.hide().addClass('active');
                $(this).parent().find('.pbuilder_icon_drop_arrow').hide();
            }
        });
        $(document).on('click', '.pbuilder_icon_dropdown a', function (e) {
            e.preventDefault();
            var $parent = $(this).parent();
            while (!$parent.hasClass('pbuilder_control')) {
                $parent = $parent.parent();
            }
            var $input = $parent.find('input');
            var val = parseInt($(this).attr('href'));
            $input.val(pbuilder_icons[val]).attr('data-current', val);
            $parent.find('.pbuilder_icon_holder i').attr('class', pbuilder_icons[val] + ' fawesome');
            //pbuilderContolChange($input);
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
                    $(this).parent().find('.pbuilder_icon_pick').addClass('pbuilder_gradient').removeClass('pbuilder_gradient_primary');
                }
            });
        });


        /* Shortcode image control */

        var thickboxId = '';
        $(document).on('click', '.pbuilder_image_button', function (e) {
            e.preventDefault();
            thickboxId = '#' + $(this).attr('data-input') + '_holder';
            formfield = $(this).attr('data-input');
            var mediaurl = ajaxurl.substr(0, ajaxurl.indexOf('admin-ajax')) + 'media-upload.php';
            tb_show('', mediaurl + '?type=image&amp;width=620&amp;height=420&amp;TB_iframe=true');
            return false;
        });

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
            //pbuilderContolChange($(this), true);
        });

        window.send_to_editor = function (html) {
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
            //pbuilderContolChange($('#' + formfield));
        }


        /* Shortcode sortable control */

        $(document).on('click', '.pbuilder_sortable_add', function (e) {
            e.preventDefault();
            var html = '';
            var name = $(this).parent().attr('data-name');
            var item_name = $(this).parent().attr('data-iname');
            var $smenu = $(this).parent().parent();
            while (!$smenu.hasClass('pbuilder_shortcode_menu'))
                $smenu = $smenu.parent();
            var itemId = parseInt($smenu.attr('data-modid'));
            var itemSh = $smenu.attr('data-shortcode');
            console.log(pbuilder_shortcodes);
            console.log('sortable add:');
			console.log(pbuilder_items);

            var shortcodeJSON = $.extend({}, pbuilder_shortcodes[itemSh]['options'][name]);
            if (typeof pbuilder_items['items'][itemId]['options'][name]['items'] == 'undefined') {
                pbuilder_items['items'][itemId]['options'][name]['items'] = {};
                pbuilder_items['items'][itemId]['options'][name]['order'] = {};
            }
            var count = 0;
            while (typeof pbuilder_items['items'][itemId]['options'][name]['items'][count] != 'undefined' && pbuilder_items['items'][itemId][name]['items'][count] != '')
                count++;

            var pos = 0;
            while (typeof pbuilder_items['items'][itemId]['options'][name]['order'][pos] != 'undefined')
                pos++;
            pbuilder_items['items'][itemId]['options'][name]['order'][pos] = count;

            html += '<div class="pbuilder_sortable_item pbuilder_collapsible" data-sortid="' + count + '" data-sortname="' + name + '"><div class="pbuilder_gradient pbuilder_sortable_handle pbuilder_collapsible_header">' + item_name + ' ' + count + ' - <span class="pbuilder_sortable_delete">delete</span><span class="pbuilder_collapse_trigger">+</span></div><div class="pbuilder_collapsible_content">';
            console.log(shortcodeJSON);
            pbuilder_items['items'][itemId]['options'][name]['items'][count] = {};
            for (var x in shortcodeJSON['options']) {
                var newControl = new pbuilderControl('fsort-' + count + '-' + x, shortcodeJSON['options'][x]);
                html += newControl.html();

                pbuilder_items['items'][itemId]['options'][name]['items'][count][x] = (typeof shortcodeJSON['options'][x]['std'] != 'undefined' ? shortcodeJSON['options'][x]['std'] : '');
            }
            html += '</div></div>';
            $(this).parent().find('.pbuilder_sortable').append(html);
            pbuilderRefreshControls();
            $('.pbuilder_shortcode_menu').trigger('fchange');
        });

        $(document).on('click', '.pbuilder_sortable_delete', function () {
            var $sortitem = $(this).parent().parent();
            var id = parseInt($sortitem.attr('data-sortid'));
            var name = $sortitem.attr('data-sortname');
            var itemId = parseInt($('.pbuilder_shortcode_menu').attr('data-modid'));
            var $sortable = $sortitem.parent();
            $sortitem.remove();
            console.log(id, name);
            delete pbuilder_items['items'][itemId]['options'][name]['items'][id];
            delete pbuilder_items['items'][itemId]['options'][name]['order'];
            pbuilder_items['items'][itemId][name]['order'] = {};
            $sortable.children('.pbuilder_sortable_item').each(function (index) {
                pbuilder_items['items'][itemId]['options'][name]['order'][index] = parseInt($(this).attr('data-sortid'));
            });
            $('.pbuilder_shortcode_menu').trigger('fchange');
        });


        /* Shortcode collapsible control */

        $(document).on('click', '.pbuilder_collapsible_header', function () {

            var $content = $(this).parent().children('.pbuilder_collapsible_content');
            if (!$(this).hasClass('active')) {
                $(this).addClass('active').find('.pbuilder_collapse_trigger').html('-');
                $content.show();
            }
            else {
                $(this).removeClass('active').find('.pbuilder_collapse_trigger').html('+');
                $content.hide();
            }
            pbuilderRefreshControls();

        });

		$(document).on('click', '.drop_button', function () {
			$(this).parent().children('ul').each(function () {
					if (!$(this).hasClass('fmCustomScrollbar')) {
						$(this).fmCustomScrollbar({mouseWheelPixels: 150});
					}
					else {
						$(this).fmCustomScrollbar('update');
					}
				});
		});

        /* Shortcode colorpicker control */
        $(document).on('click', '.pbuilder_color_display', function () {
            var $ctrl = $(this).closest('.pbuilder_color_wrapper');
            $ctrl.find('.pbuilder_colorpicker').css('margin-left', -$ctrl.position().left + 10).addClass('active').show();
            $(this).parent().find('.pbuilder_color').trigger('focus');
            pbuilderRefreshControls();
        });
        $(document).on('mouseenter', '.pbuilder_color', function () {
            $(this).parent().find('.pbuilder_colorpicker').data('hover', true);
        });
        $(document).on('mouseleave', '.pbuilder_color', function () {
            $(this).parent().find('.pbuilder_colorpicker').data('hover', false);
        });

        $(document).on('mouseenter', '.pbuilder_colorpicker', function () {
            $(this).data('hover', true);
        });
        $(document).on('mouseleave', '.pbuilder_colorpicker', function () {
            $(this).data('hover', false);
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
                    pbuilderRefreshControls();
                }
            });
            $('.pbuilder_number_button.active').each(function () {
                if (!$(this).data('hover')) {
                    $(this).removeClass('active').closest('.pbuilder_control').find('.pbuilder_number_bar_wrapper').hide();
                    pbuilderRefreshControls();
                }
            });
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
                $ctrl.find('.pbuilder_number_bar_wrapper').show();
            }
            else {
                $(this).removeClass('active');
                $this.closest('.pbuilder_control').find('.pbuilder_number_bar_wrapper').hide();
            }
        });


        /* Shortcode change

         $('.pbuilder_shortcode_menu').live('fchange', function(){
         var id = parseInt($(this).attr('data-modid'));
         var $module = $('.pbuilder_module[data-modid='+id+']');
         var f = pbuilder_items['items'][id]['f'];
         var holder = $module.find('.pbuilder_module_content:first');
         var options = pbuilder_items['items'][id]['options'];
         pbuilderGetShortcode(f, holder, options);
         });
         */
        $(document).on('click', '.ui-draggable', function (e) {
            e.preventDefault();
        });

        $("#pbuilder_page_template_import").plupload({
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
                    //console.log('[UploadFile]' + file);

                    // You can override settings before the file is uploaded
                    // up.setOption('url', 'upload.php?id=' + file.id);
                    // up.setOption('multipart_params', {param1 : 'value1', param2 : 'value2'});
                }
            },
            init: {
                BeforeUpload: function (up, file) {
                    // Called right before the upload for a given file starts, can be used to cancel it if required
                    //console.log('[BeforeUpload] File: ' + file);
                },
                FileUploaded: function (up, file, info) {
                    if (info.response != null) {
                        var template = JSON.parse(info.response);
                        if (template.tempname == "")
                        {
                            /*$(function() {
                             jQuery( '<div id="dialog" title="Basic dialog"><p>This is the default dialog which is useful for displaying information. The dialog window can be moved, resized and closed with the "x" icon.</p></div>' ).dialog();
                             });*/
                            jQuery.colorbox({html: '<div style="width:470px;"><div id="dirwrongWarring"></div><div style="padding:0 15px;"><strong>Sorry! you are using an invalid directory structure for the template.</strong><br /><p>The template files in the ZIP are supposed to be at the root level ..<br /><br />Sub-Folders are not supported.</p></div></div>'});

                            //'<div id="dialog" title="Basic dialog"><p>This is the default dialog which is useful for displaying information. The dialog window can be moved, resized and closed with the "x" icon.</p></div>';
                            //alert("Sorry! you are using an invalid directory structure for the template.\n\nThe template files in the ZIP are supposed to be at the root level ..\n\nSub-Folders are not supported.");
                        }
                        if (template.tempname && template.tempid != "") {
                            AddTemplate(template.tempid, template.tempname);
                        }
                    }
                },
            }
        });

    }

    function AddTemplate(tempid, tempname) {
        var temp_count = $("#pbuilder_select_pb_page_templates").parent().find(".mCSB_container li").length;
        var temp_exist = $("#pbuilder_select_pb_page_templates").parent().find('.mCSB_container li a[data-value=' + tempid + ']').length;
        if (temp_exist <= 0) {
            var li = '<li><a data-value="' + tempid + '" href="#"';
            if (temp_count <= 0) {
                li += ' class="selected"';
                $("#pbuilder_select_pb_page_templates").parent().find("span").html(tempname);
            }
            li += '>' + tempname + '</a></li>';
            $("#pbuilder_select_pb_page_templates").parent().find(".mCSB_container").append(li);
        }
    }


	  jQuery(document).ready(function($) {
		  $('.pb-tag-filter').on('change', function(e) {
			  pb_filter_templates();
		  });
      $('#tags_search').on('change mouseup keyup focus blur search', function(e) {
        pb_filter_templates();
      });
      
      $('#pb-show-club-themes').on('change', function(e) {
			  pb_filter_templates();
		  });
      
      $('#pb-filter-toggle').on('change', function(e) {
			  if ($('#pb-filter-toggle').is(':checked')) {
          $('#filter-by-tags').find('input').each(function( i, val ) {
            $(this).prop('checked', true);
          });
        } else {
          $('#filter-by-tags').find('input').each(function( i, val ) {
            $(this).prop('checked', false);
          });
        }
        pb_filter_templates();
		  });
      
		  pb_filter_templates();
		});

		function pb_filter_templates() {
		  // hide everything and then show only ones that are checked and have that class
		  jQuery('li.template_wrapper').hide();

		  jQuery('.pb-tag-filter').each(function(ind, el) {
        if (jQuery(el).is(':checked')) {
          tag = jQuery(el).data('tag');
          jQuery('li.template_wrapper').filter('.' + tag).show();
        }
		  });

      search_term = jQuery('#tags_search').val();
      if (search_term) {
        reg_exp = new RegExp(search_term, 'i');
        jQuery('li.template_wrapper:visible').each(function(ind, el) {
          name = $('span.template_name', el).text();
          if (name.search(reg_exp) == -1) {
            $(el).hide();
          }
        });
      }
      
      if (!jQuery('#pb-show-club-themes').is(':checked')) {
        jQuery('li.template_wrapper').filter('.pb-premium').hide();
      }

		jQuery('#filter_import_export').on('click',function(){
			jQuery('.pbuilder_collapsible_import-export').toggle();
		});

	} // pb_filter_templates



})(jQuery);
