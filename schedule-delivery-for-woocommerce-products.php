<?php
/**
 * Plugin Name:     Schedule Product Delivery Date for WooCommerce
 * Description:     Schedule product delivery date enables weekly or monthly deliveries with selected dates and quantities. Also, customize your calendar that suits your product page.
 * Version:         1.1.6
 * Author:         	ThemeHigh
 * Author URI:		https://themehigh.com/ 
 *
 * Text Domain:     schedule-delivery-for-woocommerce-products
 * Domain Path:     /languages
 *
 * WC requires at least: 5.0
 * WC tested up to: 9.2
 */

if(!defined('WPINC')) {	
	die; 
}

if (!function_exists('is_woocommerce_active')){
	function is_woocommerce_active(){
	    $active_plugins = (array) get_option('active_plugins', array());
	    if(is_multisite()){
		   $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
	    }
	    if(in_array('woocommerce/woocommerce.php', $active_plugins) || array_key_exists('woocommerce/woocommerce.php', $active_plugins) || class_exists('WooCommerce')) {
	        return true;
	    } else {
	        return false;
	    }
	}
}

if(is_woocommerce_active()) {
	define('THSDF_VERSION', '1.1.6');
	!defined('THSDF_SOFTWARE_TITLE') && define('THSDF_SOFTWARE_TITLE', 'Schedule Product Delivery Date for WooCommerce');
	!defined('THSDF_FILE') && define('THSDF_FILE', __FILE__);
	!defined('THSDF_PATH') && define('THSDF_PATH', plugin_dir_path(__FILE__));
	!defined('THSDF_URL') && define('THSDF_URL', plugins_url( '/', __FILE__));
	!defined('THSDF_BASE_NAME') && define('THSDF_BASE_NAME', plugin_basename( __FILE__));

	add_action( 'before_woocommerce_init', 'thsdf_before_woocommerce_init' ) ;

	function thsdf_before_woocommerce_init() {
	    if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
	        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	    }
	}

	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and public-facing site hooks.
	 */
	require plugin_dir_path(__FILE__) . 'includes/class-thsdf.php';

	/**
	 * Begins execution of the plugin.
	 */
	function run_thsdf() {
		$plugin = new THSDF();
	}
	run_thsdf();
}


