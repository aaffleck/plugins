<?php
/**
 * MoodysAdvancedSearchWidget widget
 */
if(!class_exists('MoodysAdvancedSearchWidget')) :
	class MoodysAdvancedSearchWidget extends WP_Widget {

		/**
		 * Register widget with WordPress.
		 */
		public function __construct() {
			parent::__construct(
				'moodys_advanced_search_widget', // Base ID
				'Moodys Advanced Search Widget', // Name
				array('description' => __('Displays the Moodys Advanced Search bar', MAS_PLUGIN_SLUG),) // Args
			);
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
			$title = apply_filters('widget_title', $instance['title']);
			$before_widget = str_replace("widget-area widget-sidebar", "widget-area widget-sidebar widget-adv-search", $before_widget);
			echo $before_widget;
			if(!empty($title)) {
				echo $before_title.$title.$after_title;
			}
			moodys_advanced_search_bar();
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
				$title = __('Search', MAS_PLUGIN_SLUG);
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
