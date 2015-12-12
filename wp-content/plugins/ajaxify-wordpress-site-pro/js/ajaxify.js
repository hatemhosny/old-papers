//Version 1.5
(function(window,undefined){

	// Prepare our Variables
	var
		History = window.History,
		$ = window.jQuery,
		document = window.document;

	// Check to see if History.js is enabled for our Browser
	if ( !History.enabled ) return false;

	// Wait for Document
	$(function(){
		// Prepare Variables
		var
			// Application Specific Variables 
			rootUrl = aws_data['rootUrl'],
			contentSelector = aws_data['container_id'],
			$content = $(contentSelector),
			contentNode = $content.get(0),
			// Application Generic Variables 
			$body = $(document.body);

		// Ensure Content
		if ( $content.length === 0 ) $content = $body;

		// Internal Helper
		$.expr[':'].internal = function(obj, index, meta, stack){
			// Prepare
			var
				$this = $(obj),
				url = $this.attr('href')||'',
				isInternalLink;

			// Check link
			isInternalLink = url.substring(0,rootUrl.length) === rootUrl || url.indexOf(':') === -1;

			// Ignore or Keep
			return isInternalLink;
		};

		// HTML Helper
		var documentHtml = function(html){
			// Prepare
			var result = String(html).replace(/<\!DOCTYPE[^>]*>/i, '')
									 .replace(/<(html|head|body|title|script)([\s\>])/gi,'<div id="document-$1"$2')
									 .replace(/<\/(html|head|body|title|script)\>/gi,'</div>');
			// Return
			return result;
		};

		// Ajaxify
		$(document).on('click', 'a:internal:not([href^="#"],[href*="wp-login"],[href*="wp-admin"])', function(event){
			var $this = $(this);
			if ( $this.hasClass('no-ajaxy') ) return; // return if a tag has no-ajaxify class
			
			// Support for fancybox
			if ( aws_data['fancybox'] ) {
				if ( $this.hasClass('fancybox') ) return;
			}
			
			if ( 'undefined' == typeof $this.attr('href') ) return;
			
			var url	= $this.attr('href').toString();
			$('#ajax-search a').attr( 'href', '');

			if ( '#' == url.match(/#/g) ) {
				var urlArray = url.split('#');
				url = urlArray[0];
				$('#ajax-search a').attr( 'href', '#' + urlArray[1]);
				if ( ('' == url) || (url == window.location) ) return;
			}
			// Prepare
			var title 	= $this.attr('title') || null;

			// Continue as normal for cmd clicks etc
			if ( event.which == 2 || event.metaKey ) return true;

			url = decodeURIComponent(url); // fix for non-latin characters

			// Ajaxify this link
			History.pushState(null,title,url);
			event.preventDefault();

			return false;
		});

		// Hook into State Changes
		$(window).bind('statechange',function(){
			// Prepare Variables
			var
			State 		= History.getState(),
			url			= State.url,
			relativeUrl = url.replace(rootUrl,'');

			// Set Loading
			$body.addClass('loading');
			
			// Modify browser title during AJAX
			document.title = 'Loding...';
			
			// Old content slide effect
			if ( 1 == aws_data['transitionupdown'] ) {
				// New content slide effect
				$('.ajaxed-container').animate({width: 'toggle'}, 500);
				
			} else if ( 1 == aws_data['transitionfade'] ) {
				$('<div class="aws-overlay"><div class="loader-inner ' + aws_data['loader'] + '"></div></div>').insertBefore('.ajaxed-container');
				awspro_load_js(rootUrl + 'wp-content/plugins/ajaxify-wordpress-site-pro/js/loaders.css.js');
			}

			// Ajax Request the Traditional Page
			$.ajax({
				url: url,
				async: false,
				success: function(data, textStatus, jqXHR){
					// Prepare
					var
						$data 			= $(documentHtml(data)),
						$dataBody		= $data.find('#document-body:first ' + contentSelector),
						bodyClasses 	= $data.find('#document-body:first').attr('class'),
						containerClasses 	= $dataBody.attr('class'),
						contentHtml, $scripts;
					
					var $menu_list = $data.find(aws_data['mcdc']);
					for ( i = 0; i < aws_data['sub_ajax_container'].length; i++ ) {
						$(aws_data['sub_ajax_container'][i]).html($data.find(aws_data['sub_ajax_container'][i]));
					}

					//Add classes to body
					jQuery('body').attr('class', bodyClasses);
					jQuery(contentSelector).attr('class', containerClasses);

					// Fetch the scripts
					$scripts = $dataBody.find('#document-script');
					if ( $scripts.length ) $scripts.detach();

					// Fetch the content
					contentHtml = $dataBody.html()||$data.html();

					// Update the content
					if ( 1 == aws_data['transitionupdown'] ) {
						$content.stop(true,true);
						$content.html(contentHtml);

						load_inbuilt_script_file(rootUrl);
						load_extra_script_file(rootUrl);
						
						// New content slide effect
						$('.ajaxed-container').animate({width: 'toggle'}, 500, function() {
							if ( '' != $('#ajax-search a').attr( 'href')) {
								$('html, body').animate({
									scrollTop: $($('#ajax-search a').attr( 'href')).offset().top
								}, 1000);
							}
						});
					} else if ( 1 == aws_data['transitionfade'] ) {
						$content.fadeToggle('slow', function() {
							$('.aws-overlay').remove();
							$content.html(contentHtml).fadeToggle('slow', function() {
								load_inbuilt_script_file(rootUrl);
								load_extra_script_file(rootUrl);
								if ( '' != $('#ajax-search a').attr( 'href')) {
									jQuery('html, body').animate({
										scrollTop: jQuery(jQuery('#ajax-search a').attr( 'href')).offset().top
									}, 2000);
								}
							});
						});
					}

					// Update Navigation
					jQuery(aws_data['mcdc']).html($menu_list.html());

					// Update the title
					document.title = $data.find('#document-title:first').text();

					// Add the scripts
					$scripts.each(function(){
						var scriptText = $(this).html();
							
						if ( '' != scriptText ) {
							scriptNode = document.createElement('script');
							scriptNode.appendChild(document.createTextNode(scriptText));
							contentNode.appendChild(scriptNode);
						} else {
							awspro_load_js( $(this).attr('src') );
						}
					});

					//Scroll to the top of ajax container
					if ( '' != aws_data['scrollTop'] ) {
						$('html, body').animate({ scrollTop: $('body').offset().top }, 1000);
					}

					$body.removeClass('loading');

					// Inform Google Analytics of the change
					if ( typeof window.pageTracker !== 'undefined' ) window.pageTracker._trackPageview(relativeUrl);

					// Inform ReInvigorate of a state change
					if ( typeof window.reinvigorate !== 'undefined' && typeof window.reinvigorate.ajax_track !== 'undefined' )
						reinvigorate.ajax_track(url);// ^ we use the full url here as that is what reinvigorate supports
				}
			}); // end ajax
			

		}); // end onStateChange
			            
		// Fixes for ml slider plugin
		if ( aws_data['ml_slider'] ) {
			if ( 'coin' == aws_data['type']) {
				awspro_load_js(rootUrl + 'wp-content/plugins/ml-slider/assets/sliders/coinslider/coin-slider.min.js');
				awspro_load_css(rootUrl + 'wp-content/plugins/ml-slider/assets/sliders/coinslider/coin-slider-styles.css');
				awspro_load_css(rootUrl + 'wp-content/plugins/ml-slider/assets/metaslider/public.css');
			}
			// Nivo
			else if ( 'nivo' == aws_data['type']) {
				awspro_load_js(rootUrl + 'wp-content/plugins/ml-slider/assets/sliders/nivoslider/jquery.nivo.slider.pack.js');
				awspro_load_css(rootUrl + 'wp-content/plugins/ml-slider/assets/sliders/nivoslider/nivo-slider.css');
				awspro_load_css(rootUrl + 'wp-content/plugins/ml-slider/assets/metaslider/public.css');
				awspro_load_css(rootUrl + 'wp-content/plugins/ml-slider/assets/sliders/nivoslider/themes/' + aws_data['theme'] + '/' + aws_data['theme'] + '.css');
			}
			// R slides
			else if ( 'responsive' == aws_data['type']) {
				awspro_load_js(rootUrl + 'wp-content/plugins/ml-slider/assets/sliders/responsiveslides/responsiveslides.min.js');
				awspro_load_css(rootUrl + 'wp-content/plugins/ml-slider/assets/sliders/responsiveslides/responsiveslides.css');
				awspro_load_css(rootUrl + 'wp-content/plugins/ml-slider/assets/metaslider/public.css');
			}
			// flex slider
			else if ( 'flex' == aws_data['type']) {
				awspro_load_js(rootUrl + 'wp-content/plugins/ml-slider/assets/sliders/flexslider/jquery.flexslider-min.js');
				awspro_load_css(rootUrl + 'wp-content/plugins/ml-slider/assets/sliders/flexslider/flexslider.css');
				awspro_load_css(rootUrl + 'wp-content/plugins/ml-slider/assets/metaslider/public.css');
			}
			
		}
		awspro_load_css(rootUrl + 'wp-includes/js/mediaelement/mediaelementplayer.min.css');
		awspro_load_css(rootUrl + 'wp-includes/js/mediaelement/wp-mediaelement.css');
		
		awspro_load_js(rootUrl + 'wp-includes/js/mediaelement/wp-mediaelement.js');
		awspro_load_js(rootUrl + 'wp-includes/js/mediaelement/mediaelement-and-player.min.js');
	}); // end onDomLoad
 
})(window); // end closure

jQuery(document).ready(function($){
	
	//Adding no-ajaxy class to a tags present under ids provided
	$(aws_data['ids']).each(function(){
		$(this).addClass('no-ajaxy');
	});
	
	// Wrap the ajax container
	$( aws_data['container_id'] ).wrap( '<div class="ajaxed-container"></div>' );
	
	//append anchor tag to DOM to make the search in site ajaxify.
	var searchButtonHtml = '<span id="ajax-search" style="display:none;"><a class="customurl" href=""></a></span>';
	$('body').prepend(searchButtonHtml);
	
	//After submitting the search form search the post without refreshing the browser.
	$(aws_data['searchID']).on('submit',
		function(d){
			d.preventDefault();
			var host = aws_data['rootUrl'] + '?s=';
			$('#ajax-search a').attr('href', host + $(this).find('input[type="search"]').val());
			$('#ajax-search a').trigger('click');
		}
	);


	// Support for rt_prettyphoto
	if ( aws_data['rt_prettyphoto'] ) {
		jQuery('a[rel^="prettyPhoto"]').each(function(){
			jQuery(this).addClass('no-ajaxy');
		});
		jQuery('.pp_details a').each(function(){
			jQuery(this).addClass('no-ajaxy');
		});
	}
});


function load_inbuilt_script_file( rootUrl ) {
	if ( aws_data['meteor_slides'] ) {
		awspro_load_js(rootUrl + 'wp-content/plugins/meteor-slides/js/jquery.cycle.all.js');
		awspro_load_js(rootUrl + 'wp-content/plugins/meteor-slides/js/jquery.metadata.v2.js');
		awspro_load_js(rootUrl + 'wp-content/plugins/meteor-slides/js/jquery.touchwipe.1.1.1.js');
		awspro_load_js(rootUrl + 'wp-content/plugins/meteor-slides/js/slideshow.js');
	}

	// Support for contact form 7
	if ( aws_data['contact_form_7'] ) {
		jQuery('div.wpcf7 > form').wpcf7InitForm();
	}
	
	//Adding no-ajaxy class to a tags present under ids provided
	jQuery(aws_data['ids']).each(function(){
		jQuery(this).addClass('no-ajaxy');
	});

	// BuddyPress Support
	if ( aws_data['bp_status'] ) {
		awspro_load_js(rootUrl + 'wp-content/plugins/buddypress/bp-templates/bp-legacy/js/buddypress.js');
		awspro_load_css(rootUrl + 'wp-content/plugins/buddypress/bp-core/deprecated/css/autocomplete/jquery.autocompletefb.min.css');
		awspro_load_js(rootUrl + 'wp-content/plugins/buddypress/bp-core/deprecated/js/autocomplete/jquery.autocompletefb.min.js');
	}
	
	// Support for disqus comment plugin
	if ( ('' != aws_data['disqussite']) && aws_data['disqus']  && jQuery('body').hasClass('single-post') ) {
		awspro_load_js('http://' + aws_data['disqussite'] + '.disqus.com/embed.js');
	}
	// Support for Novo slider
	if ( aws_data['nivo'] ) {
		jQuery('#slider').nivoSlider();
	}

	// Support for fancybox
	if ( aws_data['fancybox'] ) {
		easy_fancybox_handler();
	}

	// Support for rt_prettyphoto
	if ( aws_data['rt_prettyphoto'] ) {
		jQuery('a[rel^="prettyPhoto"]').each(function(){
			jQuery(this).addClass('no-ajaxy');
		});
		jQuery('.pp_details a').each(function(){
			jQuery(this).addClass('no-ajaxy');
		});

		jQuery("a[rel^='prettyPhoto']").prettyPhoto();
	}
	awspro_load_js(rootUrl + 'wp-includes/js/mediaelement/wp-mediaelement.js');
	awspro_load_js(rootUrl + 'wp-includes/js/mediaelement/mediaelement-and-player.min.js');
}

function awspro_load_js( url ) {
	jQuery.getScript( url );
}
function awspro_load_css( url ) {
	jQuery("<link/>", {
			   rel: "stylesheet",
			   type: "text/css",
			   href: url
			}).appendTo("head");
}