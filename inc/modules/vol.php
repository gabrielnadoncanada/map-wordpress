<?php

class Vol
{
	const POST_TYPE = 'vol';

	public function __construct()
	{
		add_action('init', array($this, 'register_vol_post_type'));
		add_action('add_meta_boxes', array($this, 'add_vol_meta_boxes'));
		add_action('save_post_vol', array($this, 'save_vol_meta_data'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_vol_path_scripts'));

	}

	public function enqueue_vol_path_scripts($hook)
	{
		if ('post.php' !== $hook && 'post-new.php' !== $hook) {
			return;
		}

		// Replace the path below with the correct path to your JavaScript file
		$script_path = plugins_url('assets/js/vol-path-admin.js', __FILE__);

		wp_enqueue_script('vol-path-admin', $script_path, array('jquery'), '1.0.0', true);
	}

	public function register_vol_post_type()
	{
		$labels = array(
			'name' => __('Vols', 'textdomain'),
			'singular_name' => __('Vol', 'textdomain'),
		);

		$args = array(
			'labels' => $labels,
			'public' => true,
			'has_archive' => true,
			'supports' => array('title','thumbnail', 'excerpt'),
			'menu_icon' => 'dashicons-location-alt',
			'show_in_rest' => true,
		);

		register_post_type(self::POST_TYPE, $args);
	}

	public function add_vol_meta_boxes()
	{
		add_meta_box(
			'vol_path_data',
			__('Vol Path Data', 'textdomain'),
			array($this, 'vol_path_data_callback'),
			self::POST_TYPE,
			'normal',
			'default'
		);
	}

	public function vol_path_data_callback($post)
	{
		wp_nonce_field(basename(__FILE__), 'vol_nonce');

		$vol_stored_meta = get_post_meta($post->ID);
		var_dump($vol_stored_meta);die();
		?>
		<div id="vol-path-data">
            <h4>Points</h4>
	        <div class="points-container">
	            <?php
	            if (!empty($vol_stored_meta)) {
		            foreach ($vol_stored_meta as $index => $point) {

			            ?>

			            <div class="point" data-index="<?php echo $index; ?>">
                    <input type="text" name="point_lat[]" value="<?php echo esc_attr($point['lat']); ?>" placeholder="Lat">
                    <input type="text" name="point_lng[]" value="<?php echo esc_attr($point['lng']); ?>" placeholder="Lng">
                    <input type="checkbox" name="enable_extra_settings[]" <?php checked($point['enable_extra_settings'], '1'); ?>>
                    <input type="text" name="point_title[]" value="<?php echo esc_attr($point['title']); ?>" placeholder="Title">
                    <textarea name="point_description[]" placeholder="Description"><?php echo esc_textarea($point['description']); ?></textarea>
                    <input type="hidden" name="gallery_photos[]" value="<?php echo esc_attr(json_encode($point['gallery_photos'])); ?>">
				            <!-- Add your media gallery field here -->
                </div>
			            <?php
		            }
	            }
	            ?>
	        </div>
            <button type="button" id="add-point">Add Point</button>
        </div>

		<?php
	}

	public function save_vol_meta_data($post_id)
	{
		// Check if our nonce is set.
		if (!isset($_POST['vol_nonce'])) {
			return $post_id;
		}

		// Verify that the nonce is valid.
		if (!wp_verify_nonce($_POST['vol_nonce'], basename(__FILE__))) {
			return $post_id;
		}

		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return $post_id;
		}

		// Check the user's permissions.
		if ('vol' === $_POST['post_type']) {
			if (!current_user_can('edit_page', $post_id)) {
				return $post_id;
			}
		} else {
			if (!current_user_can('edit_post', $post_id)) {
				return $post_id;
			}
		}

		// Save point data
		$points = [];
		$point_count = count($_POST['point_lat']);
		for ($i = 0; $i < $point_count; $i++) {
			$points[] = [
				'lat' => sanitize_text_field($_POST['point_lat'][$i]),
				'lng' => sanitize_text_field($_POST['point_lng'][$i]),
				'enable_extra_settings' => isset($_POST['enable_extra_settings'][$i]) ? '1' : '0',
				'title' => sanitize_text_field($_POST['point_title'][$i]),
				'description' => sanitize_text_field($_POST['point_description'][$i]),
				'gallery_photos' => array_map('sanitize_text_field', $_POST['gallery_photos']),
			];
		}

		update_post_meta($post_id, 'vol_points', $points);
	}

}

$vol = new Vol();
