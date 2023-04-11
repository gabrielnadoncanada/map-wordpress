<?php


class Package
{
	const POST_TYPE = 'package';

	public function __construct()
	{
		add_action('init', array($this, 'register_package_post_type'));
		add_action('add_meta_boxes', array($this, 'add_package_meta_boxes'));
		add_action('save_post', array($this, 'save_package_meta_data'));
	}

	public function register_package_post_type()
	{
		$labels = array(
			'name' => __('Packages'),
			'singular_name' => __('Package'),
			'add_new' => __('Add New'),
			'add_new_item' => __('Add New Package'),
			'edit_item' => __('Edit Package'),
			'new_item' => __('New Package'),
			'view_item' => __('View Package'),
			'search_items' => __('Search Packages'),
			'not_found' => __('No packages found'),
			'not_found_in_trash' => __('No packages found in Trash'),
		);

		$args = array(
			'labels' => $labels,
			'public' => true,
			'has_archive' => true,
			'supports' => array('title','thumbnail', 'excerpt'),
			'rewrite' => array('slug' => 'packages'),
			'show_in_rest' => true,
		);

		register_post_type(self::POST_TYPE, $args);
	}

	public function register_package_taxonomy() {
		$labels = array(
			'name' => __('Package Categories'),
			'singular_name' => __('Package Category'),
			'search_items' => __('Search Package Categories'),
			'all_items' => __('All Package Categories'),
			'parent_item' => __('Parent Package Category'),
			'parent_item_colon' => __('Parent Package Category:'),
			'edit_item' => __('Edit Package Category'),
			'update_item' => __('Update Package Category'),
			'add_new_item' => __('Add New Package Category'),
			'new_item_name' => __('New Package Category Name'),
			'menu_name' => __('Package Categories'),
		);

		$args = array(
			'labels' => $labels,
			'hierarchical' => true,
			'public' => true,
			'show_ui' => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_tagcloud' => true,
			'show_in_rest' => true,
		);

		register_taxonomy('package_category', self::POST_TYPE, $args);
	}

	public function add_package_meta_boxes()
	{
		add_meta_box(
			'package_coordinates',
			'Coordinates',
			array($this, 'render_package_coordinates_meta_box'),
			self::POST_TYPE,
			'normal',
			'default'
		);
	}

	public function render_package_coordinates_meta_box($post)
	{
		$coordinates = get_post_meta($post->ID, 'package_coordinates', true);
		$coordinates = $coordinates ? $coordinates : array('lat' => '', 'lng' => '');

		wp_nonce_field('package_coordinates_nonce', 'package_coordinates_nonce_field');

		echo '<p><label for="package_coordinates_lat">Coordinate Lat:</label>';
		echo '<input type="text" id="package_coordinates_lat" name="package_coordinates[lat]" value="' . esc_attr($coordinates['lat']) . '" /></p>';

		echo '<p><label for="package_coordinates_lng">Coordinate Lng:</label>';
		echo '<input type="text" id="package_coordinates_lng" name="package_coordinates[lng]" value="' . esc_attr($coordinates['lng']) . '" /></p>';
	}

	public function save_package_meta_data($post_id)
	{
		if (!isset($_POST['package_coordinates_nonce_field']) || !wp_verify_nonce($_POST['package_coordinates_nonce_field'], 'package_coordinates_nonce')) {
			return;
		}

		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}

		if (!current_user_can('edit_post', $post_id)) {
			return;
		}

		if (isset($_POST['package_coordinates'])) {
			$coordinates = array(
				'lat' => sanitize_text_field($_POST['package_coordinates']['lat']),
				'lng' => sanitize_text_field($_POST['package_coordinates']['lng']),
			);

			update_post_meta($post_id, 'package_coordinates', $coordinates);
		}
	}

	public function register_rest_routes() {
		register_rest_route('package/v1', '/all', array(
			'methods' => 'GET',
			'callback' => array($this, 'get_all_packages'),
			'permission_callback' => '__return_true',
		));

		register_rest_route('package/v1', '/taxonomy/(?P<taxonomy_ids>[\d,]+)', array(
			'methods' => 'GET',
			'callback' => array($this, 'get_packages_by_taxonomy'),
			'permission_callback' => '__return_true',
		));
	}

	private function get_package_data($post_id) {
		$package_coordinates = get_post_meta($post_id, 'package_coordinates', true);

		return array(
			'id' => $post_id,
			'title' => get_the_title($post_id),
			'content' => get_the_content(null, false, $post_id),
			'excerpt' => get_the_excerpt($post_id),
			'thumbnail' => get_the_post_thumbnail_url($post_id),
			'coordinates' => $package_coordinates,
		);
	}

	public function get_all_packages() {
		$args = array(
			'post_type' => self::POST_TYPE,
			'post_status' => 'publish',
			'posts_per_page' => -1,
		);

		$packages_query = new WP_Query($args);
		$packages = array();

		if ($packages_query->have_posts()) {
			while ($packages_query->have_posts()) {
				$packages_query->the_post();

				$package_id = get_the_ID();
				$packages[] = $this->get_package_data($package_id);
			}

			wp_reset_postdata();
		}

		return new WP_REST_Response($packages, 200);
	}

	public function get_packages_by_taxonomy($request) {
		$taxonomy_ids = explode(',', $request['taxonomy_ids']);

		$args = array(
			'post_type' => self::POST_TYPE,
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'tax_query' => array(
				array(
					'taxonomy' => 'package_category',
					'field' => 'term_id',
					'terms' => $taxonomy_ids,
					'operator' => 'IN',
				),
			),
		);

		$packages_query = new WP_Query($args);
		$packages = array();

		if ($packages_query->have_posts()) {
			while ($packages_query->have_posts()) {
				$packages_query->the_post();

				$package_id = get_the_ID();
				$packages[] = $this->get_package_data($package_id);
			}

			wp_reset_postdata();
		}

		return new WP_REST_Response($packages, 200);
	}
}

$package = new Package();

// Register the REST routes
add_action('init', array($package, 'register_package_taxonomy'));
add_action('rest_api_init', array($package, 'register_rest_routes'));