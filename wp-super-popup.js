jQuery(document).ready(function($) {
		$(document).ready(function(){
			
			var smp_cookie_name_a = smp_vars.cookie_id + '_a';
			var smp_cookie_name_b = smp_vars.cookie_id + '_b';
			var smp_cookie_num_visits = smp_vars.cookie_num_visits;
			var smp_show_mode = smp_vars.show_mode;
			var smp_popup_url = smp_vars.popup_url;
			
			function smp_show_popup(){
				setTimeout(function() 	{ 
											$.colorbox({
											fixed: true,
											width:smp_vars.popup_width+"px", 
											height:smp_vars.popup_height+"px", 
											iframe:true, 
											opacity:smp_vars.popup_opacity, 
											speed:smp_vars.popup_speed, 
											overlayClose:smp_vars.overlay_close, 
											href:smp_popup_url}) 
										}, 
										smp_vars.popup_delay);
			}
			function smp_reset_cookies(){
				c_value_a = $.cookie(smp_cookie_name_a);
				c_value_b = $.cookie(smp_cookie_name_b);
				if (smp_show_mode == 1 && c_value_b != null){
					$.cookie(smp_cookie_name_b, null, { path: '/', expires: 0 });
					return true;
				} else if (smp_show_mode == 2 && c_value_a != null){
					$.cookie(smp_cookie_name_a, null, { path: '/', expires: 0 });
					return true;
				} else {
					return false;
				}
			}

			var date = new Date();
			if (!smp_reset_cookies()){
				c_value_a = $.cookie(smp_cookie_name_a);
				c_value_b = $.cookie(smp_cookie_name_b);
				if (smp_show_mode == 1){
					date.setTime(date.getTime() + (smp_vars.cookie_duration * 24 * 60 * 60 * 1000));
					c_value = c_value_a;
					smp_cookie_name = smp_cookie_name_a;
				} else if (smp_show_mode == 2){
					date.setTime(date.getTime() + (100000 * 24 * 60 * 60 * 1000));
					c_value = c_value_b;
					smp_cookie_name = smp_cookie_name_b;
				}
				if (c_value == null){	
			    $.cookie(smp_cookie_name, '0', { path: '/', expires: date });
					smp_show_popup();
				} else {
					//cookie exists
					if (smp_show_mode == 2){
						date.setTime(date.getTime() + (100000 * 24 * 60 * 60 * 1000));
						c_value++;
						$.cookie(smp_cookie_name, c_value, { path: '/', expires: date });
						if (c_value < smp_cookie_num_visits){
							smp_show_popup();
						}
					}
				}
			}
		});
	});
