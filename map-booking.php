<?php
/*
Plugin Name: Booking Map
Description: Customize WordPress with powerful, professional and intuitive fields.
Version: 1.0
Author: Gabriel Nadon
Author URI: https://www.advancedcustomfields.com

*/

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

class MapBooking
{
	public $plugin;

	public function __construct()
	{
		$this->plugin = plugin_basename(__FILE__);
	}

	public function register()
	{
		add_action('admin_menu', array($this, 'add_admin_page'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
		add_filter("plugin_action_links_$this->plugin", array($this, 'settings_link'));
		add_action('init', array($this, 'handle_ipn'));
		add_action('wp_ajax_update_map_data', array($this, 'update_map_data'));
		add_action('wp_ajax_nopriv_update_map_data', array($this, 'update_map_data'));
		add_action('wp_ajax_remove_map_data', array($this, 'remove_map_data'));
		add_action('wp_ajax_nopriv_remove_map_data', array($this, 'remove_map_data'));
	}

	public function settings_link($links)
	{
		$settings_link = '<a href="admin.php?page=booking_plugin">Settings</a>';
		array_push($links, $settings_link);
		return $links;
	}

	public function enqueue_assets()
	{
		if (is_admin()) {
			wp_enqueue_style("map-booking-admin-style", plugins_url('templates/admin/src/styles.css', __FILE__));
			wp_enqueue_style("leaflet-style", 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css');
			wp_enqueue_style("leaflet-geoman-style", 'https://unpkg.com/@geoman-io/leaflet-geoman-free@latest/dist/leaflet-geoman.css');
			wp_enqueue_script('leaflet', plugins_url('templates/admin/src/leaflet.min.js', __FILE__), [], '1.0', true);
			wp_enqueue_script('leaflet-geoman', 'https://unpkg.com/@geoman-io/leaflet-geoman-free@latest/dist/leaflet-geoman.min.js', [], '1.0', true);
			wp_enqueue_script('turf', 'https://unpkg.com/@turf/turf@6/turf.min.js', [], '1.0', true);
			wp_enqueue_script('map-booking-admin', plugins_url('templates/admin/src/main.js', __FILE__), ['jquery'], '1.0', true);

			$map_data = $this->get_map_data();

//			dd($map_data);
			// Pass the data to your JavaScript code
			wp_localize_script('map-booking-admin', 'MyScriptData', array(
				'ajaxurl' => admin_url('admin-ajax.php'),
				'map_data' => $map_data,
			));
		} else {
			wp_enqueue_script('my-plugin-frontend', plugins_url('dist/frontend/frontend.js', __FILE__), [], '1.0', true);
		}
	}



	public function get_map_data()
	{
		return get_option('map_data', array());
	}

	public function my_plugin_load_esm($tag, $handle, $src)
	{
		if ('my-plugin-frontend' === $handle || 'my-plugin-admin' === $handle) {
			$tag = '<script type="module" src="' . esc_url($src) . '"></script>';
		}
		return $tag;
	}

	public function update_map_data()
	{
		if (!isset($_POST['data'])) {
			wp_send_json_error('Data not provided');
		}

		$data = $_POST['data'];

		dd($data);
		$map_data = get_option('map_data', array());

		// Update the data in the array
		$map_data[$data['id']] = $data;

		update_option('map_data', $map_data);

		wp_send_json_success('Data updated');
	}

	public function remove_map_data()
	{
		if (!isset($_POST['currentid'])) {
			wp_send_json_error('ID not provided');
		}

		$currentid = $_POST['currentid'];
		$map_data = get_option('map_data', array());

		if (isset($map_data[$currentid])) {
			unset($map_data[$currentid]);
			update_option('map_data', $map_data);
			wp_send_json_success('Data removed');
		} else {
			wp_send_json_error('Data not found');
		}
	}

	public function add_admin_page()
	{
		add_menu_page("Booking", 'Booking', 'manage_options', 'booking_plugin', array($this, 'admin_index'), '');
	}

	public function admin_index()
	{
		require_once plugin_dir_path(__FILE__) . 'templates/admin/index.php';
	}

	public function handle_ipn()
	{
		if (!isset($_GET['my_paypal_ipn_listener']) || !isset($_POST['txn_id'])) {
			return;
		}

		// Read the post data and build the request string
		$req = 'cmd=_notify-validate';
		foreach ($_POST as $key => $value) {
			$value = urlencode(stripslashes($value));
			$req .= "&$key=$value";
		}

		// Set up the PayPal request
		$url = 'https://ipnpb.paypal.com/cgi-bin/webscr';

		// Use WordPress HTTP API
		$args = array(
			'method' => 'POST',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.1',
			'blocking' => true,
			'headers' => array('Connection' => 'Close'),
			'body' => $req,
			'sslverify' => true
		);

		// Send the request and process the response
		$response = wp_remote_post($url, $args);

		// Check for errors
		if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200) {
			return;
		}

		// Get the response body
		$res = wp_remote_retrieve_body($response);

		if (strcmp($res, "VERIFIED") == 0) {
			// Check if the payment_status is Completed
			if ($_POST['payment_status'] != 'Completed') {
				return;
			}

			// Check if the receiver_email is correct
			if ($_POST['receiver_email'] != 'PAYPAL_BUSINESS_EMAIL') {
				return;
			}

			// Trigger the custom action for successful payment
			do_action('payment_success', $_POST);
		}
	}

	public function handle_submit_order(WP_REST_Request $request)
	{
		// You can refer to the WooCommerce REST API PHP library or the WooCommerce documentation for this
		// part. Here's an example using the wc_create_order() function:

		// Check if WooCommerce is active
		if (!function_exists('wc_create_order')) {
			return;
		}

		// Get the customer's user ID or create a new customer
		$user_id = email_exists($transaction_data['payer_email']);
		if (!$user_id) {
			$user_id = wp_create_user(
				$transaction_data['payer_email'],
				wp_generate_password(),
				$transaction_data['payer_email']
			);
		}


		// Get the cart details from the transaction_data
		$cart_details = json_decode($transaction_data['custom'], true);


		// Create a new order
		$order = wc_create_order(array('customer_id' => $user_id));

		// Add products to the order based on the cart details
		foreach ($cart_details as $item) {
			$product_id = $item['product_id'];
			$quantity = $item['quantity'];
			$order->add_product(wc_get_product($product_id), $quantity);
		}

		// Set the billing and shipping addresses
		$address = array(
			'first_name' => $transaction_data['first_name'],
			'last_name' => $transaction_data['last_name'],
			'email' => $transaction_data['payer_email'],
			// ... other address fields ...
		);
		$order->set_address($address, 'billing');
		$order->set_address($address, 'shipping');

		// Set the payment method
		$order->set_payment_method('paypal');

		// Set the transaction ID
		$order->set_transaction_id($transaction_data['txn_id']);

		// Set the order as paid
		$order->payment_complete();

		// Save the order
		$order->save();
		// Return a response
		return new WP_REST_Response(array('success' => true, 'data' => 'Order created'), 201);
	}
}

if (class_exists('MapBooking')) {
	$mapBooking = new MapBooking();
	$mapBooking->register();
}

// Activation
require_once plugin_dir_path(__FILE__) . 'inc/booking-plugin-activate.php';
register_activation_hook(__FILE__, array('BookingPluginActivate', 'activate'));

// Deactivation
require_once plugin_dir_path(__FILE__) . 'inc/booking-plugin-deactivate.php';
register_deactivation_hook(__FILE__, array('BookingPluginDeactivate', 'deactivate'));

require_once plugin_dir_path(__FILE__) . 'inc/modules/package.php';
require_once plugin_dir_path(__FILE__) . 'inc/modules/vol.php';


