<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    schedule-delivery-for-woocommerce-products
 * @subpackage schedule-delivery-for-woocommerce-products/admin
 */
if(!defined('WPINC')) { 
	die; 
}

if(!class_exists('THSDF_Admin')) :

	/**
     * Admin class.
    */
	class THSDF_Admin {
		private $plugin_name;
		private $version;
		private $screen_id;

		/**
		 * Initialize the class and set its properties.
		 *
		 * @param      string    $version    The version of this plugin.
		 * @param      string    $plugin_name       The name of this plugin.
		 */
		public function __construct($version, $plugin_name) {
			$this->plugin_name = $plugin_name;
			$this->version = $version;
		}

		/**
		 * Function for enqueue script and style.
		 *
		 * @param      string    $hook       The screen id of the plugin.
		 *
		 * @return void
		 */
		public function enqueue_styles_and_scripts($hook) {
			if(strpos($hook, 'product_page_th_schedule_delivery_free') === false) {
				return;
			}

			$debug_mode = apply_filters('thsdf_debug_mode', false);
			$suffix = $debug_mode ? '' : '.min';

			$this->enqueue_styles($suffix);
			$this->enqueue_scripts($suffix);
		}

		/**
		 * Function for enqueue style.
		 *
		 * @param      string    $suffix       The stylesheet suffix.
		 */
		private function enqueue_styles($suffix) {
			wp_enqueue_style (array('woocommerce_admin_styles', 'jquery-ui-style'));
			wp_enqueue_style('jquery-ui-style', THSDF_WOO_ASSETS_URL.'/css/jquery-ui/jquery-ui.css');
			wp_enqueue_style('woocommerce_admin_styles', THSDF_WOO_ASSETS_URL.'css/admin.css');
			wp_enqueue_style('thsdf-admin-style', THSDF_ASSETS_URL_ADMIN . 'css/thsdf-admin'. $suffix .'.css', THSDF_VERSION);
		}

		/**
		 * Function for enqueue script.
		 *
		 * @param      string    $suffix       The js file suffix.
		 */
		private function enqueue_scripts($suffix) {
			// $deps = array('jquery', 'jquery-ui-dialog', 'jquery-ui-sortable', 'jquery-tiptip', 'woocommerce_admin', 'wc-enhanced-select', 'select2', 'wp-color-picker', 'jquery-ui-datepicker');

			$deps = array('jquery', 'jquery-ui-dialog', 'jquery-ui-sortable', 'jquery-tiptip','wc-enhanced-select', 'select2', 'wp-color-picker', 'jquery-ui-datepicker'); 
			
			wp_enqueue_script('thsdf-admin-script', THSDF_ASSETS_URL_ADMIN . 'js/thsdf-admin'. $suffix .'.js', $deps, THSDF_VERSION, false);
	
			
		}

		/**
		 * Function for creating admin menu.
		 */
		public function admin_menu() {
		$capability = THSDF_Utils::thsdf_capability();
		$this->screen_id = add_submenu_page('edit.php?post_type=product', esc_html__('Plan Delivery', 'schedule-delivery-for-woocommerce-products'),
		esc_html__('Plan Delivery', 'plan-delivery'), $capability, 'th_schedule_delivery_free', array($this, 'output_settings'));
		}

		/**
		 * Function for set screen id.
		 *
		 * @param      string    $ids       The screen id.
		 *
		 * @return array
		 */
		public function add_screen_id($ids) {
			$ids[] = 'woocommerce_page_th_schedule_delivery_free';
			$ids[] = strtolower(esc_html__('WooCommerce', 'schedule-delivery-for-woocommerce-products')) .'_page_th_schedule_delivery_free';
			return $ids;
		}

		/**
		 * Function for set plugin action links.
		 *
		 * @param      string    $links    The action link.
		 *
		 * @return array
		 */
		public function plugin_action_links($links) {
			$settings_link = '<a href="'.esc_url(admin_url('edit.php?post_type=product&page=th_schedule_delivery_free')).'">'. esc_html__('Settings', 'schedule-delivery-for-woocommerce-products') .'</a>';
			array_unshift($links, $settings_link);

			if (array_key_exists('deactivate', $links)) {
            	$links['deactivate'] = str_replace('<a', '<a class="thsdf-deactivate-link"', $links['deactivate']);
        	}

			return $links;
		}

		 /**
         * Function for premium version notice.
         *
         *
         * @return void
         */
        private function _output_premium_version_notice() { ?>
            <div id="message" class="wc-connect updated thpladmin-notice thsdf-admin-notice">
                <div class="squeezer">
                    <table>
                        <tr>
                            <td width="70%">
                                <p>
                                    <strong><i><a href="<?php echo esc_url('https://www.themehigh.com/product/schedule-delivery-for-woocommerce/'); ?>">
                                        <?php echo esc_html__('Schedule Product Delivery Date for WooCommerce', 'schedule-delivery-for-woocommerce-products');?>

                                    </a></i></strong><?php echo esc_html__('Upgrade to the Pro Version to have access to additional features such as the Time Range Picker, which allows you to more easily plan product delivery.', 'schedule-delivery-for-woocommerce-products'); ?>
                                    <ul>
                                    <li>
                                    <?php echo esc_html__('Minimum date/ days for delivering the products can be marked.', 'schedule-delivery-for-woocommerce-products'); ?>
                                    </li>
                                    <li>
                                    <?php echo esc_html__('Can set a maximum quantity of items per date.', 'schedule-delivery-for-woocommerce-products'); ?>
                                    </li>
                                    <li>
                                    <?php echo esc_html__('The Time Range Picker option allows you to choose available delivery timing.', 'schedule-delivery-for-woocommerce-products'); ?>
                                    </li>
                                    <li>
                                    <?php echo esc_html__('Can choose the time range picker settings as mandatory or optional.', 'schedule-delivery-for-woocommerce-products'); ?>
                                    </li>
                                    <li>
                                    <?php echo esc_html__('Can choose 12 hours or 24 hours time format.', 'schedule-delivery-for-woocommerce-products'); ?>
                                    </li>
                                    <li>
                                    <?php echo esc_html__('Provide individual time pickers for each day.', 'schedule-delivery-for-woocommerce-products'); ?>
                                    </li>
                                    <li>
                                    <?php echo esc_html__('Customers can cancel their order from the Orders page in My Account.', 'schedule-delivery-for-woocommerce-products'); ?>
                                    </li>
                                    <li>
                                    <?php echo esc_html__("From the Order page, the Admin can handle the customer's request.", "schedule-delivery-for-woocommerce-products"); ?>
                                    </li>
                                    <li>
                                    <?php echo esc_html__('The calendar can be customized to match the theme of your store.', "schedule-delivery-for-woocommerce-products"); ?>
                                    </li>
                                    <li>
                                        <?php echo esc_html__('Compatible with', 'schedule-delivery-for-woocommerce-products'); ?> 
                                            <strong><i><a href="<?php echo esc_url('https://www.themehigh.com/product/woocommerce-extra-product-options/'); ?>">
                                                <?php echo esc_html__('Extra Product Options for WooCommerce', 'schedule-delivery-for-woocommerce-products'); ?>
                                            </a></i></li>
                                    </ul>
                            </p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        <?php }

		/**
		 * Function for output settings.
		 */
		public function output_settings() {
			$this->_output_premium_version_notice();
			$tab  = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'general_settings';
			if($tab === 'display_settings') {
				$display_settings = THSDF_Admin_Settings_Display::instance();
				$display_settings->render_page();
			}else{
				$general_settings = THSDF_Admin_Settings_General::instance();
				$general_settings->render_page();
			}
		}

		/**
		 * Function for calling the product page delivery setting class.
		 */
		public function product_page_delivery_settings() {
			$ptdelivery_product_settings = new THSDF_Product_Settings();
		}

		/**
		 * Function for calling the order page delivery setting class.
		 */
		public function order_page_delivery_settings() {
			$order_page = new THSDF_Admin_Order_Page_Settings();
		}		
	}
endif;