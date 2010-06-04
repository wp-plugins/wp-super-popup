<?php
/*
Plugin Name: WP Super Popup
Plugin Script: wp-super-popup.php
Plugin URI: http://www.n2h.it/wp-super-popup
Description: Creates unblockable, dynamic and fully configurable popups for your blog: it is useful for creating subscription popups which can strongly increase your email followers. It works also if WP Super Cache is enabled!
Version: 0.7
License: GPL
Author: Davide Pozza
Author URI: http://www.n2h.it

*/

/*
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
Online: http://www.gnu.org/licenses/gpl.txt
*/

$smp_default_options = array(
'exclusion_list'=>'',
'popup_url' => '',
'cookie_duration'=>'30',
'cookie_num_visits'=>'3',
'popup_height'=>'300',
'popup_width'=>'350',
'popup_opacity'=>'0.5',
'popup_delay'=>'2000',
'popup_speed'=>'700',
'popup_content' => '',
'popup_plain_content' => '',
'show_mode'=>'1',
'load_mode'=>'1',
'messages'=>'',
'enabled' => 1,
'cookie_id' => 'mycookie',
'list_mode' => 3
);

add_option('smp-options',$smp_default_options);

$smp_plugin_url_base = WP_PLUGIN_URL . '/wp-super-popup';

$smp_inline_popup_url = WP_PLUGIN_URL . '/../uploads/smp_popup.html';
$smp_inline_popup_temp_url = WP_PLUGIN_URL . '/../uploads/smp_popup_temp.html';
$smp_inline_popup_file = dirname(__FILE__) . '/../../uploads/smp_popup.html';
$smp_inline_popup_temp_file = dirname(__FILE__) . '/../../uploads/smp_popup_temp.html';

$smp_plain_popup_url = WP_PLUGIN_URL . '/../uploads/smp_plain_popup.html';
$smp_plain_popup_temp_url = WP_PLUGIN_URL . '/../uploads/smp_plain_popup_temp.html';
$smp_plain_popup_file = dirname(__FILE__) . '/../../uploads/smp_plain_popup.html';
$smp_plain_popup_temp_file = dirname(__FILE__) . '/../../uploads/smp_plain_popup_temp.html';

	
/*
if (smp_is_inline())
	add_action('wp_footer', 'smp_add_inline');
*/


if ( !defined('WP_CONTENT_URL') )
    define( 'WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
if ( ! defined( 'WP_PLUGIN_URL' ) )
      define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );


add_action('admin_menu', 'smp_create_menu');
add_action( 'init', 'smp_init' );


function smp_init(){
	global $smp_inline_popup_temp_file, $smp_plain_popup_temp_file, $smp_default_options;
	$options = get_option('smp-options');
	if (count($options) != count($smp_default_options)){
		$merged_options = array_merge((array)$smp_default_options, (array)$options);
		update_option('smp-options', $merged_options);
	}
	if (isset($_POST['smp_content'])){
		if(current_user_can("administrator")) {
			$content = stripslashes($_POST['smp_content']);
			
			if (smp_is_file_writable($smp_inline_popup_temp_file))
				smp_write_file_popup($smp_inline_popup_temp_file, $content);
		}
		die();
	}
	if (isset($_POST['smp_plain_content'])){
		if(current_user_can("administrator")) {
			$content = stripslashes($_POST['smp_plain_content']);
			
			if (smp_is_file_writable($smp_plain_popup_temp_file))
				smp_write_file_popup($smp_plain_popup_temp_file, $content);
		}
		die();
	}
	if (smp_is_page_allowed()){
		add_action('wp_print_styles', 'smp_add_styles');
		add_action('wp_print_scripts', 'smp_add_js');
		add_action('wp_head', 'smp_add_head_code');
	}
	
}

      

