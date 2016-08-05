<?php
/**
 * LI_Widget_Tag_Cloud widget
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if(!class_exists('LI_Widget_Tag_Cloud')) :
	class LI_Widget_Tag_Cloud extends LI_Widget {

		/**
		 * Register widget with WordPress.
		 */
		public function __construct() {
			$this->widget_cssclass    = 'lightspeed-inteleck widget widget_tag_cloud';
			$this->widget_description = __( 'Displays the Lightspeed Retail Tags in a tag cloud.', LSI_PLUGIN_NAME );
			$this->widget_id          = 'lightspeed_inteleck_tag_cloud';
			$this->widget_name        = __( 'Lightspeed Inteleck Tag Cloud', LSI_PLUGIN_NAME );
			$this->settings           = array(
				'title'  => array(
					'type'  => 'text',
					'std'   => __( 'Tags', LSI_PLUGIN_NAME ),
					'label' => __( 'Title', LSI_PLUGIN_NAME )
				)
			);
			parent::__construct();
		}

		/**
		 * Front-end display of widget.
		 *
		 * @see WP_Widget::widget()
		 *
		 * @param array $args     Widget arguments.
		 * @param array $instance Saved values from database.
		 */
		public function widget($args, $instance) {
			extract($args);
			$title  = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
			$before_widget = str_replace("widget-area widget-sidebar", "widget-area widget-sidebar widget-adv-search", $before_widget);
			echo $before_widget;
			if(!empty($title)) {
				echo $before_title.$title.$after_title;
			}
			do_shortcode('[lightspeed_product_tags]');
			echo $after_widget;
		}

		/**
		 * Sanitize widget form values as they are saved.
		 *
		 * @see WP_Widget::update()
		 *
		 * @param array $new_instance Values just sent to be saved.
		 * @param array $old_instance Previously saved values from database.
		 *
		 * @return array Updated safe values to be saved.
		 */
		public function update($new_instance, $old_instance) {
			$instance = array();
			$instance['title'] = strip_tags($new_instance['title']);

			return $instance;
		}

		/**
		 * Back-end widget form.
		 *
		 * @see WP_Widget::form()
		 *
		 * @param array $instance Previously saved values from database.
		 */
		public function form($instance) {
			if(isset($instance['title'])) {
				$title = $instance['title'];
			} else {
				$title = __('Tags', MAS_PLUGIN_SLUG);
			}
			?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
		</p>
		<?php
		}
	} // class MoodysAdvancedSearchWidget
endif;

?>
