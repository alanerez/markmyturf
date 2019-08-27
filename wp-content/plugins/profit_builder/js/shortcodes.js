function pbinIframe() {
    try {
        return window.self !== window.top;
    } catch (e) {
        return true;
    }
}




if(typeof window.onYouTubeIframeAPIReady == 'undefined') {
	window.onYouTubeIframeAPIReady = function() {};

}
	function pbuilderYoutube() {};

(function($){

    

	$.fn.frb_refresh = function(bodyRefresh){

		//			audio - - start
		$(this).find('.frb_audio_player').each(function(){
			var options = $(this).data('frb_options').split('|'), volume = 1, thisReference = $(this);

			thisReference.find('.frb_mute_button,.frb_muted_button').data('volume',volume);
			thisReference.find('.frb_mute_button,.frb_muted_button').data('volume');

			if (options[3]=='true'){$(this).css('display','none');}
			var duration=0, src_mp3=options[4], src_ogg=options[5], startingTime = parseInt(options[6]), song = thisReference.find('audio')[0];

			song.preload = 'metadata';
			if (song.canPlayType('audio/mpeg;')) {
			    song.type= 'audio/mpeg';
			    song.src= src_mp3;
			} else {
			    song.type= 'audio/ogg';
			    song.src= src_ogg;
			}

			song.addEventListener('loadedmetadata', function() {
				duration = song.duration;

				var hours = parseInt( duration / 3600 ) % 24, minutes = parseInt( duration / 60 ) % 60, seconds = Math.ceil(duration % 60),
					result = (hours!=0 ? (hours < 10 ? "0" + hours + ":" : hours + ":" ) : '' ) + (minutes!=0 ? ( minutes < 10 ? "0" + minutes : minutes) : "00") + ":" + (seconds  < 10 ? "0" + seconds : seconds);

				thisReference.find(".frb_full_time").text(result);
				thisReference.find(".frb_time_slider").slider({
					create: function(event, ui) {
						$(this).slider("option", "value",startingTime);
						song.currentTime = $(this).slider("option", "value");
					},
					stop: function( event, ui ) {
						song.currentTime = $(this).slider("option", "value");
			 		},
		 			change: function(event, ui){
				 		var value = $(this).slider("option", "value");
				 		hours = parseInt( value / 3600 ) % 24;
					 	minutes = parseInt( value / 60 ) % 60;
						seconds = Math.ceil(value % 60);
						result = (hours!=0 ? (hours < 10 ? "0" + hours + ":" : hours + ":" ) : '' ) + (minutes!=0 ? ( minutes < 10 ? "0" + minutes : minutes) : "00") + ":" + (seconds  < 10 ? "0" + seconds : seconds);
						thisReference.find(".frb_current_time").text(result);
					},
				 	max: Math.ceil(duration),
				 	step: 0.01,
				 	range:"min"
			 	});


			 	thisReference.find(".frb_volume_slider").slider({
				 	slide: function(event, ui){
				 		song.volume = $(this).slider("option", "value");
				 		if (song.volume == 0) {
						thisReference.find('.frb_mute_button, .frb_muted_button').removeClass('frb_mute_button frb_muted_button frb_volume_down').addClass('frb_muted_button');
						} else if (song.volume<0.5) {
						thisReference.find('.frb_mute_button, .frb_muted_button').removeClass('frb_mute_button frb_muted_button frb_volume_down').addClass('frb_mute_button frb_volume_down');
						} else {
						thisReference.find('.frb_mute_button, .frb_muted_button').removeClass('frb_mute_button frb_muted_button frb_volume_down').addClass('frb_mute_button');
						}

					},
					change: function( event, ui ) {
						if ($(this).slider("option", "value")!=0) {
							volume = $(this).slider("option", "value");
							thisReference.find('.frb_mute_button,.frb_muted_button').data('volume',volume);
						}
						song.volume = $(this).slider("option", "value");

				 		if (song.volume == 0) {
						thisReference.find('.frb_mute_button, .frb_muted_button').removeClass('frb_mute_button frb_muted_button frb_volume_down').addClass('frb_muted_button');
						} else if (song.volume<0.5) {
						thisReference.find('.frb_mute_button, .frb_muted_button').removeClass('frb_mute_button frb_muted_button frb_volume_down').addClass('frb_mute_button frb_volume_down');
						} else {
						thisReference.find('.frb_mute_button, .frb_muted_button').removeClass('frb_mute_button frb_muted_button frb_volume_down').addClass('frb_mute_button');
						}
				 	},
				 	max: 1,
				 	min: 0,
				 	step: 0.01,
				 	range:"min",
				 	value:1
				 });


				var timeBarWidth = thisReference.width()-thisReference.find('.frb_play_button').outerWidth(true)-thisReference.find('.frb_mute_button').outerWidth(true)-thisReference.find('.frb_stop_button').outerWidth(true)-thisReference.find(".frb_volume_slider").outerWidth(true)-85;
				var timeBarWidthSmall = thisReference.width()-thisReference.find('.frb_play_button').outerWidth(true)-thisReference.find('.frb_stop_button').outerWidth(true)-85;

				if (timeBarWidth>0){
					thisReference.find(".frb_time_slider").outerWidth(timeBarWidth);
				} else {
					thisReference.find(".frb_time_slider").outerWidth(timeBarWidthSmall);
				}

				if (options[0]=='true') {
					thisReference.find('.frb_play_button').removeClass('frb_play_button').addClass('frb_pause_button');
					song.autoplay=true;
				}
				if (options[1]=='true') {song.loop=true;}
				if (options[2]=='true') {
					song.volume=0;
					thisReference.find('.frb_mute_button').removeClass('frb_mute_button').addClass('frb_muted_button');
			  		thisReference.find('.frb_volume_slider').slider( "option", "value", 0 );
				}
				song.addEventListener('timeupdate',function (){
 					curtime = parseFloat(song.currentTime);
   					if (!thisReference.find(".frb_time_slider .ui-slider-handle").hasClass('ui-state-active')){
       					thisReference.find( ".frb_time_slider" ).slider( "option", "value", curtime );
       				}
    				});
				song.addEventListener('ended',function (){
    				thisReference.find('.frb_pause_button').removeClass('frb_pause_button').addClass('frb_play_button');
    				thisReference.find(".frb_time_slider" ).slider( "option", "value", 0 );
    				});
 			});
		});

		//			audio - - end


    $(window).load(function wpvf_runrun(){
      if(!Modernizr.touch){
        /*$(window).scroll(function(){
          if($(this).scrollTop() > 400 && $(document).width()>760){
            $(".frb-video-floater").addClass("frb_video_float");
            if($(".frb-video-floater").parent().parent().parent().parent().parent().parent().data('rowid')){
              $(".frb-video-floater").parent().parent().parent().parent().parent().parent().css('z-index','999');
            }
          } else {
            $(".frb-video-floater").removeClass("frb_video_float");
          }
        });*/
      }
    });

		$(this).find('.frb_accordion').each(function(){
			var collapse = true;
			var collapseInd = false;
			var heightStyle;
			if($(this).attr('data-fixedheight') != 'true')
				heightStyle = 'content';
			else
				heightStyle = 'auto';

			$(this).find('h3').each(function(index){
				if($(this).hasClass('ui-state-active')) {
					collapse = false;
					collapseInd = index;
					return false;
				}
			});
			$(this).accordion({
				collapsible : collapse,
				active : collapseInd,
				heightStyle : heightStyle
			});
		});
		$(this).find('.frb_tabs').each(function(){
			var hashtag = window.location.hash;
			if(hashtag != '' && $(this).find(hashtag).length > 0) {
				$(this).find('ul a.active').removeClass('active');
				$(this).find('ul a[href='+hashtag+']').trigger('click');
			}
			else if($(this).find('ul .active').length>0) {
				$(this).find('ul a.active').removeClass('active').trigger('click');
			}
			else {
				$(this).find("ul a:first").trigger("click");
			}
		});

		$(this).find('.frb_tour').each(function(){
			var hashtag = window.location.hash;
			if(hashtag != '' && $(this).find(hashtag).length > 0) {
				$(this).find('ul a.active').removeClass('active');
				$(this).find('ul a[href='+hashtag+']').trigger('click');
			}
			else if($(this).find('ul .active').length>0) {
				$(this).find('ul a.active').removeClass('active').trigger('click');
			}
			else {
				$(this).find("ul a:first").trigger("click");
			}

			$(this).find(".frb_tour-content").css("min-height", $(this).find("ul:first").height()+"px");
		});

		$(this).find('.frb_toggle').each(function(){
			var maxHeight = -1;
			var itemContent = $(this).find(".frb_toggle_item_content");

			if ($(this).hasClass('frb_fixed_h')) {
				maxHeight = itemContent.map(function ()
					{
			 		   return $(this).height();
					}).get();

					maxHeight = Math.max.apply(null, maxHeight);
			}

			$(this).find('.frb_toggle_item').each(function(){

			var itemContent = $(this).children(".frb_toggle_item_content");
			if (maxHeight == -1)
				$(this).data("itemHeight",itemContent.height());
			else
				$(this).data("itemHeight",maxHeight);

				if ($(this).children("input:checked").length != 0 )
					itemContent.height($(this).data("itemHeight")+"px").prev().find("i").addClass("fa-minus");
				else
					itemContent.height("0px").prev().find("i").addClass("fa-plus");
			});
		});

		//swiper slider

		$(this).find('.frb-swiper-container').each(function(){
			if(!$(this).hasClass('initiated')) {
				$(this).addClass('initiated');
				var modid = $(this).closest('.pbuilder_module').attr('data-modid');
				var sliderId = $(this).attr('id').replace('_container','');
				var options = {
			    	pagination: '#'+sliderId+' .frb-swiper-pagination',
			   		loop:true,
			   	 	grabCursor: true,
			   	 	slidesPerView: parseInt($(this).attr('data-slidesPerView')),
				    paginationClickable: true,
					calculateHeight:true,
					resizeReInit: true,
					updateOnImagesReady: true,
					mode: $(this).attr('data-mode'),
					passiveListeners:false,
					preventClicks: false,
					preventClicksPropagation: false,
					slideToClickedSlide:true
				}
				if(typeof $(this).attr('data-min-res-width') == 'undefined') {
					$(this).attr('data-min-res-width', 1);
				}
				if($(this).attr('data-autoplay') != '') options['autoplay'] = parseInt($(this).attr('data-autoplay'));

				var height = 0;

					$(this).find('.swiper-slide').each(function(){
						if (options['mode'] != 'horizontal') {
						$(this).css('width','100%');
						if($(this).height() > height) height = $(this).height()*options['slidesPerView'];
						$(this).css('height', height+'px');
					    options['calculateHeight'] = false;
					}
					});


				if(typeof bodyRefresh != 'undefined' && bodyRefresh == true) {
					$('#'+sliderId+' .frb-swiper-container').hide();
					$(window).load(function(){
						$('#'+sliderId+' .frb-swiper-container').each(function(){
							$(this).show();
							var mySwiper = new Swiper(this, options);
							$(this).find('.frb-swiper-nav-left').click(function(){mySwiper.swipePrev()});
							$(this).find('.frb-swiper-nav-right').click(function(){mySwiper.swipeNext()});
							$(this).data('swiper_controls', mySwiper);
							frbSliderResponsive($(this));
							$(this).find('a[rel^="frbprettyphoto"]').prettyPhoto({deeplinking:false});
							mySwiper.swipeTo(0, 0, false);
						});
					});
				}
				else {
					$('#'+sliderId+' .frb-swiper-container').each(function(){
						var mySwiper = new Swiper(this, options);
						$(this).find('.frb-swiper-nav-left').click(function(){mySwiper.swipePrev()});
						$(this).find('.frb-swiper-nav-right').click(function(){mySwiper.swipeNext()});
						$(this).data('swiper_controls', mySwiper);
						frbSliderResponsive($(this));
						$(this).find('a[rel^="frbprettyphoto"]').prettyPhoto({deeplinking:false});
						mySwiper.swipeTo(0, 0, false);
					});
				}
			}
		});
		if($(this).find('.frb_percentage_bar').attr('data-onscreen') == 'true'){
			$(this).find('.frb_percentage_bar').each(function(){
				$(this).frbPercentageBars();});
		}

		if($(this).attr('data-onscreen') == 'true'){
			$(this).find('.frb_scrolling_counter').each(function(){$(this).frbScrollingCounters();});
		}
		if($(this).attr('data-onscreen') == 'true'){
			$(this).find('.frb_percentage_chart').frbPercentageChart();
		}
		//	gauge
		if($(this).find('.frb_gauge_chart').attr('data-onscreen') == 'true'){
			frbGaugeChartRefresh($(this));
		}


		contactFormResize();


		frbGalleryInit($(this).find('.frb_gallery_container'));
		galleryShortcodeRefresh($(this).find('.frb_gallery_container'));



		$(this).find('a[rel^="frbprettyphoto"]').prettyPhoto({deeplinking:false});

		frbScrollAnimations();
	}




	var parallaxTimeout;

	$(window).resize(function(){
		galleryShortcodeRefresh($('.frb_gallery_container'));
		$('.frb_percentage_chart').each(function(){$(this).frbPercentageChart();});
	});



	/* DOCUMENT READY */
	$(document).ready(function(){


		if('ontouchstart' in window) {
			$('#pbuilder_wrapper').addClass('pbuilder_touch');
		}

		$(document).on("onscreen", '.frb_percentage_chart', function(){
			$(this).frbPercentageChart();
		});

		$(document).on('onscreen', '.frb_scrolling_counter', function(){
			$(this).frbScrollingCounters();
			$(this).attr('data-onscreen', true);
		});

		$(document).on('onscreen', '.frb_percentage_bar', function(){
			$(this).frbPercentageBars();
			$(this).attr('data-onscreen', true);
		});

		$(document).on('onscreen', '.frb_gauge_chart', function(){
			frbGaugeChartRefresh($(this).closest('.pbuilder_module'));
			$(this).attr('data-onscreen', true);
		});


		$('.pbuilder_module').each(function(){
			$(this).frb_refresh(true);
		});
		frbParallaxBackground();
		if(window.location.hash != '')
			window.location.hash = window.location.hash;
		//frbParallax();


		/* FULL WIDTH ROW */
		var winW = $(window).width();
		var winH = $(window).height();
		var rowW = $('#pbuilder_wrapper').width();
		$('<style id="pbuilder_dynamic_row_css" type="text/css"> .pbuilder_row.pbuilder_row_full_width{margin:0 '+( (-winW + rowW)/2 - 1500 ) +'px; } .pbuilder_row_stick_top{margin: 0 -1500px !important;} .pbuilder_row .pbuilder_row_video > div {width:'+(winW+200)+'px; height:'+(winH+200)+'px; margin:-'+((winH+200)/2)+'px 0 0 -'+((winW+200)/2)+'px !important;} </style>').appendTo('head');


		$(window).resize(function(){
			var winW = $(window).width();
			var winH = $(window).height();
			var rowW = $('#pbuilder_wrapper').width();
			$('#pbuilder_dynamic_row_css').html('.pbuilder_row.pbuilder_row_full_width{margin:0 '+( (-winW + rowW)/2 - 1500  )+'px;} .pbuilder_row_stick_top{margin: 0 -1500px !important;} .pbuilder_row .pbuilder_row_video > div {width:'+(winW+200)+'px; height:'+(winH+200)+'px; margin:-'+((winH+200)/2)+'px 0 0 -'+((winW+200)/2)+'px !important;} ');
		});


		$(this).find('.YTPlayer').mb_YTPlayer();


	});

	$(window).load(function(){
		$(this).scrollTop($(this).scrollTop()+1);
		$(this).scrollTop($(this).scrollTop()-1);
	});


	/* MODULE REFRESH */

	$(document).on('refresh','.pbuilder_module', function(){
		$(this).frb_refresh(false);
	});
	$(document).on('refresh','.pbuilder_row', function(){
		$(this).find('.YTPlayer').mb_YTPlayer();
	});

	$(window).scroll(function(){
		frbScrollAnimations();
		frbParallaxBackground();
	});

	function frbScrollAnimations() {
		$('.frb_animated').each(function(){
			if(!$(this).hasClass('frb_onScreen') && isScrolledIntoView(this)) {
				if(typeof $(this).attr('data-agroup') != 'undefined') {
					$('[data-agroup=' + $(this).attr('data-agroup') + ']').each(function(){
						if(typeof $(this).attr('data-adelay') != 'undefined' && parseInt($(this).attr('data-adelay')) != 0) {
							if(!$(this).hasClass('frb_onScreenDelay')) {
								$(this).addClass('frb_onScreenDelay');
								var $this = $(this);
								setTimeout(function(){
									$this.addClass('frb_onScreen').trigger('onscreen');
								}, parseInt($(this).attr('data-adelay')));
							}
						}
						else {
							$(this).addClass('frb_onScreen').trigger('onscreen');
						}
					});
				}
				else {
					if(typeof $(this).attr('data-adelay') != 'undefined' && parseInt($(this).attr('data-adelay')) != 0) {
						if(!$(this).hasClass('frb_onScreenDelay')) {
							$(this).addClass('frb_onScreenDelay');
							var $this = $(this);
							setTimeout(function(){
								$this.addClass('frb_onScreen').trigger('onscreen');
							}, parseInt($(this).attr('data-adelay')))
						}
					}
					else {
						$(this).addClass('frb_onScreen').trigger('onscreen');
					}

				}
			}

		})
	}
	function isScrolledIntoView(elem){
		if($.fn.scrollBro != 'undefined' && $('.scb_main_wrap').length > 0) {
			var $wrap = $('.scb_main_wrap');
			var docViewTop = $wrap.scrollBro('scrollTop');
		    var docViewLimit = $(window).height()/1.1;

			var wrapTop = $wrap.offset().top;
		    var elemTop = $(elem).offset().top;

		    return ((elemTop <= docViewLimit+wrapTop) && (elemTop >= wrapTop));
		}
		else {
		    var docViewTop = $(window).scrollTop();
		    var docViewLimit = docViewTop + $(window).height()/1.1;

		    var elemTop = $(elem).offset().top;
		    var elemBottom = elemTop + $(elem).height();

		    return ((elemTop <= docViewLimit) && (elemTop >= docViewTop));
		}
	}

	function frbParallaxEvent(){
		$('.pbuilder_row_parallax').each(function(){
			if(typeof $(this).data('backH') == 'undefined') {
				var imageSrc = $(this).css('background-image').replace(/url\((['"])?(.*?)\1\)/gi, '$2').split(',')[0];
			    var image = new Image();
			    image.src = imageSrc;
			    $(this).data('backH', image.height);
			}
			var parallaxPos = (window.myScroll.y/($(this).data('backH') - $(window).height() ))*100 + 50;
			$(this).css('background-position', '50% '+parallaxPos+'%');
		});
	}

	function frbParallaxBackground(){
		if(!('ontouchstart' in window)) {
			$('.pbuilder_row_background_parallax, .pbuilder_row_background_fixed').each(function(){
				var scrTop = $(window).scrollTop();
				var winH = $(window).height();
				var elTop = $(this).offset().top;
				var elH = $(this).height();
				var zero = elTop -winH/2 + elH/2;

				if(zero < 0) zero;
				if($(this).hasClass('pbuilder_row_background_fixed')) {
					var parallaxPos = scrTop - zero -winH/2 + elH/2;
				}
				else {
					var parallaxPos = (scrTop - zero)*0.6 -winH/2 + elH/2;
				}

				$(this).find('.pbuilder_row_back_image:first').css({top : parallaxPos, height : winH});
			});
		}

	}



	/* AUDIO */

	$(document).on('click', '.frb_play_button' ,function(e){
	  	e.preventDefault();
		$(this).siblings('audio')[0].play();
	  	$(this).removeClass('frb_play_button').addClass('frb_pause_button');
	});

	$(document).on('click', '.frb_pause_button' ,function(e){
	  	e.preventDefault();
	  	$(this).siblings('audio')[0].pause();
	  	$(this).removeClass('frb_pause_button').addClass('frb_play_button');
	});

	$(document).on('click', '.frb_mute_button' ,function(e){
	  	e.preventDefault();
	  	$(this).siblings('audio')[0].volume = 0;
	  	$(this).removeClass('frb_mute_button').addClass('frb_muted_button');
	  	$(this).parent().find('.frb_volume_slider').slider( "option", "value", 0 );
	});

	$(document).on('click', '.frb_muted_button' ,function(e){
	  	e.preventDefault();
	  	$(this).siblings('audio')[0].volume=$(this).data('volume');
	  	$(this).removeClass('frb_muted_button').addClass('frb_mute_button');
	  	$(this).parent().find('.frb_volume_slider').slider( "option", "value", $(this).data('volume') );
	});

	$(document).on('click', '.frb_stop_button' ,function(e){
	  	e.preventDefault();
	  	$(this).siblings('audio')[0].pause();
	  	$(this).siblings('audio')[0].currentTime = 0;
	  	$(".frb_pause_button").removeClass('frb_pause_button').addClass('frb_play_button');
	});

	/* TABS */

	$(document).on('click','.frb_tabs ul a', function(e){
		e.preventDefault();
		if(!$(this).hasClass('active')) {
			$(this).parent().parent().find('a').removeClass("active");
			$(this).addClass('active');

			var $containter = $(this).closest('.frb_tabs'),
				tabId = $(this).attr('href');
			$containter.children('.frb_tabs-content').stop(true, true).hide();
			$containter.find(tabId).fadeIn();
		}
	});


	/* TOGGLE */

	$(document).on('click','.frb_toggle label', function(e){
		var $itemContent = $(this).next();
		if ($itemContent.outerHeight()>0) {
			$itemContent.outerHeight("0px").prev().find("i").removeClass("fa-minus").addClass("fa-plus");
		}
		else {
			$itemContent.outerHeight($(this).parent().data("itemHeight")+"px").prev().find("i").removeClass("fa-plus").addClass("fa-minus");
		}

	});


	/* TOUR */

	$(document).on('click','.frb_tour ul a', function(e){
		e.preventDefault();
		if(!$(this).hasClass('active')) {
			$(this).parent().parent().find('a').removeClass("active");
			$(this).addClass('active').parent().next().find("a");
			var $containter = $(this).closest('.frb_tour'),
				tabId = $(this).attr('href');
			$containter.children('.frb_tour-content').stop(true, true).hide();
			$containter.find(tabId).fadeIn();
		}
	});

	/* BUTTONS */

	$(document).on('mouseenter','.frb_button', function(){
		var backcolor = $(this).attr('data-hoverbackcolor'); if(backcolor == '') backcolor = 'transparent';
		var textcolor = $(this).attr('data-hovertextcolor'); if(textcolor == '') textcolor = 'transparent';
		if(!$(this).hasClass('frb_nofill'))
			$(this).stop(true).animate({backgroundColor: backcolor, color: textcolor},300).find('.frb_button_icon i').stop(true).animate({color: textcolor},300);
		else
			$(this).stop(true).animate({borderColor: backcolor, color: textcolor},300).find('.frb_button_icon i').stop(true).animate({color: textcolor},300);
	});
	$(document).on('mouseleave','.frb_button', function(){
		var backcolor = $(this).attr('data-backcolor'); if(backcolor == '') backcolor = 'transparent';
		var textcolor = $(this).attr('data-textcolor'); if(textcolor == '') textcolor = 'transparent';
		if(!$(this).hasClass('frb_nofill'))
			$(this).stop(true).animate({backgroundColor: backcolor, color: textcolor},300).find('.frb_button_icon i').stop(true).animate({color: textcolor},300);
		else
			$(this).stop(true).animate({borderColor: backcolor, color: textcolor},300).find('.frb_button_icon i').stop(true).animate({color: textcolor},300);
	});



	/* FEATURES */

	$(document).on('mouseenter','.frb_features', function(){
		var backColor = $(this).attr('data-hovercolor'); if(backColor == '') backColor = 'transparent';
		var titleColor = $(this).find('.frb_features_title').attr('data-hovercolor'); if(titleColor == '') titleColor = 'transparent';
		var iconColor = $(this).find('.frb_features_icon').attr('data-hovercolor'); if(iconColor == '') iconColor = 'transparent';
		var textColor = $(this).find('.frb_features_content').attr('data-hovercolor'); if(textColor == '') textColor = 'transparent';

		$(this).find('.frb_features_title').stop(true).animate({color: titleColor},300);
		$(this).find('.frb_features_icon').stop(true).animate({color: iconColor},300);
		$(this).find('.frb_features_content').stop(true).animate({color: textColor},300);
		if(!$(this).hasClass('frb_features_clean'))
			$(this).stop(true).animate({backgroundColor: backColor},300);
	});


	/* ICON MENU */

	$(document).on('mouseenter','.frb_iconmenu_link', function(){
		var backColor = $(this).attr('data-backhover'); if(backColor == '') backColor = 'transparent';
		var iconColor = $(this).find('i').attr('data-hovercolor'); if(iconColor == '') iconColor = 'transparent';
		$(this).find('i').stop(true).animate({color: iconColor},300);
		$(this).stop(true).animate({backgroundColor: backColor},300);
	});
	$(document).on('mouseleave','.frb_iconmenu_link', function(){
		var backColor = $(this).attr('data-backcolor'); if(backColor == '') backColor = 'transparent';
		var iconColor = $(this).find('i').attr('data-color'); if(iconColor == '') iconColor = 'transparent';
		$(this).find('i').stop(true).animate({color: iconColor},300);
		$(this).stop(true).animate({backgroundColor: backColor},300);
	});


	/* SEARCHFORM */

	$(document).on('focus','.frb_searchform input', function(){
		if($(this).val() == $(this).attr('data-value')) $(this).val('');

		$this = $(this).parent().parent().parent();
		var backColor = $this.attr('data-backfocus'); if(backColor == '') backColor = 'transparent';
		var borderColor = $this.attr('data-borderfocus'); if(borderColor == '') borderColor = 'transparent';
		var textColor = $(this).attr('data-focuscolor'); if(textColor == '') textColor = 'transparent';

		$this.find('i').stop(true).animate({color: textColor},300);
		$this.stop(true).animate({backgroundColor: backColor, borderColor : borderColor},300);
		$(this).stop(true).animate({color: textColor},300);
	});
	$(document).on('blur','.frb_searchform input', function(){
		if($(this).val() == '') $(this).val($(this).attr('data-value'));

		$this = $(this).parent().parent().parent();
		var backColor = $this.attr('data-backcolor'); if(backColor == '') backColor = 'transparent';
		var borderColor = $this.attr('data-bordercolor'); if(borderColor == '') borderColor = 'transparent';
		var textColor = $(this).attr('data-color'); if(textColor == '') textColor = 'transparent';

		$this.find('i').stop(true).animate({color: textColor},300);
		$this.stop(true).animate({backgroundColor: backColor, borderColor : borderColor},300);
		$(this).stop(true).animate({color: textColor},300);
	});
	$(document).on('click', '.frb_searchform .frb_searchright', function(){
		var $input = $(this).parent().find('input');
		if($input.val() != $input.attr('data-value') && $input.val() != ''){
			$(this).parent().submit();
		}

	});


	/*	CONTACT FORM	*/


	$(document).on('submit', '.frb_contact_form > form', function(e){
		e.preventDefault();
		var $this = $(this);
		var sendAllow = true;
		$this.find('.frb_required').each(function(){
			if($(this).val() != '' || $(this).html() != ''){
				sendAllow = sendAllow == true ? true : false;
				$(this).removeClass('frb_req_error');
				if($(this).is('input[name="e-mail"]') && ($(this).val().indexOf('@')<0 || $(this).val().indexOf('.')<0)) {
					sendAllow = false;
					$(this).addClass('frb_req_error');
				}
			} else {
				sendAllow = false;
				$(this).addClass('frb_req_error');
			}

		});

		var $customFieldSelect = $this.find('.frb_input_wrapper input[name="custom"]'),
			submission = {},
			defaults = $this.serializeArray(),
			customName = $customFieldSelect.length > 0 ? $customFieldSelect.attr('placeholder') : null;
			responseText = $('.frb_contact_response').attr('data-text');

		submission = {'action' : 'pbuilder_contact_form', 'defaults' :defaults, 'customname' : customName, 'response' : responseText, 'recipient_email' : $this.data('email'), 'recipient_name' : $this.data('name')};
		if(sendAllow){
			var tempVal = $this.find('.frb_contact_submit > input').val(),
				$submitSel = $this.find('.frb_contact_submit > input');

        console.log($this.data('push_through_flow'));

        if($this.data('push_through_flow') == 'true'){
          var flow_url = $this.data('push_through_flow_url')+'?';
          for(f in defaults){
            flow_url+=defaults[f]['name']+'='+defaults[f]['value']+'&';
          }
          window.location.href = flow_url;
        } else {
			    $submitSel.attr('value', $submitSel.attr('data-proc-val'));
    			$.post(ajaxurl, submission).done(function(){
            if($this.data('redirect_after_sending') == 'true'){
              window.location.href = $this.data('redirect_url');
            } else {
              $this.children(':not(.frb_contact_form_overlay)').animate({opacity : 0.4},400);
      				$this.find('.frb_contact_form_overlay').show().animate({opacity: 1}, 400);
      				setTimeout(function(){
      					$this.children(':not(.frb_contact_form_overlay)').animate({opacity : 1},400);
      					$this.find('.frb_contact_form_overlay').animate({opacity: 0}, 400, function(){$(this).hide();});
      					$submitSel.attr('value', tempVal);
      				}, $this.data('responseDelay'));
            }
    			});
        }
		}
	});

	$(window).resize(function(){
		contactFormResize();
	});

	function contactFormResize(){
		$('.frb_contact_form').each(function(){
			 var $this = $(this),
				$inputWrap = $this.find('.frb_input_wrapper'),
				inputCount = $this.find('.frb_input_wrapper input').length,
				maxItems = Math.floor($this.width()/190),
				maxItems = maxItems < 1 ? 1 : maxItems;

			if(inputCount == 2) {$inputWrap.css('width', '50%');}
			if(inputCount == 3) {$inputWrap.css('width', '33.3333%');}
			if(inputCount%4 == 0 && maxItems >= 2 && maxItems < inputCount) {
				$inputWrap.removeClass('frb_fullw').addClass('frb_halfw');
			} else if(maxItems < inputCount){
				$inputWrap.removeClass('frb_halfw').addClass('frb_fullw');
			} else {$inputWrap.removeClass('frb_halfw frb_fullw');}


		});
	}




	/* IMAGE */

	$(document).on('mouseenter','.frb_image a', function(){
		// $(this).find('.frb_image_hover').stop(true).animate({opacity:$(this).find('.frb_image_hover').attr('data-transparency')},300);
		$(this).find('i.fawesome, i.frb_icon').stop(true).animate({opacity:1},300);
		$this = $(this).parent();


		if($this.find('.frb_image_desc').length >0) {
			var backColor = $this.find('.frb_image_desc').attr('data-backhover'); if(backColor == '') backColor = 'transparent';
			var descColor = $this.find('.frb_image_desc').attr('data-hovercolor'); if(descColor == '') descColor = 'transparent';
			$this.find('.frb_image_desc').stop(true).animate({backgroundColor:backColor, color:descColor},300);
		}

	});
	$(document).on('mouseleave','.frb_image a', function(){
		// $(this).find('.frb_image_hover').stop(true).animate({opacity:0},300);
		$(this).find('i.fawesome, i.frb_icon').stop(true).animate({opacity:0},300);

		if($this.find('.frb_image_desc').length >0) {
			var descColor = $this.find('.frb_image_desc').attr('data-color'); if(descColor == '') descColor = 'transparent';
			var backColor = $this.find('.frb_image_desc').attr('data-backcolor'); if(backColor == '') backColor = 'transparent';
			$this.find('.frb_image_desc').stop(true).animate({backgroundColor:backColor,color:descColor},300);
		}
	});


	/* NAV MENU */

	$(document).on('mouseenter','.frb_menu li', function(){
		var $mainlist = $(this);
		var submenu = false;
		while(!$mainlist.hasClass('frb_menu')) {
			if($mainlist.hasClass('sub-menu')) submenu = true;
			$mainlist = $mainlist.parent();
		}
		var textColor = $mainlist.attr('data-textcolor'); if(textColor == '') textColor = 'transparent';
		var subTextColor = $mainlist.attr('data-subtextcolor'); if(subTextColor == '') subTextColor = 'transparent';
		var hoverColor = $mainlist.attr('data-hovercolor'); if(hoverColor == '') hoverColor = 'transparent';
		var hoverTextColor = $mainlist.attr('data-hovertextcolor'); if(hoverTextColor == '') hoverTextColor = 'transparent';

		if($mainlist.hasClass('frb_menu_horizontal-clean') || $mainlist.hasClass('frb_menu_horizontal-squared') || $mainlist.hasClass('frb_menu_horizontal-rounded')) {
			if(submenu) $(this).children('a').stop(true).animate({color : hoverColor}, 300);
			else $(this).children('a').stop(true).animate({color : hoverTextColor, backgroundColor: hoverColor}, 300);

			if($(this).children('ul').length > 0) {
				$(this).children('ul').stop(true).show().animate({marginTop:'10px', opacity:1}, 300);
			}
		}
		else if($mainlist.hasClass('frb_menu_vertical-clean') || $mainlist.hasClass('frb_menu_vertical-squared') || $mainlist.hasClass('frb_menu_vertical-rounded')) {
			if(submenu) {
				$(this).children('a').stop(true).animate({color : textColor}, 300);
			}
			else {
				$(this).stop(true).animate({backgroundColor: hoverColor}, 300);
				$(this).children('a').stop(true).animate({color : hoverTextColor}, 300);
				$(this).find('ul a').stop(true).animate({color : hoverTextColor}, 300);
			}
		}
		else {
			$(this).children('a').stop(true).animate({color : hoverTextColor, backgroundColor: hoverColor}, 300);
		}
	});


	$(document).on('mouseleave','.frb_menu li', function(){
		var $mainlist = $(this);
		var submenu = false;
		while(!$mainlist.hasClass('frb_menu')) {
			if($mainlist.hasClass('sub-menu')) submenu = true;
			$mainlist = $mainlist.parent();
		}
		var textColor = $mainlist.attr('data-textcolor'); if(textColor == '') textColor = 'transparent';
		var subTextColor = $mainlist.attr('data-subtextcolor'); if(subTextColor == '') subTextColor = 'transparent';
		var hoverColor = $mainlist.attr('data-hovercolor'); if(hoverColor == '') hoverColor = 'transparent';
		var hoverTextColor = $mainlist.attr('data-hovertextcolor'); if(hoverTextColor == '') hoverTextColor = 'transparent';

		if($mainlist.hasClass('frb_menu_horizontal-clean') || $mainlist.hasClass('frb_menu_horizontal-squared') || $mainlist.hasClass('frb_menu_horizontal-rounded')) {
			if(submenu) $(this).children('a').stop(true).animate({color : subTextColor}, 300);
			else $(this).children('a').stop(true).animate({color : textColor, backgroundColor: 'transparent'}, 300);

			if($(this).children('ul').length > 0) {
				$(this).children('ul').stop(true).animate({marginTop:'0px', opacity:0}, 300, function(){ $(this).hide(); });
			}
		}
		else if($mainlist.hasClass('frb_menu_vertical-clean') || $mainlist.hasClass('frb_menu_vertical-squared') || $mainlist.hasClass('frb_menu_vertical-rounded')) {
			if(submenu) {
				$(this).children('a').stop(true).animate({color : subTextColor}, 300);
			}
			else {
				$(this).stop(true).animate({backgroundColor: 'transparent'}, 300);
				$(this).children('a').stop(true).animate({color : textColor}, 300);
			}
		}
		else {
			$(this).children('a').stop(true).animate({color : subTextColor, backgroundColor: 'transparent'}, 300);
		}
	});

	$(document).on('click','.frb_menu_container[class*="dropdown"] .frb_menu_header', function(){
		if(!$(this).hasClass('active')) {
			$(this).addClass('active');
			$(this).parent().find('ul.frb_menu').stop(true).show().animate({marginTop:'10px', opacity:1}, 300);
		}
		else {
			$(this).removeClass('active');
			$(this).parent().find('ul.frb_menu').stop(true).animate({marginTop:'0px', opacity:0}, 300, function(){ $(this).hide(); });
		}
	});


	/* PRICING TABLE */

	$(document).on('click','.frb_pricing_controls a', function(e){
		e.preventDefault();
		var $cont = $(this).closest('.frb_pricing_container');
		var $ctrl = $(this).closest('.frb_pricing_controls');
		if(typeof $cont.data('slide') == 'undefined') $cont.data('slide',0);
		if($(this).hasClass('frb_pricing_left') && $cont.data('slide') !=0 ) {
			$cont.data('slide',$cont.data('slide')-1);
			$cont.find('table').stop(true).animate({'margin-left':(-$cont.data('slide')*100)+'%'},300);
		}
		else if($(this).hasClass('frb_pricing_right') && $cont.data('slide') != parseInt($cont.attr('data-colnum'))-1 ) {
			$cont.data('slide',$cont.data('slide')+1);
			$cont.find('table').stop(true).animate({'margin-left':(-$cont.data('slide')*100)+'%'}, 300);
		}
	});
	$(window).resize(function(){

		if ($('.frb_charts_wrapper canvas').length > 0) {
			$('.frb_charts_wrapper').each(function(ind){
				if ($(this).hasClass('frb_onScreen')) {
					$(this).parent().parent().frbChartsDraw();
					$(this).attr('data-onscreen', true);
				}
			});
		}
		if ($('.frb_graph_wrapper canvas').length > 0) {
			$('.frb_graph_wrapper').each(function(ind){
				if ($(this).hasClass('frb_onScreen')) {
					$(this).parent().parent().frbGraphDraw();
					$(this).attr('data-onscreen', true);
				}
			});
		}
	});

	$('.pbuilder_row').on('row_width_change', function() {
		contactFormResize();
		if ($(this).find('.frb_charts_wrapper canvas').length > 0) {
			$(this).find('.frb_charts_wrapper').each(function(ind){
				if ($(this).hasClass('frb_onScreen')) {
					$(this).parent().parent().frbChartsDraw();
					$(this).attr('data-onscreen', true);
				}
			});
		}
		if ($(this).find('.frb_graph_wrapper canvas').length > 0) {
			$(this).find('.frb_graph_wrapper').each(function(ind){
				if ($(this).hasClass('frb_onScreen')) {
					$(this).parent().parent().frbGraphDraw();
					$(this).attr('data-onscreen', true);
				}
			});
		}
	});

// 			Gallery


$(document).on('click', '.frb_gallery_hover', function(){
	var href = $(this).siblings('.frb_gallery_new_tab_link').attr('href');
	 if(typeof href != 'undefined') {
	 	window.open(href,'_blank');
	 } else {
	 	$(this).siblings('a[rel^="frbprettyPhoto"]').trigger('click');
	 }
});

function frbGalleryInit($this) {
	$this.each(function(){

		var categories_height = 0;
		if ($(this).find('.frb_gallery_categories').length!=0)
		var categories_height = $(this).find('.frb_gallery_categories').outerHeight(true);
		var itemPaddingTop = parseInt($(this).find('.frb_media_file').first().css("padding-top"));

		//$(this).find('.frb_gallery_inner').css('top',categories_height-itemPaddingTop);
		$(this).find('.frb_gallery_cat').each(function(index){
			$(this).click(function(e){
				e.preventDefault();
				$(this).siblings().removeClass('frb_cat_active');
				$(this).addClass('frb_cat_active');
				$(this).closest('.frb_gallery_container').find('.frb_media_file').removeClass('frb_cat_active');

				if (index==0 ) {
					if ($(this).attr('href')=='All')
						$(this).closest('.frb_gallery_container').find('.frb_gallery_inner').isotope({filter: '.frb_media_file'});
					else
						$(this).closest('.frb_gallery_container').find('.frb_gallery_inner').isotope({filter: '.frb_gallery_cat_0'});
				}
				else {
					if ($(this).siblings().first().attr('href')=='All')
						$(this).closest('.frb_gallery_container').find('.frb_gallery_inner').isotope({filter: '.frb_gallery_cat_'+(index-1)});
					else
						$(this).closest('.frb_gallery_container').find('.frb_gallery_inner').isotope({filter: '.frb_gallery_cat_'+index});
				}


			});
		});

	});

}

function galleryShortcodeRefresh($this) {

		 $this.each(function(){

		$(this).find('.frb_cat_active').last().siblings().removeClass('frb_cat_active');//fail safe da samo jedna klasa bude active

		 var index = $(this).find('.frb_cat_active').last().index('.frb_gallery_cat');//indeks aktivne klase ili -1 ako nije nadjena

		var numOfCats = $('.frb_gallery_cat').length;

		var aspect_ratio = $(this).data('frb_aspect_ratio');
		var width = $(this).width();
		var itemWidth = $(this).find('.frb_media_file').width();
		var itemHeight = 0;

		switch(aspect_ratio) {
			case '4:3':
			    itemHeight = itemWidth/1.33;
			    break;
			case '16:9':
			    itemHeight = itemWidth/1.78;
			    break;
			case '16:10':
			    itemHeight = itemWidth/1.6;
			    break;
			case '1:2':
			    itemHeight = itemWidth*2;
			    break;
			default:
			    itemHeight = itemWidth;
			}


			$(this).find('.frb_media_file, .frb_media_file_inner, .frb_media_file_inner video').height(itemHeight+"px");
      
			$(this).find('.frb_media_file_inner img').each(function(){
        
        if(itemHeight>itemWidth){
          
          if($(this).width()<$(this).height()){
            $(this).css('height','auto');
            $(this).width($(this).parent().width());
          } else {
            $(this).css('width','auto');
            $(this).height($(this).parent().height());
          }
        } else {
          $(this).css('width','auto');
          $(this).height($(this).parent().height());          
        }
        
        var picWidth = $(this).width();
				var picHeight = $(this).height();
        
        
				$(this).css({'margin-left' : (itemWidth-picWidth)/2, 'margin-top' : (itemHeight - picHeight)/2});
			});

			//$(this).find('.frb_gallery_inner').isotope({itemSelector: '.frb_media_file'});

			if (numOfCats!=0) {
				if (index==0 ) {
						if ($(this).attr('href')=='All')
							$(this).find('.frb_gallery_inner').isotope({filter: '.frb_media_file'});
						else
							$(this).find('.frb_gallery_inner').isotope({filter: '.frb_gallery_cat_0'});
					}
					else {
						if ($(this).siblings().first().attr('href')=='All')
							$(this).find('.frb_gallery_inner').isotope({filter: '.frb_gallery_cat_'+(index-1)});
						else
							$(this).find('.frb_gallery_inner').isotope({filter: '.frb_gallery_cat_'+index});
					}

					if ($(this).find('.frb_cat_active').length > 0)
						$(this).find('.frb_cat_active').trigger('click');
					else
						$(this).find('.frb_gallery_inner').isotope({filter: '.frb_media_file'});

				} else {
					$(this).find('.frb_gallery_inner').isotope({filter: '.frb_media_file'});
				}


		 });


}

	/*	SLIDER	*/

	$(document).on('mousedown touchstart', '.frb-swiper-container .frb_lightbox_link', function(e){
		var date = new Date();
		$(this).data({'time' : date.getTime()});
		$(this).off('click');
		e.preventDefault();
	});

	$(document).on('mouseup touchend', '.frb-swiper-container .frb_lightbox_link', function(e){
		$(this).on('click', function(e){
			e.preventDefault();
		});
		var date = new Date();
		e.preventDefault();
		var oldTime = $(this).data('time');
		if(date.getTime() - oldTime < 150) {
			$(this).prettyPhoto({deeplinking:false});
		}
	});

	$(window).resize(function(){
		$('.frb-swiper-container').each(function(){
			frbSliderResponsive($(this));
		});
	});

	$(document).on("row_width_change", ".pbuilder_row", function() {
		$(this).find('.frb-swiper-container').each(function(){
			frbSliderResponsive($(this));
		});
	});


	function frbSliderResponsive($this) {
		var swp = $this.data('swiper_controls'),
			calcSPV,
			minWidth = parseInt($this.attr('data-min-res-width')),
			moduleWidth = $this.closest('.pbuilder_module').width(),
			SPV = parseInt($this.attr('data-slidesPerView')),
			maxSPV = Math.floor(moduleWidth/minWidth);


		calcSPV = SPV > maxSPV ? maxSPV : SPV;
		if(typeof swp != 'undefined' && swp.params.mode == 'horizontal') {
			swp.params.slidesPerView=calcSPV;
			swp.reInit();
		}


		var height = 0, hgt = 0;
		$this.find('.swiper-slide').each(function(){
			var content_height = $(this).children().first().innerHeight();

			if (typeof swp != 'undefined' && swp.params.mode != 'horizontal') {
			$(this).height(content_height);
				$(this).css('width','100%');
				if($(this).height() > height) height = $(this).height();
			} else if (typeof swp != 'undefined' && swp.params.mode == 'horizontal') {
				hgt = hgt < $(this).children().outerHeight(true) ? $(this).children().outerHeight(true) : hgt;
//				$this.height(hgt);

			}
		});

		if (typeof swp != 'undefined' && swp.params.mode != 'horizontal') {
			$this.css('height', height*swp.params.slidesPerView+'px');
			$this.find('.swiper-slide').each(function(){
			$(this).height(height);
			});
		}
		if(typeof swp != 'undefined') {swp.reInit();}
	}

	/*	CREATIVE POST SLIDER		*/

	$(document).on('click', '.frb_creative_link_icon', function(){
		$(this).siblings('.frb_creative_post_slider_img_wrapper').trigger('click');
	});

	$(document).on('mousedown touchstart', '.frb_creative_post_slider_img_wrapper[rel^="frbprettyphoto"]', function(e){
		var date = new Date();
		$(this).data({'time' : date.getTime()});
		$(this).off('click');
		e.preventDefault();
	});

	$(document).on('mouseup touchend', '.frb_creative_post_slider_img_wrapper[rel^="frbprettyphoto"]', function(e){
		$(this).on('click', function(e){
			e.preventDefault();
		});
		var date = new Date();
		e.preventDefault();
		var oldTime = $(this).data('time');
		if(date.getTime() - oldTime < 120) {
			$(this).prettyPhoto({deeplinking:false});
		}
	});


	$(document).on('mouseenter', '.frb_creative_post_slide_inner', function(){
		var $this = $(this);
		if(!$this.hasClass('frb_record')) {
			$this.addClass('frb_record').attr('data-height', $this.children('.frb_creative_post_slider_hover').outerHeight());
			$this.children('.frb_creative_post_slider_hover').css('bottom', -$this.children('.frb_creative_post_slider_hover').outerHeight());
		}
		$this.children('.frb_creative_post_slider_hover').stop(true).animate({'bottom' : 0}, 250);
		$this.children('.frb_creative_post_slider_img_wrapper').stop(true).animate({'top' : -40}, 250);

		var iconPos = ($(this).closest('.frb_creative_post_slider_container').height() - parseInt($(this).attr('data-height'))-parseInt($(this).closest('.frb_creative_post_slider_container').attr('data-icnh')))/2;
		$(this).closest('.frb_creative_post_slider_container').data('iconPos', iconPos);
		$this.children('.frb_creative_link_icon').stop(true).animate({'top' : $(this).closest('.frb_creative_post_slider_container').data('iconPos')}, 250);

	});

	$(document).on('mouseleave', '.frb_creative_post_slide_inner', function(){
		$(this).children('.frb_creative_post_slider_hover').stop(true).animate({'bottom' : -parseInt($(this).attr('data-height'))}, 250);
		$(this).children('.frb_creative_post_slider_img_wrapper').stop(true).animate({'top' : 0}, 250);
		var iconPos = ($(this).closest('.frb_creative_post_slider_container').height()+parseInt($(this).closest('.frb_creative_post_slider_container').attr('data-icnh')));
		$(this).children('.frb_creative_link_icon').stop(true).animate({'top' : iconPos}, 250);
	});

	$(window).resize(function(){
		$('.frb_creative_post_slide_inner.frb_record').attr('data-height', $('.frb_creative_post_slide_inner.frb_record > .frb_creative_post_slider_hover').outerHeight())
		$('.frb_creative_post_slide_inner.frb_record > .frb_creative_post_slider_hover').css('bottom', -$('.frb_creative_post_slide_inner.frb_record > .frb_creative_post_slider_hover').outerHeight());
	});

	$('.pbuilder_row').on('row_width_change', function() {
		if($(this).find('.frb_creative_post_slider_container').length > 0) {
			$(this).find('.frb_creative_post_slide_inner.frb_record').attr('data-height', $(this).find('.frb_creative_post_slide_inner.frb_record > .frb_creative_post_slider_hover').children('.frb_creative_post_slider_hover').outerHeight());
			$(this).find('.frb_creative_post_slide_inner.frb_record > .frb_creative_post_slider_hover').css('bottom', -$(this).find('.frb_creative_post_slide_inner.frb_record > .frb_creative_post_slider_hover').outerHeight());
		}
	});


	$(document).ready(function(){
		$('.frb_creative_post_slider_container').each(function(){
			frbCreativePostSlider($(this));
		});
	});

	$(document).on('refresh', '.pbuilder_module', function(){
		if($(this).find('.frb_creative_post_slider_container').length > 0) {
			frbCreativePostSlider($(this).find('.frb_creative_post_slider_container'));
		}
	});

	$(window).resize(function(){
		$('.frb_creative_post_slider_container').each(function(){
			frbCreativePostSliderResize($(this));
		});
	});

	$(document).on("row_width_change", ".pbuilder_row", function() {
		$(this).find('.frb_creative_post_slider_container').each(function(){
			frbCreativePostSliderResize($(this));
		});
	});

	function frbCreativePostSlider($this){
		var settings = {};
		settings['spv'] = parseInt($this.attr('data-spv'));
		settings['asr'] = $this.attr('data-asr');
		settings['rref'] = parseInt($this.attr('data-rref'));
		settings['ratio'] = parseInt(settings['asr'].split(':')[1])/parseInt(settings['asr'].split(':')[0]);
		settings['swiper'] = $this.swiper({
			mode:'horizontal',
			loop: true,
			slidesPerView: settings['spv'],
			resizeReInit : true,
			wrapperClass : 'frb_creative_post_slider_wrapper',
			slideClass: 'frb_creative_post_slide',
			preventLinks : true,
			preventLinksPropagation : true
		});
		$this.data('settings', settings);

		$this.height($this.find('.frb_creative_post_slide:first').width()*settings['ratio']);
		$this.find('.frb_creative_post_slide').height($this.height());
		frbCpsImageCentering($this, $this.find('img.wp-post-image'));

		var iconPos = ($this.height()+parseInt($this.attr('data-icnh')));
		$this.find('.frb_creative_link_icon').css({'top' : iconPos});
		$('.frb_creative_post_slider_img_wrapper[rel^="frbprettyphoto"]').prettyPhoto({deeplinking:false});
	}

	function frbCreativePostSliderResize($this){
		var settings = $this.data('settings'), calculatedNum = Math.floor($this.width()/settings['rref']);
		settings['spvRecalc'] = calculatedNum > settings['spv'] ? settings['spv'] : calculatedNum;
		settings['spvRecalc'] = settings['spvRecalc'] < 1 ? 1 : settings['spvRecalc'];
		settings['swiper'].params.slidesPerView=settings['spvRecalc'];
		settings['swiper'].reInit();
		$this.height($this.find('.frb_creative_post_slide:first').width()*settings['ratio']);
		$this.find('.frb_creative_post_slide').height($this.height());
		var iconPos = ($this.height()+parseInt($this.attr('data-icnh')));
		$this.data('iconPos', iconPos);
		settings['swiper'].setWrapperTranslate(0,0,0);
		frbCpsImageCentering($this, $this.find('img.wp-post-image'));
	}

	function frbCpsImageCentering($wrpThis, $cThis) {
		var width = parseInt($cThis.attr('width')),
			height = parseInt($cThis.attr('height')),
			wrpWidth = parseInt($cThis.closest('.frb_creative_post_slide').css('width')),
			wrpHeight =parseInt($wrpThis.css('height'));
		$cThis.css({'margin-left' : (wrpWidth-width)/2, 'margin-top' : (wrpHeight - height)/2});
	}


//			COUNTER

$.fn.frbScrollingCounters = function() {
		var $this = $(this), rawResultStr = $this.attr('data-result'), rawResult = parseInt(rawResultStr), tempResult = rawResult, curPosVal, fSize = parseInt($this.css('font-size')), rawStartStr = $this.attr('data-startval'), rawStart = parseInt(rawStartStr), tempStart = rawStart, lengthFactor, difference = rawStart - rawResult, directionControl = $this.attr('data-direction');
		$this.css({'height' : fSize+'px', 'line-height' : fSize+'px'});
		$this.append('<div class="frb_clear" />');
		if(directionControl =='' || typeof directionControl == 'undefined'){directionControl = 'auto'}
		if(rawResultStr.length > rawStartStr.length){lengthFactor = rawResultStr.length;} else {lengthFactor = rawStartStr.length;}
		if(difference > 0 && directionControl == 'auto') {directionControl = 'upward';} else if(difference <= 0 && directionControl == 'auto') {directionControl = 'downward';}
		for (i=0; i<lengthFactor; i++) {
			var curPosVal = tempResult%10;
			tempResult = Math.floor(tempResult/10);
			curPosStart = tempStart%10;
			if(curPosStart < 1){curPosStart = 0;}
			tempStart = Math.floor(tempStart/10);
			scrlCounterDigitSetup($this, curPosVal, curPosStart, difference, directionControl);
		}

		$this.children('.frb_scrl_count_digit_wrap').each(function(){
			scrlDigitAnimate($(this), fSize, directionControl);
		});
}

function scrlCounterDigitSetup($scrlSel, CPV, CPS, difference, directionControl) {
	$scrlSel.prepend('<div class="frb_scrl_count_digit_wrap" />');
	if(directionControl == 'upward'){
		var transite = CPS;
		while (transite != CPV) {
			$scrlSel.children('.frb_scrl_count_digit_wrap:first').append('<div>'+transite+'</div>');
			transite++;
			if(transite > 9) {transite = 0;}
		}
		$scrlSel.children('.frb_scrl_count_digit_wrap:first').append('<div>'+transite+'</div>');
	}
	if(directionControl == 'downward'){
		var transite = CPS;
		while (transite != CPV) {
			$scrlSel.children('.frb_scrl_count_digit_wrap:first').prepend('<div>'+transite+'</div>');
			transite--;
			if(transite < 0) {transite = 9;}
		}
		$scrlSel.children('.frb_scrl_count_digit_wrap:first').prepend('<div>'+transite+'</div>');
	}
	$scrlSel.children('.frb_scrl_count_digit_wrap:first').append('<div class="frb_clear" />');
}

function scrlDigitAnimate($digitSel, fSize, directionControl) {
	var targetTop = fSize - $digitSel.height();
	if(directionControl == 'upward'){
		$digitSel.each(function(){
			$(this).css('top', 0).animate({'top' : targetTop}, 2000);;
		});
	}
	if(directionControl == 'downward'){
		$digitSel.each(function(){
			$(this).css('top', targetTop).animate({'top' : 0}, 2000);;
		});
	}
}

//			CHARTS

$.fn.frbPercentageBars = function() {
	var $bar = $(this).find('.frb_pbar_line'),
		$pin = $(this).find('.frb_pbar_pin'),
		wrapWidth = $(this).find('.frb_pbar_single_bar_wrapper').width(),
		pinWidth = $pin.eq(0).outerWidth(),
		numberOfBars = $(this).find('.frb_pbar_single_bar_wrapper').length,
		target = [],
		speed = [],
		direction = [],
		dest = [];

		target = $(this).attr('data-percentage').split('|');
		speed = $(this).attr('data-aspd').split('|'),
		direction = $(this).attr('data-dir').split('|');

	for(i=0; i< numberOfBars; i++) {
		dest[i] = {};
		dest[i] = direction[i] == "rtl" ? {'right' : wrapWidth*Math.abs(parseInt(target[i]))/100 - pinWidth/2} : {'left' : wrapWidth*Math.abs(parseInt(target[i]))/100 - pinWidth/2};
		$bar.eq(i).stop(true).animate({'width' : Math.abs(parseInt(target[i]))+'%'}, parseInt(speed[i]));
		$pin.eq(i).html('<span>'+Math.abs(parseInt(target[i]))+'%</span>').stop(true).animate(dest[i], parseInt(speed[i]), function(){
			$(this).closest('.frb_pbar_single_bar_wrapper').find('h5').stop(true).animate({opacity : 1}, 300);
			$(this).children('span').stop(true).animate({opacity : 1}, 300);
		});
	}
}

$.fn.frbPercentageChart = function() {
	var $this = $(this), width = $this.width(), rad = parseInt($this.attr('data-radius'));
	if(width < rad) {
		$this.find('.frb_perchart_canvas, .frb_perchart_bg').attr({'width' : $this.innerWidth(), 'height' : $this.innerWidth()});
		$this.find('.frb_perchart_percent').height($this.innerWidth());
		frbPercentageChartDraw($this);
	} else {
		$this.find('.frb_perchart_canvas, .frb_perchart_bg').attr({'width' : rad, 'height' : rad});
		$this.find('.frb_perchart_percent').height(rad);
		frbPercentageChartDraw($this);
	}
}

function frbPercentageChartDraw($this) {
	var percent = parseInt($this.attr('data-percent')), cut = 98 - parseInt($this.attr('data-linewidth')), clr = $this.attr('data-barcolor'), bgClr = $this.attr('data-bgcolor'), dgnt = [{value: percent, color:clr}, {value: 100-percent, color:"transparent"}];
	var myChart = new Chart($this.find('.frb_perchart_canvas').get(0).getContext("2d")).Doughnut(dgnt, {
			percentageInnerCutout : cut,
			segmentShowStroke : false,
			animationEasing : "easeOutQuad",
			animationSteps : 75,
			segmentStrokeColor : "transparent",
			onAnimationComplete : function(){
				$this.find('.frb_perchart_percent').animate({opacity : 1}, 300);
			}
		});
		var myChart = new Chart($this.find('.frb_perchart_bg').get(0).getContext("2d")).Doughnut([{value: 100, color:bgClr}], {
			percentageInnerCutout : cut,
			segmentShowStroke : false,
			animation : false,
			segmentStrokeColor : "transparent"
		});
}

$.fn.frbChartsLegendSetup = function(relation) {

			var $this = $(this);
			$this.children('div:not(.frb_clear)').append('<div class="frb_charts_legend" />');
			var $legend = $this.find('.frb_charts_legend'), chSel;

			if ($this.children().children(':first').hasClass('frb_charts_wrapper')) {chSel= false;} else {chSel = true;}
			var chColor, chCaption;

			var chCount = chSel ? relation.datasets.length : relation.length;
			for(i=0;i<chCount; i++){
				chColor = chSel ? relation.datasets[i].strokeColor : relation[i].color;
				chCaption = chSel ? relation.datasets[i].caption : relation[i].caption;
				$legend.append('<div class="frb_charts_legend_row" />').children('.frb_charts_legend_row:last').append('<div /><span />').children('div').css({'background-color' : chColor}).parent().children('span').html(chCaption);
			}
			$this.append('<div class="frb_clear" />');
};


$.fn.frbChartsDraw = function() {
	var $this = $(this), width = $this.innerWidth(), rad = parseInt($this.data('radius'));
	if(width - rad <= parseInt($this.data('font-size'))*0.75*15) {$this.addClass('frb_chart_resp');} else {$this.removeClass('frb_chart_resp');}
	if(width < rad) {
		$this.find('.frb_charts_wrapper canvas').attr('width', $this.innerWidth()).attr('height', $this.innerWidth());
		var myChart = new Chart($(this).find('.frb_piechart_canvas').get(0).getContext("2d")).Doughnut($this.data('obj'),{
			percentageInnerCutout : $this.data('percentageInnerCutout'),
			segmentShowStroke : $this.data('segmentShowStroke'),
			segmentStrokeWidth : parseInt($this.data('segmentStrokeWidth')),
			animationEasing : "easeOutQuad",
			animationSteps : 30,
			segmentStrokeColor : $this.data('segmentStrokeColor'),
			onAnimationComplete : function(){
				$this.find(".frb_charts_legend").children(".frb_charts_legend_row").each(function(ind){
					$(this).delay(ind*150).animate({opacity : 1}, 300);
				});
			}
		});
	} else {
		$this.find('.frb_charts_wrapper canvas').attr('width', rad).attr('height', rad);
		var myChart = new Chart($(this).find('.frb_piechart_canvas').get(0).getContext("2d")).Doughnut($this.data('obj'),{
			percentageInnerCutout : $this.data('percentageInnerCutout'),
			segmentShowStroke : $this.data('segmentShowStroke'),
			segmentStrokeWidth : parseInt($this.data('segmentStrokeWidth')),
			animationEasing : "easeOutQuad",
			animationSteps : 30,
			segmentStrokeColor : $this.data('segmentStrokeColor'),
			onAnimationComplete : function(){
				$this.find(".frb_charts_legend").children(".frb_charts_legend_row").each(function(ind){
					$(this).delay(ind*150).animate({opacity : 1}, 300);
				});
			}
		});
	}
	if($(window).width() < 440*window.devicePixelRatio){$this.addClass('frb_chart_force_centering');} else {$this.removeClass('frb_chart_force_centering');}



}

$.fn.frbGraphDraw = function() {
	var $this = $(this),
		graphData = $(this).data("graphData"),
		curve = $(this).data("curve") == 'true' ? true : false,
		fill = $(this).data("fill") == 'true' ? true : false,
		stroke = $(this).data("barStroke") == 'true' ? true : false,
		wWidth = $this.innerWidth(),
		iWidth = $this.data('itemWidth'),
		typeSel = $this.data('graph_style'),
		legFontSize = $this.data('legend_font_size');
	if(wWidth - iWidth <= parseInt(legFontSize)*0.75*15) {$this.addClass('frb_chart_resp');} else {$this.removeClass('frb_chart_resp');}
	if(wWidth < iWidth) {$this.find('.frb_graph_wrapper canvas').attr('width', wWidth);
	} else {$this.find('.frb_graph_wrapper canvas').attr('width', iWidth);}

	var controlSet = {
			segmentShowStroke : true,
			segmentStrokeWidth : 5,
			animationEasing : "easeOutQuad",
			animationSteps : 75,
			scaleFontColor : $(this).data("scale_font_color"),
			scaleFontSize : 12,
			barShowStroke : stroke,
			scaleFontStyle : "italic",
			bezierCurve : curve,
			datasetFill :  fill,
			animateRotate : true,
			onAnimationComplete : function(){
				$this.find('.frb_charts_legend').children('.frb_charts_legend_row').each(function(ind){
					$(this).delay(ind*150).animate({opacity : 1}, 300);
				});
			} // callback
		}




	if(typeSel == 'line'){
		graphData = new Chart($this.find('.frb_graph_canvas')[0].getContext("2d")).Line(graphData, controlSet);
	} else if(typeSel == 'bar') {
		graphData = new Chart($this.find('.frb_graph_canvas')[0].getContext("2d")).Bar(graphData, controlSet);
	}



}


//			gauge

function frbGaugeChartRefresh(thisReference) {
	var initData, g, gaugeValue, gaugeWidth, parentWidth, sideMargin, topMargin;
	thisReference.find(".frb_gauge_shortcode").each(function(){
		gaugeWidth = $(this).width();
		parentWidth = $(this).parent().width();

		if (gaugeWidth>parentWidth) {
			$(this).width(parentWidth).height(parentWidth*0.6);
		}

		initData = $(this).data("gauge_init");
		gaugeValue = $(this).data("gauge_value");
		g = new JustGage(initData);
		g.refresh(gaugeValue);
		gaugeWidth = $(this).width();
		sideMargin = gaugeWidth*0.17;
		topMargin = gaugeWidth * 0.12;
		$(this).css({"margin-left":"-"+sideMargin+"px","margin-right":"-"+sideMargin+"px", "margin-top":"-"+topMargin+"px" });
	});
}


var frb_gauge_resize;
$(window).resize(function(){
	clearTimeout(frb_gauge_resize);
	frb_gauge_resize = setTimeout(function(){
		var initData, gaugeValue, gaugeWidth, parentWidth;
		$(".frb_gauge_shortcode").each(function(){
			$(this).empty();
			gaugeWidth = $(this).data('gauge_width');
			parentWidth = $(this).parent().width();

			if (gaugeWidth>parentWidth) {
				$(this).width(parentWidth).height(parentWidth*0.6);
			} else {
				$(this).width(gaugeWidth).height(gaugeWidth*0.6);
			}

			initData = $(this).data("gauge_init");
			gaugeValue = $(this).data("gauge_value");
			var g = new JustGage(initData);
			g.refresh(gaugeValue);
		});
	},250);
});


var FRBpointerEventToXY = function(e){
  var out = {x:0, y:0};
  if(e.type == 'touchstart' || e.type == 'touchmove' || e.type == 'touchend' || e.type == 'touchcancel'){
    var touch = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];
    out.x = touch.pageX;
    out.y = touch.pageY;
  } else if (e.type == 'mousedown' || e.type == 'mouseup' || e.type == 'mousemove' || e.type == 'mouseover'|| e.type=='mouseout' || e.type=='mouseenter' || e.type=='mouseleave') {
    out.x = e.pageX;
    out.y = e.pageY;
   } else if ( e.type == 'MSPointerDown' || e.type == 'MSPointerMove' || e.type == 'MSPointerUp') {
     var touch = e.originalEvent;
      out.x = touch.pageX;
      out.y = touch.pageY;
  }
  return out;
};




//			YouTube



pbuilderYoutube = function(domid, videoid, atts){

	var settings = $.extend({
		height : '480',
		width : '600',
		autoplay : true,
		controls : false,
		loop : true,
		mute : true,
		hd : false
	}, atts);
	if(typeof window.onYouTubeIframeAPIReady == 'undefined') {
		window.onYouTubeIframeAPIReady = function() {
			pbuilderYouTubeIframeAPIReady();
		}

	}
	else {
		var oldfunction = window.onYouTubeIframeAPIReady;
		window.onYouTubeIframeAPIReady = function() {
			oldfunction();
			pbuilderYouTubeIframeAPIReady();
		}
	}

		var tag = document.createElement('script');
		tag.id = 'frb_youtube_api';
		tag.src = "https://www.youtube.com/iframe_api";
		var firstScriptTag = document.getElementsByTagName('script')[0];
		firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);




	function pbuilderYouTubeIframeAPIReady() {
		if(videoid != '') {
			function onPlayerReady(event) {
				event.target.playVideo();
				if(settings['mute']) player.mute();
				if(settings['hd']) player.setPlaybackQuality('hd720');
			}

			var ytopts = {
				height: settings['height'],
				width: settings['width'],
				videoId: videoid,
			playerVars: {
					'autoplay': (settings['autoplay'] ? 1 : 0),
					'wmode' : 'transparent',
					'version' : 3
					 },
				events: {
					'onReady': onPlayerReady
				}
			}
			if(!settings['controls']) {
				ytopts['playerVars']['controls'] = 0;
				ytopts['playerVars']['showinfo'] = 0;
				ytopts['playerVars']['disablekb'] = 1;
				ytopts['playerVars']['iv_load_policy'] = 3;
			}
			if(settings['loop']) {
				ytopts['playerVars']['playlist'] = videoid;
				ytopts['playerVars']['loop'] = 1;
			}

			player = new YT.Player(domid, ytopts);
		}
	}
}



})(jQuery);

function checkoptinrequired(field, rules, i, options){
    console.log('check field');
    var message = typeof field.attr('error-message') != "undefined"?field.attr('error-message'):"This field is required";
    if (field.val() == "" || field.val() == field.attr('default-value')) {
        return '* '+message;
    }
}
