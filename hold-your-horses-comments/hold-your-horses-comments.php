<?php
/*
Plugin Name: Hold Your Horses Comments
Plugin URI: http://www.shaneandpeter.com/
Description: This plugin does not allow people to comment until: 1) the proper time has elapsed or; 2) they have answered the question correctly created by the author.
Version: 1.0
Author: Shane & Peter, Inc.
Author URI: http://www.shaneandpeter.com/
License: GPL2
*/

/*  Copyright 2011  Shane & Peter, Inc.

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
if ( !class_exists( 'Hold_Your_Horses_Comments' ) ) {
	class Hold_Your_Horses_Comments {
		
		const VERSION = '1.0';
		const OPTIONNAME = 'hold_your_horses_options';
		
		private $default_options = '';
		public $current_options;
		public $plugin_dir;
		public $plugin_url;
		public $tempo = 200;
		public $plugin_domain = 'hold-your-horses-comments';
		public $meta_keys = array('_HYHLimit','_HYHEnableQuestion','_HYHQuestion','_HYHAnswer','_HYHHint');
		
		
		function __construct(){
			$this->pluginDir = basename(dirname(__FILE__));
			$this->pluginUrl = WP_CONTENT_URL.'/plugins/'.plugin_basename(dirname(__FILE__));
			
			//Hooks
			register_activation_hook(__FILE__, array($hyhc, 'activate_hyh'));
			register_deactivation_hook(__FILE__, array($hyhc, 'deactivate_hyh'));
			
			//Actions
			add_action('admin_menu', array( $this, 'add_post_meta_box' ));
			add_action('save_post', array($this, 'add_post_meta'), 15);
			add_action('publish_post', array($this, 'add_post_meta'), 15);
			add_action('admin_menu', array($this, 'add_options_page'));
			add_action('init', array($this, 'set_admin_resources'));
			add_action('admin_init', array($this, 'gather_option_changes'));
			add_action('comment_form_top', array($this, 'hold_your_horses'));
			add_action('init', array($this, 'set_user_resources'));
			
			//Filters
			
		}
		
		/**
		 *
		 *
		 */
		function hold_your_horses(){
			global $wpdb;
			
			if (!is_single())
				return;
						
			//Get Defaults
			$options = get_option(Hold_Your_Horses_Comments::OPTIONNAME);
			$post_id = get_the_ID();
			$content = get_the_content($post_id);
			$button_text = 'Wait to Post Comment';
			
			//Get Post Options
			$enabled = get_post_meta($post_id, '_HYHLimit', true);
			$question_enabled = get_post_meta($post_id, '_HYHEnableQuestion', true);
			$set_time = get_post_meta($post_id, '_HYHTime', true);
			$question = get_post_meta($post_id, '_HYHQuestion', true);
			$answer = get_post_meta($post_id, '_HYHAnswer', true);
			$hint = get_post_meta($post_id, '_HYHHint', true);
			
			//Set Global Options
			$bar_color = $options['progressBarColor'];
			$display_bar = $options['displayProgressBar'];
			
			//Calculate average time if no defaults are set
			$avg_time = round(str_word_count(strip_tags($content))*60/$this->tempo);			
			
			if((isset($enabled)&&$enabled != '') && $avg_time>0){
				
				//determine if question is enabled
				$question_go = false;
				if($question_enabled != '')
					$question_go = true;
				
				$formatted_time = $this->format_time($avg_time);
				
				$message = $options['defaultText'];
				
				//Make sure we have a default message
				if($message == '')
					$message = __("This article's average reading time is ");
				
				//Set default question, answer and hint if not set for post
				if($question == '' || $answer == ''){
					$button_text = 'Wait to Post Comment';
					$question = $options['defaultQuestion'];
					$answer = $options['defaultAnswer'];
					$hint = $options['defaultHint'];
					if($question == '' || $answer == '')
						$question_go = false;
				}
				
				//Set the display text
				$text = 'You can comment in <span id="countdown"></span>'; 
				
				if($question_go)  $text .= ' OR you can answer the question below.';
				
				$out .= '<div class="hold-your-horses">';
				$out .= '<span id="average-reding-time-display">'.$message .' '. $formatted_time .'.</span>';
				
				$out .= '<h3>Hold Your Horses!</h3>';
				
				$out .= '<p id="comment-in">'.stripslashes($text).'</p>';
				
				if($question_go){
					$button_text = 'Answer the Question or Wait to Post Comment';
					
					$out .= '<p><span class="qa">Question</span>: '.stripslashes($question).'</p>';
				
					$out .= '<p id="answer-p"><span class="qa">Answer</span>: <input type="text" name="challenge-answer" id="challenge-answer-'.$post_id.'" class="challenge-answer" value="" /><span class="form-submit hyh-submit"><input type="button" name="answer-challenge" id="answer-challenge-'.$post_id.'" class="answer-challenge-submit" value="Answer" /></span></p>';
				
					if($hint != '')
						$out .= '<p class="hint">Hint: <em>'.stripslashes($hint).'</em></p>';
				}
				
				if($display_bar !='no'){
					
					$out .= '					
						<div class="avg_time_border">
							<span id="prog">progress</span>
							<div id="avg_time_bar_in_'.$post_id.'" class="avg_time_bar"></div>
						</div>					
					';
				}
				
				$out .= '<p id="msg"></p>';
				
				$out .= '</div>';
				
				$out .= '
						<style type="text/css">
							.avg_time_bar { background-color:'.$bar_color.';width:0px;height:10px; }
							';
							$useragent = $_SERVER['HTTP_USER_AGENT'];
							if(strpos($useragent, "Safari")!== false)
								$out .= '.hold-your-horses { bottom:119px;height:172px; } .answer-challenge-submit { float:right;margin-right:70px !important;margin-top:3px !important; }';
				$out .= '</style>
						
						<script type="text/javascript">
							jQuery(document).ready(function(){
								
								jQuery("#countdown").countdown({until : '.$avg_time.', onExpiry : release, format : \'MS\', compact : true});
	
								jQuery("#comment").attr("disabled", "disabled");
								jQuery("#commentform #submit").attr("disabled", "disabled").val("'.$button_text.'");
								
								jQuery("#avg_time_bar_in_'.$post_id.'").animate({
									width: "100%"
								}, '.$avg_time.'*1000);
							});
							
							function release(){
									jQuery("#commentform #submit").removeAttr("disabled").val("Post Comment");
									jQuery("#comment").removeAttr("disabled");
									jQuery("#countdown").countdown("destroy");
									jQuery(".avg_time_border").hide();
									jQuery("#comment-in").text("Great, go ahead and comment!");
									jQuery(".hold-your-horses").delay(2000).slideUp();
							}
						</script>
						';
				
				echo $out;
			}
			
		}
		
		/**
		 * Adds options to the edit/write post screen
		 *
		 * @return void
		 */
		function rein_in_comments() {
			global $post, $wpdb;
			$post_id = $post->ID;
			
			foreach($this->meta_keys as $key ) {
				if ($post_id){
					$$key = get_post_meta($post_id, $key, true);
				}
				else{
					$$key = '';
				}
			}
			$sel_bar_yes = ($_HYHDisplayProgrssBar=='yes') ? 'selected="selected"' : '';
			$sel_bar_no  = ($_HYHDisplayProgrssBar=='no')  ? 'selected="selected"' : '';
			
			$enabled = ($_HYHLimit=='') ? '' : ' checked';
			$question_enabled = ($_HYHEnableQuestion=='') ? '' : ' checked';
			
			$the_post = get_post($post_id);
			$post_content = $the_post->post_content;
			$avg_time = $this->format_time(round(str_word_count(strip_tags($post_content))*60/$this->tempo));
			
			include(dirname(__FILE__).'/views/hyh-meta-box.php');
		}
		
		/**
		 * Callback for adding the Meta box to edit/write post screen
		 * @return void
		 */
		function add_post_meta_box( ) {
			add_meta_box('Hold Your Horses Comments', __( 'Hold Your Horses', $this->plugin_domain), array($this, 'rein_in_comments'), 'post', 'normal', 'high');
		}
		
		/**
		 * Adds/removes the meta data.
		 *
		 * @param string $post_id 
		 * @return void
		 */
		function add_post_meta($post_id) {
			if(isset($_POST['original_publish'])||isset($_POST['save'])){
				foreach($this->meta_keys as $key){
					update_post_meta($post_id, $key, $_POST[$key]);
				}
			}
		}
		
		/**
		 * Add Admin options page. Can be overidden by individual post options.
		 * @return void
		 */
		function add_options_page(){
			add_options_page('Hold Your Horses Comments', 'Hold Your Horses', 'administrator', basename(__FILE__), array($this,'display_options_page'));
		}
		
		/**
		 * Callback to Display Admin options page.
		 * @return void
		 */
		function display_options_page(){
			include(dirname(__FILE__).'/views/options.php');
		}
        
        function save_options($options) {
            if (!is_array($options)) {
                return;
            }
            if (update_option(Hold_Your_Horses_Comments::OPTIONNAME, $options) ) {
				$this->current_options = $options;
			} else {
				$this->current_options = get_option(Hold_Your_Horses_Comments::OPTIONNAME);
			}
        }
        
        function remove_options() {
            delete_option(Hold_Your_Horses_Comments::OPTIONNAME);
        }
        
        function gather_option_changes(){
        	if (isset($_POST['saveHoldYourHorsesOptions'])) {
                $options = get_option(Hold_Your_Horses_Comments::OPTIONNAME);
				
				$options['defaultText'] = $_POST['defaultText'];
				$options['defaultQuestion'] = $_POST['defaultQuestion'];
				$options['defaultAnswer'] = $_POST['defaultAnswer'];
				$options['defaultHint'] = $_POST['defaultHint'];
				$options['progressBarColor'] = $_POST['progressBarColor'];
				$options['displayProgressBar'] = $_POST['displayProgressBar'];
				
				$this->save_options($options);
			} // end if
        }
        
        function set_admin_resources(){
        	wp_enqueue_style('hyhAdminStyles', $this->pluginUrl .'/css/hyh-admin.css', array(), Hold_Your_Horses_Comments::VERSION, 'screen');
        	wp_enqueue_script('hyhAdminUtils', $this->pluginUrl .'/js/hyh-admin-utils.js', array('jquery'));
        }
        
        function set_user_resources(){
        	wp_enqueue_style('hyhStyles', $this->pluginUrl .'/css/hyh.css', array(), Hold_Your_Horses_Comments::VERSION, 'screen');
        	wp_enqueue_script('userUtils', $this->pluginUrl .'/js/hyh-utils.js', array('jquery'));
        	wp_enqueue_script('jQueryCountdown', $this->pluginUrl .'/js/jquery.countdown.min.js', array('jquery'));
        }
        
        /**
		 * Helper funciton to format the time string
		 * @return $time
		 */
        function format_time($secs) {
		   $times = array(3600, 60, 1);
		   $time = '';
		   $tmp = '';
		   for($i = 1; $i < 3; $i++) {
			  $tmp = floor($secs / $times[$i]);
			  if($tmp < 1) {
				 $tmp = '00';
			  }
			  elseif($tmp < 10) {
				 $tmp = '0' . $tmp;
			  }
			  $time .= $tmp;
			  if($i < 2) {
				 $time .= ':';
			  }
			  $secs = $secs % $times[$i];
		   }
		   return $time;
		}
		
		/**
		 * Sets internationalization on activation. Mo PO files not created yet 03/23/2011.
		 * @return void
		 */
		function activate_hyhc(){
			if(!load_plugin_textdomain($this->plugin_domain,'/wp-content/languages/'))
				load_plugin_textdomain($this->plugin_domain,basename(dirname(__FILE__)) . '/languages/');
		}//End Activation
		
		/**
		 * Nothing to deactivate
		 * @return void
		 */
		function deactivate_hyhc(){
		
		}//End Deactivation
		
	}//End Class
	
	$hyhc = new Hold_Your_Horses_Comments();
	
} //End If Class exists
?>
