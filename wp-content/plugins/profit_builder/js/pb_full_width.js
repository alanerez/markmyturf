(function($){
    "use strict";
    $(document).ready(function(){
        if(typeof pbuilder != "undefined" && pbuilder.page_bg == "bgimage"){
            var theWindow        = $(window),
        	    $bg              = $("#bg"),
        	    aspectRatio      = $bg.width() / $bg.height();
        	                   			
        	theWindow.resize(function(){
                if ((theWindow.width() / theWindow.height()) < aspectRatio)
        		    $bg.removeClass().addClass('bgheight');
        		else
        		    $bg.removeClass().addClass('bgwidth');
        	}).trigger("resize");
        }else if(pbuilder.page_bg == "videoembed"){
            if ( pbuilder.video_bg != 'none' ) {
        		var atts = {
        			mute : ( pbuilder.video_mute == 1 ? true : false ),
        			loop : ( pbuilder.video_loop == 1 ? true : false ),
        			hd : ( pbuilder.video_hd == 1 ? true : false )
        		};
        		pbuilderYoutube( "pb_page_bg_inner", pbuilder.video_bg, atts );
        	}
        }
        
        $("[class*=timed-row-]").each(function(){
            var timed_row = this.className.match(/timed-row-(\d+)/i);
            var duration = timed_row ? parseInt(timed_row[1], 10) : 0;
            $(this).delay(duration).fadeIn();
        });
    });
})(jQuery);