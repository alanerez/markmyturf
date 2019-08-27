(function($){
	var no_exit_redirect = false;
		$(document).ready(function(){

				setTimeout(function () {
					$(window).bind('beforeunload', ShowPopup);
				}, 20);
		});

    function ShowPopup(e){
			var $popup;
			var popupHeight;
			var popupWidth;
			if (no_exit_redirect === false) {
				if ($.browser.mozilla && version_compare($.browser.version,'4.0') && !(!!navigator.userAgent.match(/Trident\/7\./)) ) {
					/*$('body').append('\
		              <div id="pb_exit_redirect_popup" class="pb_exit_redirect_popup">' +
		                  pb_exit_redirect.message.replace(/(\r\n|\n)/g,'<br />') +
		              '</div>'
		          );

		          $popup = $('#pb_exit_redirect_popup');
							popupHeight = $popup.outerHeight();
							popupWidth = $popup.outerWidth();
							$popup.css({
								'margin-top': '-' + popupHeight + 'px',
								'margin-left': '-' + (popupWidth / 2) + 'px'
							});*/
				}

				if (e) {
					e.returnValue = pb_exit_redirect.message;
				}

				console.log(pb_exit_redirect.time);
				setTimeout(function () {
					var url = pb_exit_redirect.url;
					console.log('created redirect');
					$(window).unbind('beforeunload',ShowPopup);
					url += url.indexOf('?') < 0?'?':'&';
					url += 'pb_exit_redirected=true';
					window.location = url;
				}, pb_exit_redirect.time);

				return pb_exit_redirect.message;
		}
	}

	function version_compare(a,b){
		if(a === b){
			return 0;
		}
		a = a.split('.');
		b = b.split('.');
		for(var i=0,len=Math.min(a.length,b.length);i<len;i++){
			if(parseInt(a[i]) > parseInt(b[i]))
				return 1;
			if(parseInt(a[i]) < parseInt(b[i]))
				return -1;
		}
		if(a.length > b.length)
			return 1;
		if(a.length < b.length)
			return -1;
		return 0;
	};
}(jQuery));
