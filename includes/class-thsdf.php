<?php
/**
 * The file that defines the core plugin class.
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    schedule-delivery-for-woocommerce-products
 * @subpackage schedule-delivery-for-woocommerce-products/includes
 */

if(!defined('WPINC')) {	
	die; 
}

if(!class_exists('THSDF')) :

	/**
     * The plugin main class.
     */
	class THSDF {
		const TEXT_DOMAIN = 'schedule-delivery-for-woocommerce-products';
		/**
		 * The loader that's responsible for maintaining and registering all hooks that power
		 * the plugin.
		 *
		 * @access   protected
		 * @var      $loader    Maintains and registers all hooks for the plugin.
		 */
		protected $loader;

		/**
		 * The unique identifier of this plugin.
		 *
		 * @access   protected
		 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
		 */
		protected $plugin_name;

		/**
		 * The current version of the plugin.
		 *
		 * @access   protected
		 * @var      string    $version    The current version of the plugin.
		 */
		protected $version;

		/**
		 * Define the core functionality of the plugin.
		 *
		 * Set the plugin name and the plugin version that can be used throughout the plugin.
		 * Load the dependencies, define the locale, and set the hooks for the admin area and
		 * the public-facing side of the site.
		 */
		public function __construct() {
			if (defined('THSDF_VERSION')) {
				$this->version = THSDF_VERSION;
			} else {
				$this->version = '1.0.0';
			}
			$this->plugin_name = 'schedule-delivery-for-woocommerce-products';

			$this->load_dependencies();
			$this->set_locale();
			$this->define_admin_hooks();
			$this->define_public_hooks();
			add_action('init', array($this, 'init'));
			// $this->loader->add_filter('thsdf_custom_section_positions', 'THSDF_Utils', 'custom_step_hooks');

		}

		/**
		 * The init function. 
		 */
		public function init(){
			$this->define_constants();
		}

		/**
		 * The constants define function. 
		 */
		private function define_constants() {
			//define("JQUERY_UI_WP_URL", plugin_dir_url(__FILE__));
			!defined('THSDF_ASSETS_URL_ADMIN') && define('THSDF_ASSETS_URL_ADMIN', THSDF_URL . 'admin/assets/');
			!defined('THSDF_ASSETS_URL_PUBLIC') && define('THSDF_ASSETS_URL_PUBLIC', THSDF_URL . 'public/assets/');
			!defined('THSDF_WOO_ASSETS_URL') && define('THSDF_WOO_ASSETS_URL', WC()->plugin_url() . '/assets/');
		}

		/**
		 * Load the required dependencies for this plugin.
		 *
		 * Include the following files that make up the plugin:
		 *
		 * - THSDF_Loader. Orchestrates the hooks of the plugin.
		 * - THSDF_Admin. Defines all hooks for the admin area.
		 * - THSDF_Public. Defines all hooks for the public side of the site.
		 *
		 * Create an instance of the loader which will be used to register the hooks
		 * with WordPress.
		 *
		 * @access   private
		 */
		private function load_dependencies() {
			if(!function_exists('is_plugin_active')) {
				include_once(ABSPATH . 'wp-admin/includes/plugin.php');
			}
			require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-thsdf-autoloader.php';

			/**
			 * The class responsible for orchestrating the actions and filters of the
			 * core plugin.
			 */
			require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-thsdf-loader.php';

			/**
			 * The class responsible for defining all actions that occur in the admin area.
			 */
			require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-thsdf-admin.php';

			/**
			 * The class responsible for defining all actions that occur in the public-facing
			 * side of the site.
			 */
			require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-thsdf-public.php';

			require_once plugin_dir_path(dirname(__FILE__)) . 'includes/utils/class-thsdf-utils.php';
			require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-thsdf-admin-settings.php';
			require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-thsdf-admin-settings-general.php';
			require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-thsdf-admin-settings-display.php';
			require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-thsdf-admin-product-page-settings.php';
			require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-thsdf-admin-product-display-setting.php';
			require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-thsdf-admin-product-general-setting.php';

			$this->loader = new THSDF_Loader();
		}

		/**
		 * Define the locale for this plugin for internationalization.
		 *
		 * @access   private
		 */
		private function set_locale() {
			add_action('plugins_loaded', array($this, 'load_plugin_textdomain'));
		}

		/**
		 * Load the plugin text domain for translation.
		 */
		public function load_plugin_textdomain() {
			$locale = apply_filters('plugin_locale', get_locale(), self::TEXT_DOMAIN);
			load_textdomain(self::TEXT_DOMAIN, WP_LANG_DIR.'/'.self::TEXT_DOMAIN.'/'.self::TEXT_DOMAIN.'-'.$locale.'.mo');
			load_plugin_textdomain(self::TEXT_DOMAIN, false, dirname(dirname(THSDF_BASE_NAME)) . '/languages/');
		}


		/**
		 * Register all of the hooks related to the public-facing functionality
		 * of the plugin.
		 *
		 * @access   private
		 */
		private function define_admin_hooks() {
			$plugin_admin = new THSDF_Admin($this->version, $this->plugin_name);

			add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_styles_and_scripts'));
			add_action('admin_menu', array($plugin_admin, 'admin_menu'));
			add_filter('woocommerce_screen_ids', array($plugin_admin, 'add_screen_id'));
			add_filter('plugin_action_links_'.THSDF_BASE_NAME, array($plugin_admin, 'plugin_action_links'));

			// Product page settings
			add_action('init', array($plugin_admin, 'product_page_delivery_settings'));

			// Order page settings
			add_action('init', array($plugin_admin, 'order_page_delivery_settings'));

			add_action('admin_footer', array($this, 'quick_links'), 10);

			add_action('admin_footer-plugins.php', array($this, 'thsdf_deactivation_form'));
			add_action('wp_ajax_thsdf_deactivation_reason', array($this, 'thsdf_deactivation_reason'));
		}

		/**
		 * Register all of the hooks related to the public-facing functionality
		 * of the plugin.
		 *
		 * @access   private
		 */
		private function define_public_hooks() {
			$plugin_public = new THSDF_Public($this->version, $this->plugin_name);
			add_action('wp_enqueue_scripts', array($plugin_public, 'enqueue_styles_and_scripts'));
		}

		/**
		 * Plugin action links.
		 *
		 * @param $links the links
		 *
		 * @return string 
		 */
		public function plugin_action_links($links) {
            $premium_link = '<a href="'.esc_url('https://www.themehigh.com/product/schedule-delivery-for-woocommerce/').'">'. __('Premium plugin', 'schedule-delivery-for-woocommerce-products') .'</a>';
            $settings_link = '<a href="'.esc_url(admin_url('admin.php?&page=th_multiple_addresses_free')).'">'. __('Settings', 'schedule-delivery-for-woocommerce-products') .'</a>';

            array_unshift($links, $premium_link);
            array_unshift($links, $settings_link);

            if (array_key_exists('deactivate', $links)) {
            	$links['deactivate'] = str_replace('<a', '<a class="thsdf-deactivate-link"', $links['deactivate']);
        	}

            return $links;
        }

		/**
		 * Run the loader to execute all of the hooks with WordPress.
		 */
		public function run() {
			$this->loader->run();
		}

		/**
		 * The name of the plugin used to uniquely identify it within the context of
		 * WordPress and to define internationalization functionality.
		 *
		 * @return    string    The name of the plugin.
		 */
		public function get_plugin_name() {
			return $this->plugin_name;
		}

		/**
		 * The reference to the class that orchestrates the hooks with the plugin.
		 *
		 * @return    Loader Object    Orchestrates the hooks of the plugin.
		 */
		public function get_loader() {
			return $this->loader;
		}

		/**
		 * Retrieve the version number of the plugin.
		 *
		 * @return    string    The version number of the plugin.
		 */
		public function get_version() {
			return $this->version;
		}

		public function quick_links(){
	        $current_screen = get_current_screen();
	        if($current_screen->id !== 'product_page_th_schedule_delivery_free'){
	            return;
	        }
	        
	        ?>
	        <div class="th_quick_widget-float">
	            <div id="myDIV" class="th_quick_widget">
	                <div class="th_whead">
	                    <div class="th_whead_close_btn" onclick="thwsdfwidgetClose()">
	                        <img src="<?php echo THSDF_URL.'admin/assets/img/th-icon-cross.svg'; ?>" alt="" class="">
	                    </div>
	                    <!-- -----------------------------Widget head icon ----------------------------->
	                    <div class="th_whead_icon">
	                        <img src="<?php echo THSDF_URL.'admin/assets/img/th-icon-purple.svg'; ?>" alt="" class="">
	                    </div>
	                    <!--------------------------Whidget heading section ---------------------------->
	                    <div class="th_quick_widget_heading">
	                        <div class="th_whead_t1"><p>Welcome, we're</p><p><b style="font-size: 28px;">themehigh</b></p></div>
	                        </div>
	                    </div>
	                    <!-- --------------------Widget Body--------------------------------------- -->
	                    <div class="th_quick_widget_body">
	                        <ul>
	                            <li>
	                                <div class="list_icon" style="background-color: rgb(30 194 229 / 11%);">
	                                    <img src="<?php echo THSDF_URL.'admin/assets/img/upgrade-icon.svg'; ?>" alt="" class="">
	                                </div>
	                                <a href="https://www.themehigh.com/product/schedule-delivery-for-woocommerce/" target="_blank" class="quick-widget-doc-link">Upgrade to Premium</a>
	                            </li>
	                            <li>    
	                                <div class="list_icon" style="background-color: rgba(255, 245, 235, 1);">
	                                    <img src="<?php echo THSDF_URL.'admin/assets/img/th-icon-join.svg'; ?>" alt="" class="">
	                                </div><a href="https://www.facebook.com/groups/740534523911091" target="_blank" class="quick-widget-community-link">Join our Community</a>
	                            </li>
	                            <li>
	                                <div class="list_icon" style="background-color: rgba(238, 240, 255, 1);">
	                                    <img src="<?php echo THSDF_URL.'admin/assets/img/th-icon-speaker.svg'; ?>" alt="" class="">
	                                </div><a href="https://wordpress.org/support/plugin/schedule-product-delivery-date-for-woocommerce/" target="_blank" class="quick-widget-support-link">Get support</a>
	                            </li>
	                            <li>
	                                <div class="list_icon" style="background-color: rgba(255, 0, 0, 0.15);">
	                                    <img src="<?php echo THSDF_URL.'admin/assets/img/demo-icon.svg'; ?>" alt="" class="">
	                                </div><a href="https://flydemos.com/sd/" target="_blank" class="quick-widget-support-link">Try demo</a>
	                            </li>
	                        </ul>
	                    </div>
	                </div>
	            <div id="myWidget" class="widget-popup" onclick="thwsdfwidgetPopUp()">
	                <span id="th_quick_border_animation"></span>
	                <div class="widget-popup-icon" id="th_arrow_head">
	                    <img src="<?php echo THSDF_URL.'admin/assets/img/th-icon-white.svg'; ?>" alt="" class="">
	                </div>
	            </div>
	            </div>
	        <?php
	    }


		public function thsdf_deactivation_form(){
	        $is_snooze_time = get_user_meta( get_current_user_id(), 'thsdf_deactivation_snooze', true );
	        $now = time();

	        if($is_snooze_time && ($now < $is_snooze_time)){
	            return;
	        }

	        $deactivation_reasons = $this->get_deactivation_reasons();
	        ?>
	        <div id="thsdf_deactivation_form" class="thpladmin-modal-mask">
	            <div class="thpladmin-modal">
	                <div class="modal-container">
	                    <!-- <span class="modal-close" onclick="thwvsfCloseModal(this)">×</span> -->
	                    <div class="modal-content">
	                        <div class="modal-body">
	                            <div class="model-header">
	                                <img class="th-logo" src="<?php echo esc_url(THSDF_URL .'admin/assets/img/themehigh.svg'); ?>" alt="themehigh-logo">
	                                <span><?php echo __('Quick Feedback', 'schedule-delivery-for-woocommerce-products'); ?></span>
	                            </div>

	                            <!-- <div class="get-support-version-b">
	                                <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s,</p>
	                                <a class="thsdf-link thsdf-right-link thsdf-active" target="_blank" href="https://help.themehigh.com/hc/en-us/requests/new"><?php echo __('Get Support', 'schedule-delivery-for-woocommerce-products'); ?></a>
	                            </div> -->

	                            <main class="form-container main-full">
	                                <p class="thsdf-title-text"><?php echo __('If you have a moment, please let us know why you want to deactivate this plugin', 'schedule-delivery-for-woocommerce-products'); ?></p>
	                                <ul class="deactivation-reason" data-nonce="<?php echo wp_create_nonce('thsdf_deactivate_nonce'); ?>">
	                                    <?php 
	                                    if($deactivation_reasons){
	                                        foreach($deactivation_reasons as $key => $reason){
	                                            $reason_type = isset($reason['reason_type']) ? $reason['reason_type'] : '';
	                                            $reason_placeholder = isset($reason['reason_placeholder']) ? $reason['reason_placeholder'] : '';
	                                            ?>
	                                            <li data-type="<?php echo esc_attr($reason_type); ?>" data-placeholder="<?php echo esc_attr($reason_placeholder); ?> ">
	                                                <label>
	                                                    <input type="radio" name="selected-reason" value="<?php echo esc_attr($key); ?>">
	                                                    <span><?php echo esc_html($reason['radio_label']); ?></span>
	                                                </label>
	                                            </li>
	                                            <?php
	                                        }
	                                    }
	                                    ?>
	                                </ul>
	                                <p class="thsdf-privacy-cnt"><?php echo __('This form is only for getting your valuable feedback. We do not collect your personal data. To know more read our ', 'schedule-delivery-for-woocommerce-products'); ?> <a class="thsdf-privacy-link" target="_blank" href="<?php echo esc_url('https://www.themehigh.com/privacy-policy/');?>"><?php echo __('Privacy Policy', 'schedule-delivery-for-woocommerce-products'); ?></a></p>
	                            </main>
	                            <footer class="modal-footer">
	                                <div class="thsdf-left">
	                                    <a class="thsdf-link thsdf-left-link thsdf-deactivate" href="#"><?php echo __('Skip & Deactivate', 'schedule-delivery-for-woocommerce-products'); ?></a>
	                                </div>
	                                <div class="thsdf-right">
	                                    <a class="thsdf-link thsdf-right-link thsdf-active" target="_blank" href="https://help.themehigh.com/hc/en-us/requests/new"><?php echo __('Get Support', 'schedule-delivery-for-woocommerce-products'); ?></a>
	                                    <a class="thsdf-link thsdf-right-link thsdf-active thsdf-submit-deactivate" href="#"><?php echo __('Submit and Deactivate', 'schedule-delivery-for-woocommerce-products'); ?></a>
	                                    <a class="thsdf-link thsdf-right-link thsdf-close" href="#"><?php echo __('Cancel', 'schedule-delivery-for-woocommerce-products'); ?></a>
	                                </div>
	                            </footer>
	                        </div>
	                    </div>
	                </div>
	            </div>
	        </div>
	        <style type="text/css">
	            .th-logo{
	                margin-right: 10px;
	            }
	            .thpladmin-modal-mask{
	                position: fixed;
	                background-color: rgba(17,30,60,0.6);
	                top: 0;
	                left: 0;
	                width: 100%;
	                height: 100%;
	                z-index: 9999;
	                overflow: scroll;
	                transition: opacity 250ms ease-in-out;
	            }
	            .thpladmin-modal-mask{
	                display: none;
	            }
	            .thpladmin-modal .modal-container{
	                position: absolute;
	                background: #fff;
	                border-radius: 2px;
	                overflow: hidden;
	                left: 50%;
	                top: 50%;
	                transform: translate(-50%,-50%);
	                width: 50%;
	                max-width: 960px;
	                /*min-height: 560px;*/
	                /*height: 80vh;*/
	                /*max-height: 640px;*/
	                animation: appear-down 250ms ease-in-out;
	                border-radius: 15px;
	            }
	            .model-header {
	                padding: 21px;
	            }
	            .thpladmin-modal .model-header span {
	                font-size: 18px;
	                font-weight: bold;
	            }
	            .thpladmin-modal .model-header {
	                padding: 21px;
	                background: #ECECEC;
	            }
	            .thpladmin-modal .form-container {
	                margin-left: 23px;
	                clear: both;
	            }
	            .thpladmin-modal .deactivation-reason input {
	                margin-right: 13px;
	            }
	            .thpladmin-modal .thsdf-privacy-cnt {
	                color: #919191;
	                font-size: 12px;
	                margin-bottom: 31px;
	                margin-top: 18px;
	                max-width: 75%;
	            }
	            .thpladmin-modal .deactivation-reason li {
	                margin-bottom: 17px;
	            }
	            .thpladmin-modal .modal-footer {
	                padding: 20px;
	                border-top: 1px solid #E7E7E7;
	                float: left;
	                width: 100%;
	                box-sizing: border-box;
	            }
	            .thsdf-left {
	                float: left;
	            }
	            .thsdf-right {
	                float: right;
	            }
	            .thsdf-link {
	                line-height: 31px;
	                font-size: 12px;
	            }
	            .thsdf-left-link {
	                font-style: italic;
	            }
	            .thsdf-right-link {
	                padding: 0px 20px;
	                border: 1px solid;
	                display: inline-block;
	                text-decoration: none;
	                border-radius: 5px;
	            }
	            .thsdf-right-link.thsdf-active {
	                background: #0773AC;
	                color: #fff;
	            }
	            .thsdf-title-text {
	                color: #2F2F2F;
	                font-weight: 500;
	                font-size: 15px;
	            }
	            .reason-input {
	                margin-left: 31px;
	                margin-top: 11px;
	                width: 70%;
	            }
	            .reason-input input {
	                width: 100%;
	                height: 40px;
	            }
	            .reason-input textarea {
	                width: 100%;
	                min-height: 80px;
	            }
	            input.th-snooze-checkbox {
	                width: 15px;
	                height: 15px;
	            }
	            input.th-snooze-checkbox:checked:before {
	                width: 1.2rem;
	                height: 1.2rem;
	            }
	            .th-snooze-select {
	                margin-left: 20px;
	                width: 172px;
	            }

	            /* Version B */
	            .get-support-version-b {
	                width: 100%;
	                padding-left: 23px;
	                clear: both;
	                float: left;
	                box-sizing: border-box;
	                background: #0673ab;
	                color: #fff;
	                margin-bottom: 20px;
	            }
	            .get-support-version-b p {
	                font-size: 12px;
	                line-height: 17px;
	                width: 70%;
	                display: inline-block;
	                margin: 0px;
	                padding: 15px 0px;
	            }
	            .get-support-version-b .thsdf-right-link {
	                background-image: url(<?php echo esc_url(THSDF_URL .'admin/assets/img/get_support_icon.svg'); ?>);
	                background-repeat: no-repeat;
	                background-position: 11px 10px;
	                padding-left: 31px;
	                color: #0773AC;
	                background-color: #fff;
	                float: right;
	                margin-top: 17px;
	                margin-right: 20px;
	            }
	            .thsdf-privacy-link {
	                font-style: italic;
	            }
	        </style>

	        <script type="text/javascript">
	            (function($){
	                var popup = $("#thsdf_deactivation_form");
	                var deactivation_link = '';

	                $('.thsdf-deactivate-link').on('click', function(e){
	                    e.preventDefault();
	                    deactivation_link = $(this).attr('href');
	                    popup.css("display", "block");
	                    popup.find('a.thsdf-deactivate').attr('href', deactivation_link);
	                });

	                popup.on('click', 'input[type="radio"]', function () {
	                    var parent = $(this).parents('li:first');
	                    popup.find('.reason-input').remove();

	                    var type = parent.data('type');
	                    var placeholder = parent.data('placeholder');

	                    var reason_input = '';
	                    if('text' == type){
	                        reason_input += '<div class="reason-input">';
	                        reason_input += '<input type="text" placeholder="'+ placeholder +'">';
	                        reason_input += '</div>';
	                    }else if('textarea' == type){
	                        reason_input += '<div class="reason-input">';
	                        reason_input += '<textarea row="5" placeholder="'+ placeholder +'">';
	                        reason_input += '</textarea>';
	                        reason_input += '</div>';
	                    }else if('checkbox' == type){
	                        reason_input += '<div class="reason-input ">';
	                        reason_input += '<input type="checkbox" id="th-snooze" name="th-snooze" class="th-snooze-checkbox">';
	                        reason_input += '<label for="th-snooze">Snooze this panel while troubleshooting</label>';
	                        reason_input += '<select name="th-snooze-time" class="th-snooze-select" disabled>';
	                        reason_input += '<option value="<?php echo HOUR_IN_SECONDS ?>">1 Hour</option>';
	                        reason_input += '<option value="<?php echo 12*HOUR_IN_SECONDS ?>">12 Hour</option>';
	                        reason_input += '<option value="<?php echo DAY_IN_SECONDS ?>">24 Hour</option>';
	                        reason_input += '<option value="<?php echo WEEK_IN_SECONDS ?>">1 Week</option>';
	                        reason_input += '<option value="<?php echo MONTH_IN_SECONDS ?>">1 Month</option>';
	                        reason_input += '</select>';
	                        reason_input += '</div>';
	                    }

	                    if(reason_input !== ''){
	                        parent.append($(reason_input));
	                    }
	                });

	                popup.on('click', '.thsdf-close', function () {
	                    popup.css("display", "none");
	                });

	                popup.on('click', '.thsdf-submit-deactivate', function (e) {
	                    e.preventDefault();
	                    var button = $(this);
	                    if (button.hasClass('disabled')) {
	                        return;
	                    }
	                    var radio = $('.deactivation-reason input[type="radio"]:checked');
	                    var parent_li = radio.parents('li:first');
	                    var parent_ul = radio.parents('ul:first');
	                    var input = parent_li.find('textarea, input[type="text"]');
	                    var sdf_deacive_nonce = parent_ul.data('nonce');

	                    $.ajax({
	                        url: ajaxurl,
	                        type: 'POST',
	                        data: {
	                            action: 'thsdf_deactivation_reason',
	                            reason: (0 === radio.length) ? 'none' : radio.val(),
	                            comments: (0 !== input.length) ? input.val().trim() : '',
	                            security: sdf_deacive_nonce,
	                        },
	                        beforeSend: function () {
	                            button.addClass('disabled');
	                            button.text('Processing...');
	                        },
	                        complete: function () {
	                            window.location.href = deactivation_link;
	                        }
	                    });
	                });

	                popup.on('click', '#th-snooze', function () {
	                    if($(this).is(':checked')){
	                        popup.find('.th-snooze-select').prop("disabled", false);
	                    }else{
	                        popup.find('.th-snooze-select').prop("disabled", true);
	                    }
	                });

	            }(jQuery))
	        </script>

	        <?php 
    	}

    	private function get_deactivation_reasons(){
	        return array(
	            'found_better_plugin' => array(
	                'radio_val'          => 'found_better_plugin',
	                'radio_label'        => __('I found a better Plugin', 'schedule-delivery-for-woocommerce-products'),
	                'reason_type'        => 'text',
	                'reason_placeholder' => __('Could you please mention the plugin?', 'schedule-delivery-for-woocommerce-products'),
	            ),

	            'hard_to_use' => array(
	                'radio_val'          => 'hard_to_use',
	                'radio_label'        => __('It was hard to use', 'schedule-delivery-for-woocommerce-products'),
	                'reason_type'        => 'text',
	                'reason_placeholder' => __('How can we improve your experience?', 'schedule-delivery-for-woocommerce-products'),
	            ),

	            'feature_missing'=> array(
	                'radio_val'          => 'feature_missing',
	                'radio_label'        => __('A specific feature is missing', 'schedule-delivery-for-woocommerce-products'),
	                'reason_type'        => 'text',
	                'reason_placeholder' => __('Type in the feature', 'schedule-delivery-for-woocommerce-products'),
	            ),

	            'not_working_as_expected'=> array(
	                'radio_val'          => 'not_working_as_expected',
	                'radio_label'        => __('The plugin didn’t work as expected', 'schedule-delivery-for-woocommerce-products'),
	                'reason_type'        => 'text',
	                'reason_placeholder' => __('Specify the issue', 'schedule-delivery-for-woocommerce-products'),
	            ),

	            'temporary' => array(
	                'radio_val'          => 'temporary',
	                'radio_label'        => __('It’s a temporary deactivation - I’m troubleshooting an issue', 'schedule-delivery-for-woocommerce-products'),
	                'reason_type'        => 'checkbox',
	                'reason_placeholder' => __('Could you please mention the plugin?', 'schedule-delivery-for-woocommerce-products'),
	            ),

	            'other' => array(
	                'radio_val'          => 'other',
	                'radio_label'        => __('Not mentioned here', 'schedule-delivery-for-woocommerce-products'),
	                'reason_type'        => 'textarea',
	                'reason_placeholder' => __('Kindly tell us your reason, so that we can improve', 'schedule-delivery-for-woocommerce-products'),
	            ),
	        );
    	}

    	public function thsdf_deactivation_reason(){
	        global $wpdb;

	        check_ajax_referer('thsdf_deactivate_nonce', 'security');

	        if(!isset($_POST['reason'])){
	            return;
	        }

	        if($_POST['reason'] === 'temporary'){

	            $snooze_period = isset($_POST['th-snooze-time']) && $_POST['th-snooze-time'] ? $_POST['th-snooze-time'] : MINUTE_IN_SECONDS ;
	            $time_now = time();
	            $snooze_time = $time_now + $snooze_period;

	            update_user_meta(get_current_user_id(), 'thsdf_deactivation_snooze', $snooze_time);

	            return;
	        }
	        
	        $data = array(
	            'plugin'        => 'sdf',
	            'reason'        => sanitize_text_field($_POST['reason']),
	            'comments'      => isset($_POST['comments']) ? sanitize_textarea_field(wp_unslash($_POST['comments'])) : '',
	            'date'          => gmdate("M d, Y h:i:s A"),
	            'software'      => $_SERVER['SERVER_SOFTWARE'],
	            'php_version'   => phpversion(),
	            'mysql_version' => $wpdb->db_version(),
	            'wp_version'    => get_bloginfo('version'),
	            'wc_version'    => (!defined('WC_VERSION')) ? '' : WC_VERSION,
	            'locale'        => get_locale(),
	            'multisite'     => is_multisite() ? 'Yes' : 'No',
	            'plugin_version'=> THSDF_VERSION
	        );

	        $response = wp_remote_post('https://feedback.themehigh.in/api/add_feedbacks', array(
	            'method'      => 'POST',
	            'timeout'     => 45,
	            'redirection' => 5,
	            'httpversion' => '1.0',
	            'blocking'    => false,
	            'headers'     => array( 'Content-Type' => 'application/json' ),
	            'body'        => json_encode($data),
	            'cookies'     => array()
	                )
	        );

	        wp_send_json_success();
	    }
		
	}
endif;