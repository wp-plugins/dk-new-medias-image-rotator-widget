<?php
	/**
	Plugin Name: DK New Media's Image Rotator Widget
	Plugin URI: http://www.dknewmedia.com
	Description: A sidebar widget for rotating images utilizing jQuery. Built by <a href="http://dknewmedia.com">DK New Media</a>.
	Version: 0.2.6
	Author: Stephen Coley, Douglas Karr
	Author URI: http://www.dknewmedia.com

	Copyright 2011  DK New Media  (email : doug@dknewmedia.com)

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


	
	/**
	 * Script & stype loader for widget.php
	 */
	function irw_admin_actions($hook) {
		if('widgets.php' != $hook) {
			return;
		}
		// Scripts
		if(function_exists('wp_enqueue_media')) {
			wp_enqueue_media();
		} else {
			wp_enqueue_style('thickbox');
			wp_enqueue_script('media-upload');
			wp_enqueue_script('thickbox');
		}
				
		wp_enqueue_script('jquery-ui-sortable');
		wp_register_script('irw-js', path_join(WP_PLUGIN_URL, basename( dirname( __FILE__ ) ).'/js/main.js'), array('jquery','media-upload','thickbox'));
		wp_enqueue_script('irw-js');
		wp_register_script('irw-qtip', path_join(WP_PLUGIN_URL, basename( dirname( __FILE__ ) ).'/js/jquery.qtip.js'), array('jquery','media-upload','thickbox'));
		wp_enqueue_script('irw-qtip');

		// Styles
		wp_register_style('irw-css', path_join(WP_PLUGIN_URL, basename( dirname( __FILE__ ) ).'/css/main.css'));
		wp_enqueue_style('irw-css');
	}

	/**
	 * Script & style loader for the actual widget
	 */
	function irw_widget_actions() {

		// Scripts
		wp_enqueue_script('jquery'); 
		wp_register_script('jquery-imagesloaded', path_join(WP_PLUGIN_URL, basename(dirname(__FILE__)).'/js/jquery.imagesloaded.js'));
		wp_enqueue_script('jquery-imagesloaded');
		wp_register_script('irw-widget', path_join(WP_PLUGIN_URL, basename(dirname(__FILE__)).'/js/dk-image-rotator-widget.js'));
		wp_enqueue_script('irw-widget');

		// Styles
		wp_register_style('irw-widget', path_join(WP_PLUGIN_URL, basename(dirname(__FILE__)).'/css/dk-image-rotator-widget.css'));
		wp_enqueue_style('irw-widget');
	}

	/**
	 * Hooks & schizzz
	 */
	add_action('admin_enqueue_scripts', 'irw_admin_actions');
	add_action('wp_enqueue_scripts', 'irw_widget_actions');
	add_action('widgets_init', create_function('', 'register_widget("DK_Image_Rotator_Widget");'));

	/**
	 * DK_Image-Rotator-Widget Class
	 */
	class DK_Image_Rotator_Widget extends WP_Widget {

		function DK_Image_Rotator_Widget() {
			$this->WP_Widget('dk-image-rotator-widget', 'Image Rotator Widget', array('description' => 'A widgetized, bare bones image rotator.'));
		}

		function widget($args, $instance) {
			extract($args, EXTR_SKIP);
			$image_list = $instance['irw_images'];

			// If $image_list is set and not empty...
			if(isset($image_list) && $image_list != "") {
				$images = explode(", ", $image_list);
				$irw_title = apply_filters('widget_title', $instance['irw_title']);
				$transition = $instance['irw_transition'];
				$transition_speed = $instance['irw_transition_speed'];
				$no_follow = $instance['irw_nofollow'];
				$new_window = $instance['irw_new_window'];
				/*if($new_window === "true") {
					$new_window = 'target="_blank"';
				} else {
					$new_window = '';
				}*/
				$new_window = ($new_window === 'true') ? 'target="_blank"' : '';
				echo $before_widget;
				if ( !empty( $irw_title ) ) { echo $before_title . $irw_title . $after_title; }
				echo '<div class="irw-widget">';
				echo '<input type="hidden" class="irw-transition" value="' . $transition . '" />';
				echo '<input type="hidden" class="irw-transition-speed" value="' . $transition_speed . '" />';
				echo '<input type="hidden" class="irw-new-window" value="' . $new_window . '" />';
				echo '<ul class="irw-slider">';
				// Loop through images
				foreach($images as $image) {
					$a = explode("|", $image);
					$image_path = str_replace(get_bloginfo('url'), $_SERVER['DOCUMENT_ROOT'], $a[0]);
					$sizes = getimagesize($image_path);
					if(count($a) > 1 && $a[0] != "" && $a[1] != "") {
						$nofollow = (isset($no_follow) && $no_follow === 'true') ? 'rel="nofollow"' : '';
						echo '<li><a href="' . $a[1] . '" ' . $new_window . ' '  . $nofollow . '><img src="' . $a[0] . '" width="' . $sizes[0] . '" height="' . $sizes[1] . '" class="pointer_cursor" /></a></li>';
					} else {
						echo '<li><img src="' . $a[0] . '" width="' . $sizes[0] . '" height="' . $sizes[1] . '"/></li>';
					}
				}
				echo '</ul></div>';
				echo $after_widget;
			// Else
			} else {
				echo '<p>You must add an image in the Widget Settings.</p>';
			}
		}

		function update($new_instance, $old_instance) {
			$instance = $old_instance;
			$instance['irw_title'] = strip_tags($new_instance['irw_title']);
			$instance['irw_images'] = strip_tags($new_instance['irw_images']);
			$instance['irw_transition'] = strip_tags($new_instance['irw_transition']);
			$instance['irw_transition_speed'] = strip_tags($new_instance['irw_transition_speed']);
			$instance['irw_nofollow'] = strip_tags($new_instance['irw_nofollow']);
			$instance['irw_new_window'] = strip_tags($new_instance['irw_new_window']);
			return $instance;
		}

		function form($instance) {

			$defaults = array( 'irw_images' => '', 'irw_nofollow' => 'false', 'irw_new_window' => 'false' );
			$instance = wp_parse_args((array) $instance, $defaults);

			if ($instance) {
				$irw_title = esc_attr($instance['irw_title']);
				$irw_images = esc_attr($instance['irw_images']);
				$irw_transition = esc_attr($instance['irw_transition']);
				$irw_transition_speed = esc_attr($instance['irw_transition_speed']);
				$irw_nofollow = esc_attr($instance['irw_nofollow']);
				$irw_new_window = esc_attr($instance['irw_new_window']);
			} ?>

			<h5 class="irw_header">Options</h5>

			<p>
				<label for="<?php echo $this->get_field_id('irw_title'); ?>"><?php _e('Title:'); ?></label> 
				<input class="widefat" id="<?php echo $this->get_field_id('irw_title'); ?>" name="<?php echo $this->get_field_name('irw_title'); ?>" type="text" value="<?php echo $irw_title; ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_name('irw_transition'); ?>">Transition: </label>
				<select class="widefat" name="<?php echo $this->get_field_name('irw_transition'); ?>" id="<?php echo $this->get_field_id('irw_transition'); ?>">
					<option <?php if($irw_transition == "linear") { echo 'selected="selected"'; } ?> value="linear">Linear</option>
					<option <?php if($irw_transition == "loop") { echo 'selected="selected"'; } ?> value="loop">Loop</option>
					<option <?php if($irw_transition == "fade") { echo 'selected="selected"'; } ?> value="fade">Fade</option>
				</select>
			</p>
			<p>
				<label for="<?php echo $this->get_field_name('irw_transition_speed'); ?>">Transition Speed: </label>
				<select class="widefat" name="<?php echo $this->get_field_name('irw_transition_speed'); ?>" id="<?php echo $this->get_field_id('irw_transition_speed'); ?>">
					<option <?php if($irw_transition_speed == "1") { echo 'selected="selected"'; } ?> value="1">1 - Fastest</option>
					<option <?php if($irw_transition_speed == "2") { echo 'selected="selected"'; } ?> value="2">2</option>
					<option <?php if($irw_transition_speed == "3") { echo 'selected="selected"'; } ?> value="3">3</option>
					<option <?php if($irw_transition_speed == "4") { echo 'selected="selected"'; } ?> value="4">4</option>
					<option <?php if($irw_transition_speed == "5") { echo 'selected="selected"'; } ?> value="5">5</option>
					<option <?php if($irw_transition_speed == "6") { echo 'selected="selected"'; } ?> value="6">6</option>
					<option <?php if($irw_transition_speed == "7") { echo 'selected="selected"'; } ?> value="7">7</option>
					<option <?php if($irw_transition_speed == "8") { echo 'selected="selected"'; } ?> value="8">8</option>
					<option <?php if($irw_transition_speed == "9") { echo 'selected="selected"'; } ?> value="9">9</option>
					<option <?php if($irw_transition_speed == "10") { echo 'selected="selected"'; } ?> value="10">10 - Slowest</option>
				</select>
			</p>
			<p>
				<label for="<?php echo $this->get_field_name('irw_nofollow'); ?>">Apply nofollow: </label>
				<select class="widefat" name="<?php echo $this->get_field_name('irw_nofollow'); ?>" id="<?php echo $this->get_field_id('irw_nofollow'); ?>">
					<option <?php if($irw_nofollow == "true") { echo 'selected="selected"'; } ?> value="true">True</option>
					<option <?php if($irw_nofollow == "false") { echo 'selected="selected"'; } ?> value="false">False</option>
				</select>
			</p><p>
				<label for="<?php echo $this->get_field_name('irw_new_window'); ?>">Open in New tab/window: </label>
				<select class="widefat" name="<?php echo $this->get_field_name('irw_new_window'); ?>" id="<?php echo $this->get_field_id('irw_new_window'); ?>">
					<option <?php if($irw_new_window == "true") { echo 'selected="selected"'; } ?> value="true">True</option>
					<option <?php if($irw_new_window == "false") { echo 'selected="selected"'; } ?> value="false">False</option>
				</select>
			</p>

			<h5 class="irw_header">Images</h5>
			
			<ul class="irw_images">
				<?php // If there are images, loop through them and echo out a list item for each one ?>
				<?php if(isset($irw_images) && $irw_images != "") : ?>
					<?php $images = explode(", ", $irw_images); // str to array ?>
					<?php $i = 1; // counter ?>
					<?php foreach($images as $image) : // Loop through images ?>
						<?php
							$a = explode("|", $image);
							if(count($a) > 1) {
								$image = $a[0];
								$image_link = $a[1];
							} else {
								$image = $a[0];
								$image_link = "";
							}
						?>
						<li data-url="<?php echo $image; ?>" data-link="<?php echo $image_link; ?>" ><span><?php $arr = explode("/", $image); $i = count($arr); echo $arr[$i - 1]; ?></span> <button class="button irw_button"> - </button></li>
					<?php endforeach; ?>
				<?php // Else ?>
				<?php else : ?>
					<?php $images = array(); ?>
				<?php endif; ?>
			</ul>
			<p style="width: 226px;" class="add_image text_align_right <?php if(count($images) < 1) { echo "alert"; } ?>">
				<?php $version = explode("-", get_bloginfo('version')); ?>
				<?php $dep = ($version >= 3.5) ? "" : "_dep"; ?>
				<button class="button add-image-button irw_button" onclick="media_dialog<?php echo $dep; ?>(this); return false;">+</button>
			</p>
			<input type="hidden" id="<?php echo $this->get_field_id('irw_images'); ?>" class="image_list" name="<?php echo $this->get_field_name('irw_images'); ?>" value="<?php echo $irw_images; ?>" />

			<script type="text/javascript">
				jQuery(function(){
					irw_load();
				});
			</script>

		<?php }

	} ?>
