<?php
/**
 * The Product page modification functions.
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

if(!class_exists('THSDF_Product_Settings')) :

	/**
     * Admin product settings class. 
     */ 
	class THSDF_Product_Settings{
		protected static $_instance = null;
		private $settings_fields = NULL;

		/**
         * Constructor.
         */
		public function __construct() {
			$this->add_hook();
		}

		/**
         * Function for instance.
         *
         * @return string
         */
		public static function instance() {
			if(is_null(self::$_instance)) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Hooks adding function.
		 *
		 */
		public function add_hook() {
			$debug_mode = apply_filters('thsdf_debug_mode', false);
			$suffix = $debug_mode ? '' : '.min';

			!defined('THSDF_ASSETS_URL_ADMIN') && define('THSDF_ASSETS_URL_ADMIN', THSDF_URL . 'admin/assets/');
			if(is_admin()){
				$this->enqueue_scripts($suffix);
				$this->enqueue_styles($suffix);
			}

			add_action('wp_ajax_save_calender_settings', array($this, 'save_calender_settings'), 10);
			
			add_filter('product_type_options', array($this, 'plan_delivery_checkbox_field'),100,1);
			add_filter('woocommerce_product_data_tabs', [ $this, 'plan_delivery_settings_tabs' ]);

			// Restore have problem.
			//add_action('save_post_product', array($this ,'save_plan_delivery_checkbox_option'), 10, 3);
			add_action('woocommerce_process_product_meta', array($this ,'save_delivery_global_checkbox_option'), 10, 2);
			add_action('woocommerce_product_data_panels', array($this ,'delivery_settings_product_data_fields'));

			add_action('wp_ajax_save_caldr_display_setting', array($this, 'save_product_display_settings'), 10);

    		add_action('wp_ajax_default_general_settings', array($this, 'default_general_settings'), 10);

    		add_action('wp_ajax_default_display_settings', array($this, 'default_display_settings'), 10);

		}
		/**
		 * Style enqueue function.
		 *
		 * @param string $suffix The suffix of the style sheet file
		 */
		private function enqueue_styles($suffix) {
			wp_enqueue_style('thsdf-admin-style', THSDF_ASSETS_URL_ADMIN . 'css/thsdf-admin'. $suffix .'.css', THSDF_VERSION);
		}

		/**
		 * Script enqueue function.
		 *
		 * @param string $suffix The suffix of the style sheet file
		 */
		private function enqueue_scripts($suffix) {
			// $deps = array('jquery', 'jquery-ui-dialog', 'jquery-ui-sortable', 'jquery-tiptip', 'woocommerce_admin', 'wc-enhanced-select', 'select2', 'wp-color-picker', 'jquery-ui-datepicker');

			$deps = array('jquery', 'jquery-ui-dialog', 'jquery-ui-sortable', 'jquery-tiptip','wc-enhanced-select', 'select2', 'wp-color-picker', 'jquery-ui-datepicker');

			wp_enqueue_script('thsdf-admin-script', THSDF_ASSETS_URL_ADMIN . 'js/thsdf-admin'. $suffix .'.js', $deps, THSDF_VERSION, false);
			wp_localize_script( 'thsdf-admin-script', 'thsdf_ajax_script', [          
	            'general_settings_nonce' => wp_create_nonce('product-general-settings-form'),
	            'display_settings_nonce' => wp_create_nonce('product-display-settings-form'),
	            'order_status_completed_nonce' => wp_create_nonce('order-status-completed'),
	            'order_status_canceled_nonce' => wp_create_nonce('order-status-canceled'),
	            'order_date_specific_refund_nonce' => wp_create_nonce('order-date-specific-refund'),
	            'delete_from_cancelled_table_nonce' => wp_create_nonce('delete-from-cancelled-table'),

	            'mon_decimal_point'                 => wc_get_price_decimal_separator(),
	            'rounding_precision'            	=> wc_get_rounding_precision(),

        	]);

		}

		/**
		 * Display the Plan Delivery tab.
		 *
		 * @param array $product_data_tabs The existing product data tabs
		 *
	 	 * @return array 
		 */
		public function plan_delivery_settings_tabs($product_data_tabs){
 
			$product_data_tabs['plan_delivery'] = array(
				'label'    => esc_html__('Plan Delivery', 'schedule-delivery-for-woocommerce-products'),
				'target'   => 'plan_delivery_product_data',
				'class'    => array('show_if_simple','show_if_variable'),
				'priority' => 70,
			);
			return $product_data_tabs;
		}

		/**
		 * Display the Plan Delivery checkbox field.
		 *
		 * @param array $options The existing checkbox options
		 *
	 	 * @return array 
		 */
		public function plan_delivery_checkbox_field($options) {
			$option_id = 'plan_delivery_set';
			$options[ $option_id ] = array(
			'id'            => '_plan_delivery_set',
			'wrapper_class' => 'show_if_simple show_if_variable',
			'label'         => esc_html__('Plan Delivery', 'schedule-delivery-for-woocommerce-products'),
			'description'   => esc_html__('Enable this option is activate plan delivery settings.', 'ptdelivery'),
			'default'       => 'no'
			);
			return $options;
		}
				/**
		 * Display the plan delivery settings fields.
		 * 
		 */
		function delivery_settings_product_data_fields() {
			$post_id = get_the_ID(); ?>
			<div id = 'plan_delivery_product_data' class = 'panel woocommerce_options_panel' >
	 			<div class = 'options_group' >
	 				<?php $global_setting = get_post_meta($post_id, THSDF_Utils::POST_KEY_PLAN_DELIVERY_GENERAL, true);
	 				if($global_setting == '') {
	 					$value = 'yes';
	 				} else {
	 					$value = $global_setting;
	 				}
				 	woocommerce_wp_checkbox(
						array(
							'id' 			=> '_global_settings',
							'value'  		=> esc_attr($value),
							'label' 		=> esc_html__('Use Global Settings', 'schedule-delivery-for-woocommerce-products'),
							'class' 		=> 'ptdelivery-global-settings',
							'desc_tip' 		=> false,
							'description' 	=> '',
						)
				 	); ?>
					<div class="thptdelivery-accordion">
						<ul class="thptdelivery-accordion-ul">
						  	<li>
							    <a class="thptdelivery-toggle" href=#> <?php esc_html_e('Calendar Settings', 'schedule-delivery-for-woocommerce-products'); ?><span class="dashicons dashicons-controls-play"></span></a>
							    <div class="thptdelivery-inner">
							    	<!-- <form action="" method="post" class="thpt-general-settings-form">
									</form> -->
							      <?php 
							      $display_settings = new THSDF_Product_General_Settings();
							     	$display_settings->render_page();
							     	 ?>
							    </div>
						  	</li>
						  
						  	<li>
							    <a class="thptdelivery-toggle" href=#><?php esc_html_e('Display Settings', 'schedule-delivery-for-woocommerce-products'); ?> <span class="dashicons dashicons-controls-play"></span></a>
							    <div class="thptdelivery-inner">
							    	<?php 
							    	$display_settings = new THSDF_Product_Display_Settings();
							     	$display_settings->render_page(); 
							     	?>
							    </div>
						  	</li>
						</ul>
					</div><!-- end accordion-container -->
				</div>
			</div>
  <?php }
  		/**
		 * Plan delivery global checkbox saving option.
		 *
		 * @param integer $post_id The post id info
		 * @param array $product The eproduct details
		 *
		 */
		public function save_delivery_global_checkbox_option($post_id, $product){
			$capability = THSDF_Utils::thsdf_capability();
			if(!current_user_can($capability)){
				wp_die();
			}
			// $post_id = get_the_ID();

			$plan_delivery_global = isset($_POST['_global_settings']) ? 'yes' : 'no';
			if (!empty($plan_delivery_global)) {
				update_post_meta($post_id, THSDF_Utils::POST_KEY_PLAN_DELIVERY_GENERAL, $plan_delivery_global);
			}
			$plan_delivery_set = isset($_POST["_plan_delivery_set"]) ? "yes" : "no";

			update_post_meta(
		          $product->ID
		        , THSDF_Utils::POST_KEY_PLAN_DELIVERY_CHECKBOX
		        , $plan_delivery_set
		    );

			$this->update_calendar_settings();
			$this->update_calendar_display_settings();
			if($plan_delivery_set == 'yes'){
				$calender_settings = array();
				$display_settings = array();
				$product_calender_settings = array();
				$product_display_settings = array();
				$product_data = array();
				$single_general_settings = array();
				$single_display_settings = array();

				$calender_settings = get_option(THSDF_Utils::OPTION_KEY_DELIVERY_SETTINGS);
				$display_settings = get_option(THSDF_Utils::OPTION_KEY_DISPLAY_SETTINGS);
				$product_calender_settings = get_post_meta($post_id, THSDF_Utils::POST_KEY_SINGLE_SETTINGS, true);
				$product_display_settings = get_post_meta($post_id, THSDF_Utils::POST_KEY_SINGLE_DISPLAY_SETTINGS, true);
				if(!empty($calender_settings)){
					$product_data = array(
							'product_id' 		=> $post_id,);
					$single_general_settings = array_merge($product_data,$calender_settings);
				}
				if(!empty($display_settings)){
					$product_data = array(
							'product_id' 		=> $post_id,);
					$single_display_settings = array_merge($product_data,$display_settings);
				}
				if(empty($product_calender_settings)){
					if(!empty($single_general_settings)){
						$result = update_post_meta($post_id, THSDF_Utils::POST_KEY_SINGLE_SETTINGS, $single_general_settings);
					}
				}
				if(empty($product_display_settings)){
					if(!empty($single_display_settings)){
						$result = update_post_meta($post_id, THSDF_Utils::POST_KEY_SINGLE_DISPLAY_SETTINGS, $single_display_settings);
					}
				}
			}
		} 

  		/**
		 * Function for save individual product calender settings.
		 */
		function save_calender_settings() {
			if (check_ajax_referer('product-general-settings-form','nonce')) {
				$capability = THSDF_Utils::thsdf_capability();
				if(!current_user_can($capability)){
					wp_die();
				}
				$product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : '';
				$calender_settings_info = $this->calendar_informations();
				$settings = $calender_settings_info;

				// Update Post meta data of general settings.
				$result = update_post_meta($product_id, THSDF_Utils::POST_KEY_SINGLE_SETTINGS, $settings);				
				$this->data_saving_notice($result);
				exit();
			}
		}

		/**
		 * Function for save individual product calender display settings.
		 */
		function save_product_display_settings(){
			if (check_ajax_referer('product-display-settings-form','nonce')) {
				$capability = THSDF_Utils::thsdf_capability();
				if(!current_user_can($capability)){
					wp_die();
				}
				$product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : '';
				$calender_settings_info = $this->calendar_informations();
				$settings = $calender_settings_info;

				// Update Post meta data of display settings.
				$result = update_post_meta($product_id, THSDF_Utils::POST_KEY_SINGLE_DISPLAY_SETTINGS, $settings);
				$this->data_saving_notice($result);
				exit();

			}
		}

		/**
		 * Function for set default general settings of individual product.
		 */
		function default_general_settings(){
			if (check_ajax_referer('product-general-settings-form','nonce')) {
				$capability = THSDF_Utils::thsdf_capability();
				if(!current_user_can($capability)){
					wp_die();
				}
				$product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : '';
				$calender_settings_info = $this->calendar_informations();
				$settings = $calender_settings_info;

				$result = update_post_meta($product_id, THSDF_Utils::POST_KEY_SINGLE_SETTINGS, $settings);
				THSDF_Admin_Settings::print_notices('Settings successfully reset.','updated',false);
				exit();
			}
		}

		/**
		 * Function for set default display settings of individual product.
		 */
		function default_display_settings(){
			if (check_ajax_referer('product-display-settings-form','nonce')) {
				$capability = THSDF_Utils::thsdf_capability();
				if(!current_user_can($capability)){
					wp_die();
				}
				$product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : '';
				$calender_settings_info = $this->calendar_informations();
				$settings = $calender_settings_info;

				// Update Post meta data of display settings
				$result = update_post_meta($product_id, THSDF_Utils::POST_KEY_SINGLE_DISPLAY_SETTINGS, $settings);
				THSDF_Admin_Settings::print_notices('Settings successfully reset.','updated',false);
				exit();
			}			
		}

		/**
		 * get calendar informations.
		 *
		 * @return array
		 */
		function calendar_informations() {
			$calender_settings = isset($_POST['settings_data']) ? wc_clean($_POST['settings_data']) : array();
			return $calender_settings;
		}		

		function data_saving_notice($result) {
			if($result == true) {
				THSDF_Admin_Settings::print_notices('Your changes were saved','updated',false);
			} else {
				THSDF_Admin_Settings::print_notices('Your changes were not saved due to an error (or you made none!).','error',false);
			}
		}

		public function get_fields() {
			$week_start = THSDF_Admin_Settings::get_week_start();
			$delivery_day = THSDF_Admin_Settings::get_delivery_day();
			$plan_display = THSDF_Admin_Settings::get_plan_display();
			return array(
				'product_id'        	    => array('type'=>'hidden', 'name'=>'product_id', 'label'=>'Product Id'),
				'week_start'				=> array('type'=>'select', 'name'=>'week_start', 'label'=>esc_html__('Start the Week on', 'schedule-delivery-for-woocommerce-products'), 'options'=>$week_start, 'class' => esc_attr('week_start')),
				'delivery_day'  			=> array('type'=>'multicheckbox', 'name'=>'delivery_day[]', 'label'=>esc_html__('Enable Plan Delivery for Weekdays', 'schedule-delivery-for-woocommerce-products'), 'check'=>$delivery_day, 'required' =>1, 'id' => 'delivery_day', 'required_tag' => esc_html__('Weekdays are required', 'schedule-delivery-for-woocommerce-products'), 'required_class' => esc_attr('thsdf-weekday-required-mssg'),'label_class' => esc_attr('thpt-delivery-day-label'), 'class'=>'delivery_day'),
				'start_date'   				=> array('type'=>'text', 'name'=>'start_date', 'label'=>esc_html__('Start Date', 'schedule-delivery-for-woocommerce-products'), 'required'=>1,'id'=>'ptdt_datepicker_start','placeholder' => 'yyyy-mm-dd', 'class' => esc_attr('general-set-date general-start-date'), 'required_class' => ('thsdf-startdate-required-mssg thsdf-single-required-mssg'), 'required_tag' => esc_html__('Start Date field is required', 'schedule-delivery-for-woocommerce-products')),
				
				'end_date' 					=> array('type'=>'text', 'name'=>'end_date', 'label'=>esc_html__('End Date', 'schedule-delivery-for-woocommerce-products'), 'required'=>1,'id'=>'ptdt_datepicker_end','placeholder' => 'yyyy-mm-dd', 'class' => esc_attr('general-set-date'),'required_class' => ('thsdf-required-mssg thsdf-single-required-mssg'), 'required_tag' => esc_html__('End Date field is required', 'schedule-delivery-for-woocommerce-products')),
				'holidays'   				=> array('type'=>'multidatepicker', 'name'=>'holidays[]', 'label'=>esc_html__('Holiday / Unavailable days', 'schedule-delivery-for-woocommerce-products'), 'placeholder' => 'yyyy-mm-dd', 'class' => esc_attr('general-set-date_holiday g-holidays')),
				
				'plan_display'  			=> array('type'=>'multicheckbox', 'name'=>'plan_display[]', 'check'=>$plan_display, 'checked'=>0, 'status'=>1,'label_class' =>esc_attr('plan-display-label plan-display-pro'), 'class' => 'g-plan-display'),
				'delivery_plan_display' => array('type'=>'separator', 'value'=>esc_html__('Delivery Plan Display', 'schedule-delivery-for-woocommerce-products'), 'class' => esc_attr('thpt-general-settings-header')),
				
			);
		}

		/**
         * update calendar settings.
         */
        public function update_calendar_settings() {
        	$product_id = get_the_ID();
        	$settings_fields = $this->get_fields();
			// if (check_ajax_referer('product-general-settings-form','nonce')) {
				$capability = THSDF_Utils::thsdf_capability();
				if(!current_user_can($capability)){
					wp_die();
				}
				$general_settings = array();
				if(!empty($settings_fields) && is_array($settings_fields)) {
					foreach($settings_fields as $name => $field) {
						$value = '';
						if($field['type'] === 'multiselect_grouped') {
							if(!empty($_POST['g_'.$name])) {
								$value = is_array($_POST['g_'.$name]) ? array_map('sanitize_text_field',(implode(',', $_POST['g_'.$name]))) : sanitize_text_field($_POST['g_'.$name]);
							}							
						} else {
							if(!empty($_POST['g_'.$name])) {
								$value = is_array($_POST['g_'.$name]) ? wc_clean($_POST['g_'.$name]) : sanitize_text_field($_POST['g_'.$name]);
							}
						}
						if(!empty($value)) {
							$value = preg_replace('/\s/', '', $value);						
						}
						$settings[$name] = $value;
					}
				}
				
				// Update option data of single product - calendar settings.
				$result = false;
				if(!empty($settings)) {
					if(($settings['start_date'] != '') && !empty($settings['delivery_day']) && ($settings['end_date'] != '')) {
						$result = update_post_meta($product_id, THSDF_Utils::POST_KEY_SINGLE_SETTINGS, $settings);
					}
				}
				self::data_saving_notice($result);	
			// }
		}

		/**
		 * update calendar display settings.
		 **/
		public function update_calendar_display_settings() {
			// if(isset($_POST['display-settings-form']) && $_POST['display-settings-form']){
			// 	$nonce = $_POST['display-settings-form'];
			// 	if(!wp_verify_nonce($nonce, 'display_settings_form')){
			// 		die('You are not authorized to perform this action.');
			// 	} else {

			$capability = THSDF_Utils::thsdf_capability();
			if(!current_user_can($capability)){
				wp_die();
			}
			$product_id = get_the_ID();
        	$settings_fields = THSDF_Admin_Settings_Display::get_field_form_props();

					$display_settings = array();
					if(!empty($settings_fields) && is_array($settings_fields)) {
						foreach($settings_fields as $name => $field) {
							$value = '';
							if($field['type'] === 'checkbox') {
								$value = (!empty($_POST['g_'.$name]))  ? sanitize_text_field($_POST['g_'.$name]) : '';
							}else if($field['type'] === 'multiselect_grouped') {
								$value = (!empty($_POST['g_'.$name])) ? sanitize_text_field($_POST['g_'.$name]) : '';
								$value = is_array($value) ? implode(',', $value) : $value;
							}else if($field['type'] === 'text') {
								$value = (!empty($_POST['g_'.$name])) ? sanitize_text_field($_POST['g_'.$name]) : '';
								$value = !empty($value) ? stripslashes(trim($value)) : '';
							} else if($field['type'] === 'textarea') {
								$value = (!empty($_POST['g_'.$name])) ? sanitize_textarea_field($_POST['g_'.$name]) : '';
								$value = !empty($value) ? stripslashes(trim($value)) : '';
							}else {
								if(!empty($_POST['g_'.$name])) {
									$value = is_array($_POST['g_'.$name]) ? array_map('sanitize_text_field',$_POST['g_'.$name]) : sanitize_text_field($_POST['g_'.$name]);
								}
							}
							if(!empty($value)) {
								$value = preg_replace('/\s/', '', $value);
							}
							$settings[$name] = $value; 
						}
					}

					// Update option data of general settings.
					$result = false;
					if(!empty($settings)) {
						$result = update_post_meta($product_id, THSDF_Utils::POST_KEY_SINGLE_DISPLAY_SETTINGS, $settings);
					}
					self::data_saving_notice($result);	
			// 	}
			// }
		}		
	}
endif;