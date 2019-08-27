(function ($) {
    "use strict";
    $(document).ready(function () {



        if (typeof pbuilder != "undefined") {
            if (pbuilder.page_bg == "bgimage") {
                var theWindow = $(window),
                        $bg = $("#bg"),
                        aspectRatio = $bg.width() / $bg.height();

                theWindow.resize(function () {
                    if ((theWindow.width() / theWindow.height()) < aspectRatio)
                        $bg.removeClass().addClass('bgheight');
                    else
                        $bg.removeClass().addClass('bgwidth');
                }).trigger("resize");
            } else if (pbuilder.page_bg == "videoembed") {
                if (pbuilder.video_bg != 'none') {
                    var atts = {
                        mute: (pbuilder.video_mute == 1 ? true : false),
                        loop: (pbuilder.video_loop == 1 ? true : false),
                        hd: (pbuilder.video_hd == 1 ? true : false)
                    };
                    pbuilderYoutube("pb_page_bg_inner", pbuilder.video_bg, atts);
                }
            }
        }

        $("[class*=timed-row-]").each(function () {
            var timed_row = this.className.match(/timed-row-(\d+)/i);
            var duration = timed_row ? parseInt(timed_row[1], 10) : 0;
            $(this).delay(duration).fadeIn('fast',function() {
				FlipClock.Resize();
           		mbComingsoonResize();
			});
        });


        $(window).on("resize", function () {
            /**************************************/
            // Code added by Asim Ashraf - DevBatch
            // DateTime: 28 Jan 2015
            // Code Edit Start
            /************************************/
            FlipClock.Resize();
            mbComingsoonResize();
            /**************************************/
            // Code added by Asim Ashraf - DevBatch
            // DateTime: 28 Jan 2015
            // Code Edit End
            /************************************/



            $(".googlemap").each(function () {
                var $this = $(this);
                var MapUrl = $this.attr("src");
                var windowWidth = $(window).width();
                var ZoomLevel = "&z=17&";
                if (windowWidth <= 752 && windowWidth >= 324) {
                    ZoomLevel = "&z=16&";
                } else if (windowWidth < 325) {
                    ZoomLevel = "&z=15&";
                }
                var Czoom = MapUrl.replace(/&z=(.*)&/mg, ZoomLevel);
                $this.attr("src", Czoom);
            });
            $(".optin").each(function () {
                var $this = $(this);
                var col_width = $this.width();//console.log(col_width);
                if (col_width > 727) {
                    var fields_count = $this.find(".field").length + 1;
                    var fieldswidth = col_width / fields_count;

                    if (col_width <= 642 && $this.hasClass("optin_style_Vertical")) {
                        fieldswidth = col_width;
                    } else {
                        if (col_width > 548) {
                            if (fieldswidth < 300)
                                fieldswidth = col_width;
                            else
                                fieldswidth -= 5;
                        } else {
                            fieldswidth -= 10;
                        }
                    }


                    //console.log(fields_count);
                    //console.log('1: '+fieldswidth);

                    var padleft = parseInt($(this).find("input").eq(0).attr("padding-left"));
                    var padright = parseInt($(this).find("input").eq(0).attr("padding-right"));
					var padtop = parseInt($(this).find("input").eq(0).attr("padding-top"));
                    var padbottom = parseInt($(this).find("input").eq(0).attr("padding-bottom"));

                    $this.find(".field").each(function () {
                        var fieldwidth = fieldswidth - 5;

                        $(this).css({
                            'float': 'left',
                            'display': 'block',
                            "margin": "0 5px 10px 0",
                            // 'width':fieldwidth+'px',
                            "padding-left": "0px",
                            "padding-right": "0px",

                        });

                        var fieldwidth = fieldswidth - 5;
                        if (typeof $this.find('.field input').eq(0).css("box-sizing") == "undefined" || $this.find('.field input').eq(0).css("box-sizing") != 'border-box')
                            fieldwidth = fieldswidth - padleft - padright - 5;
                        //console.log('2: '+fieldwidth);
                        $(this).find("input").css({
                            'float': 'none',
                            'display': 'block',
                            "margin": "none",
                            // "width":fieldwidth+"px",
                            "padding-left": padleft + "px",
                            "padding-right": padright + "px",
							"padding-top": padtop + "px",
                                "padding-bottom": padbottom + "px"
                        });
                    });
                    var fieldwidth = fieldswidth + padleft + padright;
                    var padleft = $this.find(".frb_button").css("padding-left");
                    var padright = $this.find(".frb_button").css("padding-right");
                    //console.log('3: '+fieldwidth);
                    padleft = (typeof padleft != "undefined" && padleft != null) ? padleft.replace("px", "") : 0;
                    padright = (typeof padright != "undefined" && padright != null) ? padright.replace("px", "") : 0;

                    fieldwidth = fieldswidth - 5;
                    if (typeof $this.find('.field input').eq(0).css("box-sizing") == "undefined" || $this.find('.field input').eq(0).css("box-sizing") != 'border-box')
                        fieldwidth = fieldswidth - padleft - padright - 5;
                    // console.log('4: '+fieldwidth);
                    //alert('fieldwidth #183: '+fieldwidth);


					$this.find(".frb_button").css({
                        'float': 'left',
                        'display': 'block',
                        "margin": "0 5px 10px 0",
                        //'width':fieldwidth+'px',
                        'box-sizing': 'border-box',
                    })
                            .prev("div.clear").remove().before("<div class='clear' style='clear:both;'></div>")
                            .next("div.clear").remove().before("<div class='clear' style='clear:both;'></div>");

                    if(typeof $this.find(".frb_button").position() !== 'undefined' ) 	var btntop = $this.find(".frb_button").position().top;

                    $this.find(".field").each(function () {
                        var thistop = $(this).position().top;
                        if (btntop == thistop) {
                            var padtop = $this.find(".frb_button").css("padding-top");
                            var padbottom = $this.find(".frb_button").css("padding-bottom");

                            padtop = (typeof padtop != "undefined" && padtop != null) ? padtop.replace("px", "") : 0;
                            padbottom = (typeof padbottom != "undefined" && padbottom != null) ? padbottom.replace("px", "") : 0;

                            padtop = parseInt(padtop) + 5;
                            padbottom = parseInt(padbottom) + 5;

                            $(this).find("input").css({
                                "padding-top": padtop + "px",
                                "padding-bottom": padbottom + "px"
                            });
                        } else {
                            $(this).find("input").css({
                                "padding-top": padtop + "px",
                                "padding-bottom": padbottom + "px"
                            });
                        }
                        //console.log(thistop);
                    });
                } else {
                    var padleft = parseInt($(this).find(".field input").eq(0).attr("padding-left"));
                    var padright = parseInt($(this).find(".field input").eq(0).attr("padding-right"));

                    $this.find(".field").each(function () {
                        var fieldwidth = col_width;
                        $(this).css({
                            'float': 'none',
                            'display': 'block',
                            "margin": "none",
                            //  'width':fieldwidth+'px',
                            "padding-left": "0px",
                            "padding-right": "0px",
                        });
                        var fieldwidth = col_width;// - padleft - padright;
                        if (typeof $this.find('.field input').eq(0).css("box-sizing") == "undefined" || $this.find('.field input').eq(0).css("box-sizing") != 'border-box')
                            fieldwidth = col_width - padleft - padright;
//console.log('5: '+fieldwidth);
                        $(this).find("input").css({
                            'float': 'none',
                            'display': 'block',
                            "margin": "none",
                            // "width":fieldwidth+"px",
                            "padding-left": padleft + "px",
                            "padding-right": padright + "px"
                        });
                    });

                    var fieldwidth = col_width + padleft + padright;
                    var padleft = $this.find(".frb_button").css("padding-left");
                    var padright = $this.find(".frb_button").css("padding-right");

                    padleft = (typeof padleft != "undefined" && padleft != null) ? padleft.replace("px", "") : 0;
                    padright = (typeof padright != "undefined" && padright != null) ? padright.replace("px", "") : 0;
                    //console.log(col_width + ' ' + padleft + ' ' + padright);

                    fieldwidth = col_width;// - padleft - padright;
                    //alert('fieldwidth #255: '+fieldwidth);
                    //fieldwidth = fieldwidth == 312 ? 331 : fieldwidth;
                    //fieldwidth = fieldwidth == 516 ? 521 : fieldwidth;
                    //$this.find(".frb_button").css('width:'+fieldwidth+'px !important;');
                    $this.find(".frb_button").css({
                        'float': 'none',
                        'display': 'block',
                        'margin': '0px auto',
                        //'width': fieldwidth + 'px !important',
                        'box-sizing': 'border-box',
                    }).prev("div.clear").remove().before("<div class='clear' style='clear:both;'></div>");

                    /*if( fieldwidth == 331 ){
                     $this.find(".frb_button"). addClass('width331');
                     }
                     if( fieldwidth == 521 ){
                     //alert('fieldwidth: '+fieldwidth);
                     $this.find(".frb_button"). addClass('width521');
                     //alert('afieldwidth: '+fieldwidth);
                     }*/
                    //$this.find(".frb_button").css('cssText', 'float:none;display:block;margin:0px;width: '+fieldwidth+'px !important;box-sizing:border-box;');

                }
            });
        }).trigger("resize");

        //removecufon();
    });





})(jQuery);

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

function removecufon() {
    jQuery('cufon cufontext').each(function () {
        jQuery(this).closest('cufon').parent().append(jQuery(this).html());
        jQuery(this).closest('cufon').remove();
    });
}