function smp_is_page_allowed(){
	$options = get_option('smp-options');
	if ($options['enabled']==0) return false;
	$list_mode = $options['list_mode'];
	$paths = explode("\n", $options['exclusion_list']);
	if ($list_mode == 1){
		//exclusion
		foreach($paths as $path){
			$path = trim($path);
			if (strcmp($path, $_SERVER["REQUEST_URI"]) == 0){
				return false;
			} 
		}
		return true;
	} else if($list_mode == 2){
		//inclusion
		foreach($paths as $path){
			$path = trim($path);
			if (strcmp($path, $_SERVER["REQUEST_URI"]) == 0){
				return true;
			} 
		}
		return false;
	} else {
		return true;	
	}
}

/*
function smp_is_inline(){
	$options = get_option('smp-options');
	return $options['load_mode']==2;
}
*/

function smp_add_head_code(){
	global $smp_plugin_url_base, $smp_inline_popup_url,$smp_plain_popup_url;
	$options = get_option('smp-options');
?>
<script type="text/javascript">
	jQuery.noConflict();
	jQuery(document).ready(function($) {
		$(document).ready(function(){
			var smp_cookie_name_a = 'smp_<?php echo $options['cookie_id']?>_a';//1
			var smp_cookie_name_b = 'smp_<?php echo $options['cookie_id']?>_b';//2
			var smp_cookie_num_visits = <?php echo $options['cookie_num_visits']?>;
			var smp_show_mode = <?php echo $options['show_mode']?>;
			function smp_show_popup(){
				<?php 
				if ($options['load_mode'] == 1){
					$smp_popup_url = $options['popup_url'];
				} else if ($options['load_mode'] == 2){
					$smp_popup_url = $smp_inline_popup_url;
				} else {
					$smp_popup_url = $smp_plain_popup_url;
				}
				?>
				setTimeout(function() { $.fn.colorbox({width:"<?php echo $options['popup_width']?>px", height:"<?php echo $options['popup_height']?>px", iframe:true, opacity:<?php echo $options['popup_opacity']?>, speed:<?php echo $options['popup_speed']?>, href:'<?php echo $smp_popup_url?>'}) }, <?php echo $options['popup_delay']?>);
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
			$(document).ready(function(){
				var date = new Date();
				if (!smp_reset_cookies()){
					c_value_a = $.cookie(smp_cookie_name_a);
					c_value_b = $.cookie(smp_cookie_name_b);
					if (smp_show_mode == 1){
						date.setTime(date.getTime() + (<?php echo $options['cookie_duration']?> * 24 * 60 * 60 * 1000));
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
	});
</script>
<?php 
}


/*
============================================
ADMIN
============================================
*/

function smp_create_menu() {

	//create new top-level menu
	$page = add_menu_page('Super Popup', 'Super Popup', 'administrator', __FILE__, 'smp_settings_page',plugins_url('/images/icon.png', __FILE__));

	add_action('admin_print_scripts-'.$page, 'smp_add_js_admin');
	add_action('admin_print_styles-'.$page, 'smp_add_styles');
	//call register settings function
	add_action( 'admin_init', 'smp_register_mysettings' );
	add_filter('admin_head','smp_add_admin_head_code');

}

function smp_add_styles(){
	global $smp_plugin_url_base;
	wp_enqueue_style('smp_style',$smp_plugin_url_base . '/colorbox.css', array(), mt_rand(), 'all' );
}

function smp_add_js_admin(){
	global $smp_plugin_url_base;
	wp_enqueue_script('jquery');
	wp_enqueue_script('smp_colorbox',	$smp_plugin_url_base . '/jquery.colorbox-min.js', array('jquery'), mt_rand() );
	wp_enqueue_script('smp_cookie',	$smp_plugin_url_base . '/jquery.cookie-min.js',	array('jquery'), mt_rand() );
	wp_enqueue_script('smp_tiny_mce',	$smp_plugin_url_base . '/tiny_mce/tiny_mce.js',	array(), mt_rand() );
	wp_enqueue_script('smp_admin',	$smp_plugin_url_base . '/admin.js', array('smp_tiny_mce'), mt_rand() );
}

function smp_add_js(){
	global $smp_plugin_url_base;
	wp_enqueue_script('jquery');
	wp_enqueue_script('smp_colorbox',	$smp_plugin_url_base . '/jquery.colorbox-min.js', array('jquery'), mt_rand() );
	wp_enqueue_script('smp_cookie',	$smp_plugin_url_base . '/jquery.cookie-min.js',	array('jquery'), mt_rand() );
}


function smp_add_admin_head_code() {
    global $smp_plugin_url_base,$smp_inline_popup_temp_url,$smp_plain_popup_temp_url;
    $options = get_option('smp-options'); 

?>

<script type="text/javascript">
	jQuery.noConflict();
	jQuery(document).ready(function($) {
		$(document).ready(function(){
			
			$("input[rel='reset']").click(function(){
				if (confirm('Are you sure you want to save the options and fully reset the popup cookies already stored on all the browsers?')){
					var id = new Date().getTime();
					$("input[name='smp-options[cookie_id]']").val(id);
					$('#target').submit();
				}
			});
	
			$("input[rel='preview']").click(function(){
				var purl;
				var checkedLoadMode = $("input[name='smp-options[load_mode]']:checked").val();
				
				if (checkedLoadMode == 1){
					purl=$("input[name='smp-options[popup_url]']").val();
				}else if (checkedLoadMode == 2){
					purl='<?php echo($smp_inline_popup_temp_url)?>';
					var content = tinyMCE.get('popup_content').getContent();				
					$.post("/",{smp_content: content});
				} else {
					purl='<?php echo($smp_plain_popup_temp_url)?>';
					var content = $("textarea[name='smp-options[popup_plain_content]']").val();
					$.post("/",{smp_plain_content: content});
				}
				
				setTimeout(
					function() { $.fn.colorbox({
						width:$("input[name='smp-options[popup_width]']").val()+"px", 
						height:$("input[name='smp-options[popup_height]']").val()+"px", 
						iframe:true, 
						opacity:$("input[name='smp-options[popup_opacity]']").val(), 
						speed:$("input[name='smp-options[popup_speed]']").val(), 
						href:purl
					})},
					$("input[name='smp-options[popup_delay]']").val()
				);					
			});
		});
	})
</script>

<?php
}

function smp_register_mysettings() {
	//register our settings
	register_setting( 'smp-settings-group', 'smp-options', 'smp_options_validate' );
}

function smp_write_file_popup($file_name, $content){
	global $smp_plugin_url_base;
	$handle = fopen($file_name,"w");
	fwrite($handle, '<html><head><link type="text/css" media="screen" rel="stylesheet" href="' . $smp_plugin_url_base . '/tiny_mce/themes/advanced/skins/default/content.css" /></head><body>'.$content.'</body></html>');
	fclose($handle);
}

function smp_write_file_plain_popup($file_name, $content){
	global $smp_plugin_url_base;
	$handle = fopen($file_name,"w");
	fwrite($handle, $content);
	fclose($handle);
}

function smp_options_validate($options) {
	global $smp_inline_popup_file,$smp_plain_popup_file;
	$prev_options = get_option('smp-options'); 

	$options['messages'] = '';	
	$messages = '';
	
	if ($options['load_mode']==2){
		if (smp_is_file_writable($smp_inline_popup_file)){
			smp_write_file_popup($smp_inline_popup_file, $options['popup_content']);
		}else {
			$messages .= "Unable to save inline content. Please make sure that the '$smp_uploads_dir' is writable and try again.<br/>";
		}
	}

	if ($options['load_mode']==3){
		if (smp_is_file_writable($smp_plain_popup_file)){
			smp_write_file_plain_popup($smp_plain_popup_file, $options['popup_plain_content']);
		}else {
			$messages .= "Unable to save inline content. Please make sure that the '$smp_uploads_dir' is writable and try again.<br/>";
		}
	}

	if (!is_numeric($options['popup_height'])){
		$messages .= "The field 'Popup Height' requires a numeric value.<br/>";
		$options['popup_height'] = $prev_options['popup_height'];
	}
	if (!is_numeric($options['popup_width'])){
		$messages .= "The field 'Popup Width' requires a numeric value.<br/>";
		$options['popup_width'] = $prev_options['popup_width'];
	}
	if (!is_numeric($options['popup_delay'])){
		$messages .= "The field 'Popup Delay' requires a numeric value.<br/>";
		$options['popup_delay'] = $prev_options['popup_delay'];
	}
	if (!is_numeric($options['popup_speed'])){
		$messages .= "The field 'Popup Speed' requires a numeric value.<br/>";
		$options['popup_speed'] = $prev_options['popup_speed'];
	}
	if (!preg_match('/[0-9\.]/',$options['popup_opacity'])){
		$messages .= "The field 'Popup Opacity' requires a value between 0 and 1 (using a '.' as separator)<br/>";
		$options['popup_opacity'] = $prev_options['popup_opacity'];
	}
	if (!isset($options['enabled'])){
		$options['enabled'] = 0;
	}
	if (strlen($messages) > 0){
		$options['messages'] = $messages;
	}
	return $options;
}

function smp_is_file_writable($filename) {
	if(!is_writable($filename)) {
		if(!@chmod($filename, 0666)) {
			$pathtofilename = dirname($filename);
			if(!is_writable($pathtofilename)) {
				if(!@chmod($pathtoffilename, 0666)) {
					return false;
				}
			}
		}
	}
	return true;
}

function smp_settings_page() {
	$options = get_option('smp-options');
	global $smp_plugin_url_base;
?>
<div class="wrap">
	<h2>WP Super Popup</h2>
	<div style="padding-bottom:10px;margin-top:5px;margin-bottom:10px;border-bottom:1px solid #CCCCCC;">
	by <strong>Davide</strong> of <strong><a target="_blank" href="http://www.n2h.it">N2H</a></strong>
</div>
  <?php      
  if (strlen($options['messages']) > 0){
  echo '<div class="error fade" style="background-color:red;"><p>' . $options['messages'] .'</p></div>';
  }?>

<form id="target" method="post" action="options.php">
    <?php settings_fields( 'smp-settings-group' ); ?>
    <input type="hidden" name="smp-options[cookie_id]" value="<?php echo $options['cookie_id']; ?>" /></td>
		

		<h2>Base Settings</h2>
    <table class="form-table">
        <tr valign="top"><th scope="row"><strong>Status:</strong></th>
           <td><input type="checkbox" <?php echo($options['enabled']==1?'checked':'')?> name="smp-options[enabled]" value="1"> Popup enabled </td>
        </tr>
        <tr valign="top"><th scope="row"><strong>Paths inclusion/exclusion</strong>: type the paths (one for each line).</th>
           <td>
        		<input type="radio" <?php echo($options['list_mode']==3?'checked':'')?> name="smp-options[list_mode]" value="3"> Show the popup on all the pages  <br/>
        		<input type="radio" <?php echo($options['list_mode']==1?'checked':'')?> name="smp-options[list_mode]" value="1"> Don't show the popup for the following paths <br/>
        		<input type="radio" <?php echo($options['list_mode']==2?'checked':'')?> name="smp-options[list_mode]" value="2"> Show the popup only for the following paths  <br/>
           	<textarea name="smp-options[exclusion_list]" rows=10 cols=40><?php echo ($options['exclusion_list'])?></textarea> </td>
        </tr>
        <tr valign="top">
        	<th scope="row"><strong>Show the popup:</strong></th>
        	<td>
        		<input type="radio" <?php echo($options['show_mode']==1?'checked':'')?> name="smp-options[show_mode]" value="1"> Every <input size="5" type="text" name="smp-options[cookie_duration]" value="<?php echo $options['cookie_duration']; ?>" /> days<br/>
        		<input type="radio" <?php echo($options['show_mode']==2?'checked':'')?> name="smp-options[show_mode]" value="2"> For the first <input size="5" type="text" name="smp-options[cookie_num_visits]" value="<?php echo $options['cookie_num_visits']; ?>" /> visits
        	</td>
        </tr>          
       
               
    </table> 
    
		<h2>Popup Content</h2>	

    <table class="form-table">
        <tr valign="top">
        	<th scope="row"><strong>Popup content load mode:</strong><br/>
        		<p class="submit">
					  <input type="button" rel="preview" class="button-primary" value="<?php _e('Live Preview') ?>" />
					  </p>
        		</th>
          <td>
          	<input type="radio" <?php echo($options['load_mode']==1?'checked':'')?> name="smp-options[load_mode]" value="1"> Embed the following URL content:<br/>
          	<input size="70" type="text" name="smp-options[popup_url]" value="<?php echo $options['popup_url']; ?>" />
					  <br/><br/>
          	<input type="radio" <?php echo($options['load_mode']==2?'checked':'')?> name="smp-options[load_mode]" value="2"> Embed the following WYSIWYG content:<br/>
          	<textarea id="popup_content" rows=15 cols=60 name="smp-options[popup_content]"><?php echo $options['popup_content']; ?></textarea>
          	<br/><a href="javascript:smp_toggleEditor('popup_content');">Add/Remove editor</a>
					  <br/><br/>
          	<input type="radio" <?php echo($options['load_mode']==3?'checked':'')?> name="smp-options[load_mode]" value="3"> Embed the following plain HTML content:<br/>
          	<textarea id="popup_plain_content" rows=15 cols=60 name="smp-options[popup_plain_content]"><?php echo $options['popup_plain_content']; ?></textarea>
          	


          </td>

        </tr>
        
    </table>
    

		<h2>Visual Effects Settings</h2>
    <table class="form-table">
        <tr valign="top">
        <th scope="row"><strong>Background Opacity</strong> (between 0 and 1):</th>
        <td><input size="5" type="text" name="smp-options[popup_opacity]" value="<?php echo $options['popup_opacity']; ?>" /></td>
        </tr>         
        <tr valign="top">
        <th scope="row"><strong>Popup Height</strong>:</th>
        <td><input size="5" type="text" name="smp-options[popup_height]" value="<?php echo $options['popup_height']; ?>" />px</td>
        </tr>         
        <tr valign="top">
        <th scope="row"><strong>Popup Width</strong>:</th>
        <td><input size="5" type="text" name="smp-options[popup_width]" value="<?php echo $options['popup_width']; ?>" />px</td>
        </tr>         
        <tr valign="top">
        <th scope="row"><strong>Popup Delay</strong>:</th>
        <td><input size="5" type="text" name="smp-options[popup_delay]" value="<?php echo $options['popup_delay']; ?>" />ms</td>
        </tr>         
        <tr valign="top">
        <th scope="row"><strong>Popup Speed</strong>:</th>
        <td><input size="5" type="text" name="smp-options[popup_speed]" value="<?php echo $options['popup_speed']; ?>" />ms</td>
        </tr>         
        
    </table>

    <p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
	  <input type="button" rel="reset" class="button-primary" value="<?php _e('Save Changes and Reset Cookies') ?>" />
    </p>

</form>
</div>
<?php } 

function smp_debug($msg) {
    $today = date("Y-m-d H:i:s ");
    $myFile = dirname(__file__) . "/debug.log";
    $fh = fopen($myFile, 'a') or die("Can't open debug file. Please manually create the 'debug.log' file ");
    $ua_simple = preg_replace("/(.*)\s\(.*/","\\1",$_SERVER['HTTP_USER_AGENT']);
    fwrite($fh, $today . " [from: ".$_SERVER['REMOTE_ADDR']."|$ua_simple] - " . $msg . "\n");
    fclose($fh);
}

?>