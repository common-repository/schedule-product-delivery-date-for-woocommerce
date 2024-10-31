<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    schedule-delivery-for-woocommerce-products
 * @subpackage schedule-delivery-for-woocommerce-products/public
 */
if(!defined('WPINC')) {  
    die; 
}

if(!class_exists('THSDF_Public')) :
     
    /**
     * Public class.
     */ 
    class THSDF_Public {
        private $plugin_name;
        private $version;

        /**
         * Constructor.
         *
         * @param string $plugin_name The plugin name
         * @param string $version The plugin version number
         */
        public function __construct($plugin_name, $version) {
            $this->plugin_name = $plugin_name;
            $this->version = $version;
            add_action('after_setup_theme', array($this, 'define_public_hooks'));
        }

        /**
         * Enqueue script and style.
         */
        public function enqueue_styles_and_scripts() {
            global $wp_scripts;
            $is_quick_view = THSDF_Utils::is_quick_view_plugin_active();
            if(is_cart() ||is_checkout() || is_account_page()|| ($is_quick_view && (is_shop() || is_product_category()))) {
                $debug_mode = apply_filters('thsdf_debug_mode', false);
                $suffix = $debug_mode ? '' : '.min';
                $jquery_version = isset($wp_scripts->registered['jquery-ui-core']->ver) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.9.2';
                
                $this->enqueue_styles($suffix, $jquery_version);
                $this->enqueue_scripts($suffix, $jquery_version, $is_quick_view);
            }
            if(is_product() ) {
                $debug_mode = apply_filters('thsdf_debug_mode', false);
                $suffix = $debug_mode ? '' : '.min';
                $jquery_version = isset($wp_scripts->registered['jquery-ui-core']->ver) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.9.2';

                $product_id = get_the_ID();
                $delivary_checkbox = get_post_meta($product_id, THSDF_Utils::POST_KEY_PLAN_DELIVERY_CHECKBOX, true);
                $product = wc_get_product( $product_id );
                if ($product->is_purchasable() && $product->is_in_stock()){
                    if($delivary_checkbox == 'yes') {
                        $this->enqueue_styles($suffix, $jquery_version);
                        $this->enqueue_scripts($suffix, $jquery_version, $is_quick_view);
                    }
                }
            }
        }
        
        /**
         * Function for enqueue public hooks.
         */
        public function define_public_hooks() {
            
        	add_action('woocommerce_before_add_to_cart_button', array($this, 'calendar_content'), 21);
        	
        	add_filter('woocommerce_add_cart_item_data',array($this, 'add_cart_item_data'), 10, 4);

        	// date:qty list below cart name and checkout page list.
        	add_filter('woocommerce_get_item_data',array($this, 'cart_item_product_name_extra'), 20, 3);
        	if(THSDF_Utils::woo_version_check()) {
                add_action('woocommerce_new_order_item',array($this, 'new_order_item'), 1, 3);
            }else{
                add_action('woocommerce_add_order_item_meta',array($this, 'add_order_item_meta'), 1, 3);
            }
            add_action('woocommerce_after_checkout_validation',array($this, 'checkout_validation'), 1, 2);
            add_filter('woocommerce_order_item_get_formatted_meta_data', array($this, 'order_item_get_formatted_meta_data'), 10, 2);
            add_filter('woocommerce_order_item_get_formatted_meta_data', array($this, 'order_item_visible_data'), 10, 2);

        	add_filter('woocommerce_cart_item_quantity', array($this, 'cart_item_quantity'), 10, 3);
        	add_filter('woocommerce_product_add_to_cart_text', array($this, 'add_to_cart_text'), 10, 2);

        	if(THSDF_Utils::woo_version_check('3.3')) {
                add_filter('woocommerce_loop_add_to_cart_link', array($this, 'loop_add_to_cart_link'), 10, 3);
            }else {
                add_filter('woocommerce_loop_add_to_cart_link', array($this, 'loop_add_to_cart_link'), 10, 2);
            }

            $theme_name = THSDF_Utils::check_current_theme();
            if($theme_name == 'flatsome') {
                add_filter('woocommerce_locate_template', array($this, 'woocommerce_locate_template'), 10, 3);
            }

            //Order again.
            add_filter('woocommerce_order_again_cart_item_data', array($this, 'filter_order_again_cart_item_data'), 10, 3); 

        	// Update price.
            add_action('wp_ajax_update_product_price', array($this, 'update_product_price'), 10);
            add_action('wp_ajax_nopriv_update_product_price', array($this, 'update_product_price'), 10);
        }

        /**
         * Function for enqueue style.
         *
         * @param string $suffix The suffix of the style sheet file
         * @param string $jquery_version The passed jquery version
         */
        private function enqueue_styles($suffix, $jquery_version) {
           // wp_enqueue_style('jquery-ui-style', '//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css?ver=1.11.4');
           // wp_enqueue_style('jquery-ui');
          //  wp_enqueue_style('FontAwesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css');
            wp_enqueue_style('thsdf-public-style', THSDF_ASSETS_URL_PUBLIC . 'css/thsdf-public'. $suffix .'.css', $this->version);
            wp_enqueue_style('dashicons');

            $theme_name = THSDF_Utils::check_current_theme();
            if($theme_name == 'twentynineteen') {
                $custom_css = "
                    dl.variation p {
                        margin-top: 0;
                    }";
                wp_add_inline_style('thsdf-public-style', $custom_css);
            }
        }

        /**
         * Function for enqueue script.
         *
         * @param string $suffix The suffix of the style sheet file
         * @param string $jquery_version The passed jquery version 
         * @param string $is_quick_view string for check quick view of the theme flatsome
         */
        private function enqueue_scripts($suffix, $jquery_version, $is_quick_view) {
            wp_register_script('thsdf_public_script', THSDF_ASSETS_URL_PUBLIC . 'js/thsdf-public'. $suffix .'.js', array('jquery', 'jquery-ui-dialog', 'jquery-ui-accordion', 'select2','jquery-ui-datepicker'), $this->version, true);

            $theme_name = THSDF_Utils::check_current_theme();
            if($theme_name == 'astra' || $theme_name == 'shapely' || $theme_name == 'hestia' || $theme_name == 'divi' || $theme_name == 'twentyeleven' || $theme_name == 'twentytwelve' || $theme_name == 'twentyfourteen' || $theme_name == 'twentyfifteen' || $theme_name == 'twentysixteen' ||$theme_name == 'twentynineteen') {
                $special_theme_class = esc_attr('special_theme_class');
            } else if($theme_name == 'flatsome') {
                $special_theme_class = esc_attr('special_flatsome_class');
            } else {
                $special_theme_class = '';
            }

            $script_var = array(
                'ajax_url'          => admin_url('admin-ajax.php'),
                'is_quick_view'     => $is_quick_view,
                'spec_class'        => $special_theme_class,
                'january'           => esc_html__('january', 'schedule-delivery-for-woocommerce-products'),
                'february'          => esc_html__('february', 'schedule-delivery-for-woocommerce-products'),
                'march'             => esc_html__('march', 'schedule-delivery-for-woocommerce-products'),
                'april'             => esc_html__('april', 'schedule-delivery-for-woocommerce-products'),
                'may'               => esc_html__('may', 'schedule-delivery-for-woocommerce-products'),
                'june'              => esc_html__('june', 'schedule-delivery-for-woocommerce-products'),
                'july'              => esc_html__('july', 'schedule-delivery-for-woocommerce-products'),
                'august'            => esc_html__('august', 'schedule-delivery-for-woocommerce-products'),
                'september'         => esc_html__('september', 'schedule-delivery-for-woocommerce-products'),
                'october'           => esc_html__('october', 'schedule-delivery-for-woocommerce-products'),
                'november'          => esc_html__('november', 'schedule-delivery-for-woocommerce-products'),
                'december'          => esc_html__('december', 'schedule-delivery-for-woocommerce-products'),
                'sunday'            => esc_html__('Sunday', 'schedule-delivery-for-woocommerce-products'),
                'monday'            => esc_html__('Monday', 'schedule-delivery-for-woocommerce-products'),
                'tuesday'           => esc_html__('Tuesday', 'schedule-delivery-for-woocommerce-products'),
                'wednesday'         => esc_html__('Wednesday', 'schedule-delivery-for-woocommerce-products'),
                'thursday'          => esc_html__('Thursday', 'schedule-delivery-for-woocommerce-products'),
                'friday'            => esc_html__('Friday', 'schedule-delivery-for-woocommerce-products'),
                'saturday'          => esc_html__('Saturday', 'schedule-delivery-for-woocommerce-products'),
                'no_delivery'       => esc_html__('No Delivery', 'schedule-delivery-for-woocommerce-products'),
                'qty_limit_tooltip' => esc_html__('The maximum allowed quantity of this product on a single day is', 'schedule-delivery-for-woocommerce-products'),
                'update_price_nonce'=> wp_create_nonce('update-product-price-nonce'),
            );
            wp_localize_script('thsdf_public_script', 'thsdf_public_var', $script_var);
            wp_enqueue_script('thsdf_public_script');
            
        }

        /**
         * Product delivery calendar creation.
         * 
         * @return void
         */
        public function calendar_content() {
            $date_components = getdate();
            $month = $date_components['mon'];      
            $year = $date_components['year'];
            $delivery_day_g = array();
            $start_date_g = '';
            $end_date_g = '';
            $product_id = get_the_ID();

            // Single product - setting info.
            $delivary_checkbox = get_post_meta($product_id, THSDF_Utils::POST_KEY_PLAN_DELIVERY_CHECKBOX, true);
            $global_setting = get_post_meta($product_id, THSDF_Utils::POST_KEY_PLAN_DELIVERY_GENERAL, true);
            $get_single_calender_settings = get_post_meta($product_id, THSDF_Utils::POST_KEY_SINGLE_SETTINGS, true);
            $get_single_display_settings = get_post_meta($product_id, THSDF_Utils::POST_KEY_SINGLE_DISPLAY_SETTINGS, true);

            //General - setting info.
            $get_general_settings = get_option(THSDF_Utils::OPTION_KEY_DELIVERY_SETTINGS);
            $get_display_settings = get_option(THSDF_Utils::OPTION_KEY_DISPLAY_SETTINGS);

            if($get_single_calender_settings != '') {
                $delivery_day_s = isset($get_single_calender_settings['delivery_day']) ? $get_single_calender_settings['delivery_day'] : "";
                $start_date_s = isset($get_single_calender_settings['start_date']) ? $get_single_calender_settings['start_date'] : "";
                $end_date_s = isset($get_single_calender_settings['end_date']) ? $get_single_calender_settings['end_date'] : "";
            }

            if($get_general_settings != '') {
                $delivery_day_g = isset($get_general_settings['delivery_day']) ? $get_general_settings['delivery_day'] : "";
                $start_date_g = isset($get_general_settings['start_date']) ? $get_general_settings['start_date'] : "";
                $end_date_g = isset($get_general_settings['end_date']) ? $get_general_settings['end_date'] : "";
            }

            $html_tags = THSDF_Utils::allowed_html_tags();

            $current_date = date('Y-m-d');
            if($delivary_checkbox == 'yes') {
                if($global_setting == 'yes') {
                    if(($get_general_settings != '') || ($get_display_settings != '')) {
                        if(($delivery_day_g != '') && ($start_date_g != '') && ($end_date_g != '')) {
                            if($end_date_g >= $current_date) {
                                $build_calendar = $this->build_calendar($month, $year, $get_general_settings, $get_display_settings);

                                echo wp_kses($build_calendar, $html_tags);
                                
                            } else {
                                echo '<p class="settings_upadte_mssg">'.esc_html__('Please Update your calendar settings', 'schedule-delivery-for-woocommerce-products').'</p>';
                            }
                        }
                    }
                } else {
                    if(($get_single_calender_settings != '')||($get_single_display_settings != '')) {
                        if(($delivery_day_s != '') && ($start_date_s != '') && ($end_date_s != '')) {
                            if($end_date_s >= $current_date) {
                                $build_calendar  = $this->build_calendar($month, $year, $get_single_calender_settings, $get_single_display_settings);

                                echo wp_kses($build_calendar, $html_tags);
                            } else {
                                echo '<p class="settings_upadte_mssg">'.esc_html__('Please Update your calendar settings', 'schedule-delivery-for-woocommerce-products').'</p>';
                            }
                        }
                    }
                }
            }
        }

        /**
         * Build Product delivery calendar calendar.
         * 
         * @return data.
         */
        public function build_calendar($month, $year, $gen_array, $dis_array) {
        	$ptdt_calendar_data = $this->get_session_content();
        	
            // General Setting Data.
            if($gen_array != '') {
                $week_start         = isset($gen_array['week_start']) ? $gen_array['week_start'] : "";
                $delivery_day       = isset($gen_array['delivery_day']) ? $gen_array['delivery_day'] : "";
                $start_date         = isset($gen_array['start_date']) ? $gen_array['start_date'] : "";
                $end_date           = isset($gen_array['end_date']) ? $gen_array['end_date'] : "";
                $holidays           = isset($gen_array['holidays']) ? $gen_array['holidays'] : "";

            } else {
                $week_start = "";
                $delivery_day = "";
                $start_date = "";
                $end_date = "";
                $holidays = "";
                
            }

            // Display Setting Data.
            if($dis_array != '') {
                $calendar_border = isset($dis_array['calendar_border']) ? $dis_array['calendar_border'] : "";
                $day_bg = isset($dis_array['day_bg']) ? $dis_array['day_bg'] : "";
                $nav_arrow = isset($dis_array['nav_arrow']) ? $dis_array['nav_arrow'] : "";
                $tooltip_bg = isset($dis_array['tooltip_bg']) ? $dis_array['tooltip_bg'] : "";
                $date_color = isset($dis_array['date_color']) ? $dis_array['date_color'] : "";
                $day_title_color = isset($dis_array['day_title_color']) ? $dis_array['day_title_color'] : "";
                $day_color = isset($dis_array['day_color']) ? $dis_array['day_color'] : "";
                $holiday_color = isset($dis_array['holiday_color']) ? $dis_array['holiday_color'] : "";
                $tooltip_text = isset($dis_array['tooltip_text']) ? $dis_array['tooltip_text'] : "";
                $input_text = isset($dis_array['input_text']) ? $dis_array['input_text'] : "";
            } else {
                $calendar_border = "";
                $day_bg = "";
                $nav_arrow = "";
                $tooltip_bg = "";
                $date_color = "";
                $day_title_color = "";
                $day_color = "";               
                $holiday_color = "";
                $tooltip_text = "";
                $input_text = "";
            }

            // Call function to get the array of week days, orderd with respect to start day.
            $days_of_week = $this->week_start_day($week_start);

            //Set Disabled week days.
            $disabled_weekdays = $this->set_disabled_weekdays($delivery_day, $start_date, $end_date);

            // First day of the month.
            $first_day_of_month = mktime(0,0,0,$month,1,$year);

            // Total days in this month.
            $number_days = date('t',$first_day_of_month);

            // Date components.
            $date_components = getdate($first_day_of_month);

            // Month.
            $month_name = $date_components['month'];
            $month_name_uppr = ucfirst($month_name);
            $month_name_sub = substr($month_name_uppr,0,3);
            // Number of blank fields before start day.
            $day_of_week = $date_components['wday'];

            $theme_class_name = THSDF_Utils::check_current_theme();
            $theme_class = 'thsdf-'.esc_attr($theme_class_name).'-calendar-wrapper-public';

            $calendar = '<div class="ptdt-calendar-wrapper-public '.esc_attr($theme_class).'">';
                $calendar .= '<div class="ptdelivery-controls">';
                    $calendar .= '<div class="ptdelivery-controls-buttons">';
                        $calendar .= '<a class="button ptdelivery-prev" id="thsdf_prev"><span class="dashicons dashicons-arrow-left ptdt-nav-arrow"></span></a>';
                        $calendar .= '<div id="ptdelivery-date" class="ptdt_general_color">'.esc_html__($month_name_sub,'schedule-delivery-for-woocommerce-products') .''. esc_attr($year).'</div>';
                        $calendar .= '<a class="button ptdelivery-next" id="thsdf_next"><span class="dashicons dashicons-arrow-right ptdt-nav-arrow"></span></a>';
                    $calendar .= '</div>';
                $calendar .= '</div>';

                $daynum = date("w", strtotime($week_start));

                // Product price.
                $product_id = get_the_ID();
                $_product = wc_get_product($product_id);

                $price = $_product->get_price();
                $calendar .= '<input type="hidden" name="product_price" value="'.esc_attr($price).'" id="ptdt_product_price">';
                $price_formated = wc_price(wc_format_decimal($price));

                // Currency symbol.
                $currency_symbol = get_woocommerce_currency_symbol();
                $calendar .= '<input type="hidden" name="currency_symbol" value="'.esc_attr($currency_symbol).'" id="ptdt_currency_symbol">';

                // Current date.
                $current_date = date('Y-m-d', current_time('timestamp'));
                $calendar .= '<input type="hidden" name="current_date" value="'.esc_attr($current_date).'" id="ptdt_current_date">';

                // Hidden fields for general settings.
                
                $calendar .= '<input type="hidden" name="product_id" value="'.esc_attr($product_id).'" id="ptdt_product_id">'; 

                // Set week start day.
                $week_day = json_encode($days_of_week);
                $calendar .= '<input type="hidden" name="week_start_days[]" value="'.esc_attr($week_day).'" id="ptdt_week_start_days" data-daynum="'.esc_attr($daynum).'">';

                // Set disabled week days.
                $disabled_days = json_encode($disabled_weekdays);
                $calendar .= '<input type="hidden" name="week_disable_days[]" value="'.esc_attr($disabled_days).'" id="ptdt_week_disable_days">';

                // Set Minimum price limit for daily purchase.
                $product_price = apply_filters('thsdf_minimum_product_price', 0, $product_id);
                $calendar .= '<input type="hidden" name="min_price" value="'.esc_attr($product_price).'" id="ptdt_min_price">'; 
                $new_start_date = '';
                // Set start date.
                if($current_date > $start_date) {
                    $new_start_date = $current_date;
                } else {
                    $new_start_date = $start_date;
                }
                

                $calendar .= '<input type="hidden" name="schedule_start_date" value="'.esc_attr($start_date).'" id="ptdt_schedule_start_date">'; 
                $calendar .= '<input type="hidden" name="calendar_start_date" value="'.esc_attr($new_start_date).'" id="ptdt_start_date">'; 

                // Set End date.
                $calendar .= '<input type="hidden" name="calendar_end_date" value="'.esc_attr($end_date).'" id="ptdt_end_date">';

                // Set holidays.
                $holidays = json_encode($holidays);
                $calendar .= '<input type="hidden" name="week_holidays[]" value="'.esc_attr($holidays).'" id="ptdt_week_holidays">';

                 
                // Hidden fields for display settings.
                $calendar .= '<input type="hidden" name="calendar_border_code" value="'.esc_attr($calendar_border).'" id="ptdt_calendar_border_code">'; 
                $calendar .= '<input type="hidden" name="day_bg_code" value="'.esc_attr($day_bg).'" id="ptdt_day_bg_code">'; 
                $calendar .= '<input type="hidden" name="nav_arrow_code" value="'.esc_attr($nav_arrow).'" id="ptdt_nav_arrow_code">'; 
                $calendar .= '<input type="hidden" name="tooltip_bg_code" value="'.esc_attr($tooltip_bg).'" id="ptdt_tooltip_bg_code">'; 
                $calendar .= '<input type="hidden" name="day_title_color_code" value="'.esc_attr($day_title_color).'" id="ptdt_day_title_color_code">'; 
                $calendar .= '<input type="hidden" name="date_color_code" value="'.esc_attr($date_color).'" id="ptdt_date_color_code">'; 
                $calendar .= '<input type="hidden" name="day_color_code" value="'.esc_attr($day_color).'" id="ptdt_day_color_code">'; 
                $calendar .= '<input type="hidden" name="holiday_color_code" value="'.esc_attr($holiday_color).'" id="ptdt_holiday_color_code">';
                $calendar .= '<input type="hidden" name="tooltip_text_code" value="'.esc_attr($tooltip_text).'" id="ptdt_tooltip_text_code">';
                $calendar .= '<input type="hidden" name="input_text_code" value="'.esc_attr($input_text).'" id="ptdt_input_text_code">';

                // Create the table.
                $calendar .= '<table class="ptdelivery-calendar" id="ptdelivery-calendar">';
                    $calendar .= '<tr>';
                    // Create the calendar headers.
                    if(!empty($days_of_week) && is_array($days_of_week)) {
                        foreach($days_of_week as $day) {
                            $calendar .= '<th class="ptdelivery-calendar-header"><div class="calendar-week-day">'.esc_html__($day,'schedule-delivery-for-woocommerce-products').'</div></th>';
                        } 
                    }

                    $count_days = '';
                    $count_days = count($days_of_week);
                    $current_day = 1;
                    $newday_of_week = '';

                    $calendar .= '</tr><tr>';
                    $newday_of_week = $day_of_week - $daynum;
                    if ($newday_of_week > 0) { 
                        $calendar .= '<td colspan="'.esc_attr($newday_of_week).'" class="ptdelivery-calendar-day ptdelivery-calendar-other">&nbsp;</td>'; 
                    } else if($newday_of_week < 0) {
                        $newday_of_week = $count_days - $daynum + $day_of_week;
                        $calendar .= '<td colspan="'.esc_attr($newday_of_week).'" class="ptdelivery-calendar-day ptdelivery-calendar-other">&nbsp;</td>';
                    }
                     
                    $month = str_pad($month, 2, "0", STR_PAD_LEFT);
                    while ($current_day <= $number_days) {
                        if ($newday_of_week == 7) {
                            $newday_of_week = 0;
                            $calendar .= '</tr><tr>';
                        }                 
                        $current_day_Rel = str_pad($current_day, 2, "0", STR_PAD_LEFT);

                        $date = "$year-$month-$current_day_Rel";
                        $calendar .= '<td class="ptdt_date_td ptdelivery-calendar-day tooltip"  rel="'.esc_attr($date).'" title="This is the tooltip text">';
                            $calendar .= '<div class="ptdelivary-single-date">';
                            $calendar .= '<span class="ptdelivary-date-day">'.esc_attr($current_day).'</span>';
                            $calendar .= '<input type="number" name="number" class="ptdelivery-number-input" min="0">';
                            $calendar .= '</div>';
                        $calendar .= '</td>';

                        $current_day++;
                        $newday_of_week++;

                    }
                    if ($newday_of_week != 7) {            
                        $remaining_days = 7 - $newday_of_week;
                        $calendar .= '<td colspan="'.esc_attr($remaining_days).'" class="ptdt_date_td ptdelivery-calendar-day">&nbsp;</td>'; 
                    }            
                    $calendar .= '</tr>';
                $calendar .= '</table>';

                $calendar .= "<div class='hover_bkgr_fricc'>";
                $calendar .= "</div>";
                $calendar .= "<div class='end-date-popup-view'>";
                    $calendar .= '<span class="helper"></span>';
                    $calendar .= "<div class='end-date-popup-view-wrap'>";
                    $calendar .= "</div>";
                $calendar .= "</div>";

                $calendar .= '<div id="total_product_quantity" class="">';
                    $calendar .= '<p id="product_total_price_value">';
                        $calendar .= wc_price(wc_format_decimal(0));
                    $calendar .= '</p>';
                $calendar .= '</div>';

                // Hidden Submited datas.
                $calendar_data = '';
                $calendar_data = json_encode($ptdt_calendar_data);
                $calendar .= '<input type="hidden" name="calendar_data" value="'.esc_attr($calendar_data).'" id="ptdt_calendar_datas">'; 
            $calendar .= '</div>';

            return $calendar;
        }

         /**
         * Get cart item content from front end display.
         * 
         * @return array.
         */
        public function get_session_content() {
            $calendar_settings = isset($_POST['calendar_data']) ? wc_clean($_POST['calendar_data']) : '';
            if(!empty($calendar_settings)) {
                $calendar_settings_data = json_decode(stripslashes($calendar_settings), true);
                $cart_delivery_data = $calendar_settings_data ;
            } else {
                $cart_delivery_data = '';
            }
            return $cart_delivery_data;
        }
        /**
         * Function for set week starting day.
         * 
         * @param string $week_start The week starting day.
         *
         * @return array.
         */
        public function week_start_day($week_start) {
            $days_of_week_arr = array();
            $days_of_week_arr = array(0 => 'Sun', 1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat');
            $newdate_arr = $this->set_weekstart_day($week_start, $days_of_week_arr);
            return $newdate_arr;
        }

        /**
         * FUnction for getting week start day.
         * 
         * @param string $week_start The week starting day.
         * @param array $days_of_week_arr  All days of a week.
         *
         * @return array.
         */
        public function set_weekstart_day($week_start, $days_of_week_arr) {
            $week = [
                esc_html__('Sun', 'schedule-delivery-for-woocommerce-products'),
                esc_html__('Mon', 'schedule-delivery-for-woocommerce-products'),
                esc_html__('Tue', 'schedule-delivery-for-woocommerce-products'),
                esc_html__('Wed', 'schedule-delivery-for-woocommerce-products'),
                esc_html__('Thu', 'schedule-delivery-for-woocommerce-products'),
                esc_html__('Fri', 'schedule-delivery-for-woocommerce-products'),
                esc_html__('Sat', 'schedule-delivery-for-woocommerce-products'),
            ];
            $daynum = date("w", strtotime($week_start));
            for ($i=0; $i <= $daynum-1 ; $i++) {
                array_push($week, array_shift($week));
            }
            $newdate = $week;
            return $newdate;
        }
        /**
         * Function for set disabled week-days.
         * 
         * @param string $delivery_day The setted delievery day.
         * @param string $start_date  Start date of the delivey.
         * @param string $end_date  End date of the delivey.
         *
         * @return array.
         */
        public function set_disabled_weekdays($delivery_day, $start_date, $end_date) {
            $disabled_weekdays = array();
            $no_deliverydays = '';
            $start = '';
            $end = '';           
            $no_deliverydays = $this->get_no_delivery_days($delivery_day);
            $start = $start_date;
            $end = $end_date;
            $period = floor((strtotime($end) - strtotime($start))/(24*60*60));
            for($i = 0; $i <= $period; $i++) {
                if(in_array(date('l',strtotime("$start +$i day")),$no_deliverydays))
                    $disabled_weekdays[] = date('Y-m-d',strtotime("$start +$i day"));
            }
            return $disabled_weekdays;
        }
        /**
         * Function for get the exclude dates from the delivery date.
         * 
         * @param string $days The setted delievery day
         *
         * @return array.
         */
        public function get_no_delivery_days($days) { 
            $not_avilable_days = array();
            $m = 0;
            $all_days = array(
                '0' => "Sunday",
                '1' => "Monday",
                '2' => "Tuesday",
                '3' => "Wednesday",
                '4' => "Thursday",
                '5' => "Friday",
                '6' => "Saturday",
            );
            if(!empty($all_days) && is_array($all_days)) {
                foreach($all_days as $key => $value) {
                    if($days != '') {
                        if (!in_array($key,$days)) {
                            $not_avilable_days[$m] = $all_days[$key];
                            $m++;
                        }
                    }
                }
            }
            return $not_avilable_days;
        }
        /**
         * Function for update price on product page(If wepo plugin added)-ajax function.
         *
         * @return void
         */ 
        public function update_product_price() {
            if (check_ajax_referer('update-product-price-nonce','update_price_nonce')) {
                $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : '';
                $rslt_sum = isset($_POST['rslt_sum']) ? sanitize_text_field($_POST['rslt_sum']):'';
                $ptdt_product_price = isset($_POST['ptdt_product_price']) ? sanitize_text_field($_POST['ptdt_product_price']) : '';
                $variation_id = isset($_POST['variation_id']) ? sanitize_text_field($_POST['variation_id']) : '';
                $is_variable_product = isset($_POST['is_variable_product']) ? sanitize_text_field($_POST['is_variable_product']) : '';
                
                // If THWEPO plugin activated.
                $thwepo_price = '';
                if (THSDF_Utils::is_wepo_plugin_active()) {      
                    $request_data_json = isset($_POST['priceInfoArr']) ? stripslashes(wc_clean($_POST['priceInfoArr'])) : '';
                    $return = array();
                    if($request_data_json) {
                        try {
                            $valid_data = true;
                            $request_data = json_decode($request_data_json, true);
                            $product_id = isset($request_data['product_id']) ? $request_data['product_id'] : false;
                            if(class_exists('THWEPO_Utils_Price')) {
                                $result = THWEPO_Utils_Price::calculate_total_extra_cost($request_data);                       
                                $is_variable_product = isset($request_data['is_variable_product']) ? $request_data['is_variable_product'] : false;
                                $variation_id = isset($request_data['variation_id']) ? $request_data['variation_id'] : false;
                                if($is_variable_product && !$variation_id) {
                                    $valid_data = false;
                                }                                        
                                if($result && $valid_data) {
                                    $return = array(
                                        'code' => 'E000',
                                        'message' => '',
                                        'result' => $result
                                    );
                                }else {
                                    $price_html = THWEPO_Utils_Price::get_product_price($request_data, true);
                                    if($price_html) {
                                        $return = array(
                                            'code' => 'E002',
                                            'message' => '',
                                            'result' => $price_html
                                        );
                                    }else {
                                        $return = array(
                                            'code' => 'E003',
                                            'message' => ''
                                        );
                                    }
                                }
                            }else if(class_exists('THWEPO_Price')) {
                                $thwepo_price = new THWEPO_Price;
                                $return = $thwepo_price->get_calculated_extra_price_response($request_data);
                            }

                        } catch (Exception $e) {
                            $return = array(
                                'code' => 'E004',
                                'message' => $e->getMessage()
                            );
                        }
                    }
                    if(!empty($return)) {
                        if(array_key_exists("price_data",$return)) {
                            $thwepo_price = isset($return['price_data']['price_final']) ? $return['price_data']['price_final']: '';
                        } else if(array_key_exists("result",$return)) {
                            $thwepo_price = isset($return['result']['final_price']) ? $return['result']['final_price']: '';
                        }
                    }
                    if($thwepo_price == '') {
                        if($is_variable_product) {
                            if($variation_id) {
                                $variation_id = $variation_id;
                                $product_variation = new WC_Product_Variation($variation_id);
                                $variation_price =  $product_variation->get_price();
                                $ptdt_product_price = $variation_price;
                            } else {
                                $ptdt_product_price = 0;
                            }
                        } else {
                            $ptdt_product_price = $ptdt_product_price;
                        }
                    }
                } else {
                    if($is_variable_product == 'true') {
                        if($variation_id != 0) {
                            $variation_id = $variation_id;
                            $product_variation = new WC_Product_Variation($variation_id);
                            $variation_price =  $product_variation->get_price();
                            $ptdt_product_price = $variation_price;
                        } else {
                            $ptdt_product_price = 0;
                        }
                    } else {
                        $ptdt_product_price = $ptdt_product_price;
                    }
                }

                $total_price = '';
                if(!empty($thwepo_price) && is_numeric($thwepo_price)) {
                    if(!empty($rslt_sum)) {
                        if(!empty($thwepo_price)) {
                            $total_price = $rslt_sum * $thwepo_price;
                        }
                    } else {
                        if(!$is_variable_product) {
                            $total_price = $thwepo_price;
                        }
                    }
                } else {
                    if(!empty($rslt_sum)) {                        
                        $total_price = $rslt_sum*$ptdt_product_price;                      
                    }
                }
                if(!empty($total_price)) {
                    $total_price_formated = wc_price(wc_format_decimal($total_price));
                    $html_tags = THSDF_Utils::allowed_html_tags();
                    echo wp_kses($total_price_formated, $html_tags);
                    
                }
                exit();
            } else{
                die();
            }
        }

        /**
         * Function for add extra data to product name field.
         * 
         * @param array $item_data The item datas
         * @param array $cart_item The cart item
         *
         * @return array.
         */
        function cart_item_product_name_extra($item_data, $cart_item) {
            if(($cart_item != null) && is_array($cart_item)) {
                $ptdt_delivery_data = array();
                $products_per_date = array();

                foreach($cart_item as $item => $value) {
                    $cart_quantity = array_key_exists('quantity', $cart_item) ? sanitize_text_field($cart_item['quantity']) : '';
                    $product_id = array_key_exists('product_id', $cart_item) ? absint($cart_item['product_id']) : '';
                    $variation_id = array_key_exists('variation_id', $cart_item) ? absint($cart_item['variation_id']) : '';
                    if($item == 'ptdt_delivery_data') {
                        $ptdt_delivery_data = array_key_exists('ptdt_delivery_data', $cart_item) ? wc_clean($cart_item['ptdt_delivery_data']) : '';         
                        $products_per_date = array_key_exists('products_per_date', $ptdt_delivery_data) ? $ptdt_delivery_data['products_per_date'] : '';
                    }
                    if(apply_filters('enable_to_rise_the_date_specific_quantity', true)) {
                        if($item == 'ptdt_delivery_data') {
                            $ptdt_delivery_data = isset($cart_item['ptdt_delivery_data']) ? wc_clean($cart_item['ptdt_delivery_data'])  :'';
                            $products_per_date = isset($ptdt_delivery_data['products_per_date']) ? $ptdt_delivery_data['products_per_date'] : '';
                            $sd_total_quantity = isset($ptdt_delivery_data['total_quantity']) ? $ptdt_delivery_data['total_quantity'] : '';
                            
                            $new_product_per_date = array();
                            if($cart_quantity != $sd_total_quantity) {
                                if($sd_total_quantity != 0) {
                                    $divider = '';
                                    if($sd_total_quantity < $cart_quantity) {
                                        $divider = $cart_quantity/$sd_total_quantity;
                                    }
                                    $sd_products_per_date = $ptdt_delivery_data;    

                                    if(isset($sd_products_per_date['products_per_date'])) {
                                        if(!empty($sd_products_per_date['products_per_date']) && is_array($sd_products_per_date['products_per_date'])) {
                                            foreach ($sd_products_per_date['products_per_date'] as $key_date => $qty_info) {
                                                if(!empty($divider)) {
                                                    if(!is_array($qty_info)) {
                                                        $new_product_per_date[$key_date] = $qty_info*$divider;  
                                                    } else {
                                                        $new_product_per_date[$key_date] = array($qty_info[0]*$divider,$qty_info[1]);
                                                    }   
                                                }                           
                                            }
                                        }
                                    }
                                }
                            }
                            if(!empty($new_product_per_date)) {
                                $products_per_date = $new_product_per_date;
                            }
                        }
                    }
                }
                $cart_item_id = $product_id;
                $plan_display = $this->plan_delivery_details_display($cart_item_id);
                $flag= 'false';
                if(!empty($plan_display)) {
                    if(is_cart()) {
                        if(in_array("cart-page-view", $plan_display)) {
                            $flag = 'true';
                        } 
                    } else if(is_checkout()) {
                        if (in_array("checkout-page-view", $plan_display)) {
                            $flag = 'true';
                        }
                    } else if(function_exists('woocommerce_mini_cart')) {
                        if(in_array("cart-page-view", $plan_display)) {
                            $flag = 'true';
                        }
                    }           
                }

                $item_data = is_array($item_data) ? $item_data : array();
                if($flag == 'true') {            
                    if(($products_per_date != null) && is_array($products_per_date)) {
                        foreach($products_per_date as $key => $value) {

                            // hook added for change date format.
                            $date_format = apply_filters('thsdf_change_date_format', $format = '');
                            if(!empty($date_format)) {
                                $key = date($date_format, strtotime($key));
                            }

                            if(is_array($value)) {
                                if(!empty($value[1])) {
                                    $zeros_eliminated_value = ltrim($value[0], '0');
                                    $item_data[] = array("name" => $key.' ('.$value[1].')' , "value" => $zeros_eliminated_value);
                                } else {
                                    $zeros_eliminated_value = ltrim($value[0], '0');
                                    $item_data[] = array("name" => $key , "value" => $zeros_eliminated_value);
                                }
                            } else {
                                $zeros_eliminated_value = ltrim($value, '0');
                                $item_data[] = array("name" => $key , "value" => $zeros_eliminated_value);
                            }
                        }
                    }
                }
            }
            return $item_data;
        }

        /**
         * Core function for display date and time datas on thank you page.
         * 
         * @param integer $product_id The product id.
         *
         * @return array
         */
        public function plan_delivery_details_display($product_id) {
            $plan_display = '';
            $delivary_checkbox = get_post_meta($product_id, THSDF_Utils::POST_KEY_PLAN_DELIVERY_CHECKBOX, true);
            $global_setting = get_post_meta($product_id, THSDF_Utils::POST_KEY_PLAN_DELIVERY_GENERAL, true );
            $get_single_calender_settings = get_post_meta($product_id, THSDF_Utils::POST_KEY_SINGLE_SETTINGS, true );
            $get_single_display_settings = get_post_meta($product_id, THSDF_Utils::POST_KEY_SINGLE_DISPLAY_SETTINGS, true);
            $get_general_settings = get_option(THSDF_Utils::OPTION_KEY_DELIVERY_SETTINGS);
            $get_display_settings = get_option(THSDF_Utils::OPTION_KEY_DISPLAY_SETTINGS);

            if($delivary_checkbox == 'yes') {
                if($global_setting == 'yes') {
                    if(($get_general_settings != '') ||($get_display_settings != '')) {
                        $plan_display = isset($get_general_settings['plan_display']) ? $get_general_settings['plan_display'] : "";
                    }
                } else {
                    if(($get_single_calender_settings != '')||($get_single_display_settings != '')) {
                        $plan_display = isset($get_single_calender_settings['plan_display']) ? $get_single_calender_settings['plan_display'] : "";
                    }
                }
            }
            return $plan_display;
        }

        /**
         * Function for display delivery date datas to thank-you page(for latest version).
         * 
         * @param integer $item_id The item id
         * @param string $item The item datas
         * @param integer $order_id The order id
         *
         * @return void
         */
        public function new_order_item($item_id, $item, $order_id) {
            $product_delivery_info = array();
            $legacy_values = is_object($item) && isset($item->legacy_values) ? $item->legacy_values : false;    

            if(!empty($legacy_values)) {
                $ptdt_delivery_data = isset($legacy_values['ptdt_delivery_data']) ? $legacy_values['ptdt_delivery_data'] : '';          
                $quantity = isset($legacy_values['quantity']) ? $legacy_values['quantity'] : '';
                $new_ptdt_delivery_data = array();

                if(!empty($ptdt_delivery_data) && is_array($ptdt_delivery_data)) {
                    foreach ($ptdt_delivery_data as $key => $value) {               
                        $sd_total_quantity = isset($ptdt_delivery_data['total_quantity']) ? $ptdt_delivery_data['total_quantity'] : '';
                        if($quantity != $ptdt_delivery_data['total_quantity']) {
                            if($sd_total_quantity != 0) {
                                $divider = '';
                                if($sd_total_quantity < $quantity) {
                                    $divider = $quantity/$sd_total_quantity;
                                }
                                $sd_products_per_date = $ptdt_delivery_data;
                                $product_per_date = array();

                                if(isset($sd_products_per_date['products_per_date']) && is_array($sd_products_per_date['products_per_date'])) {
                                    foreach ($sd_products_per_date['products_per_date'] as $key_date => $qty_info) {
                                        if(!empty($divider)) {
                                            if(!is_array($qty_info)) {
                                                $product_per_date[$key_date] = $qty_info*$divider;  
                                            } else {
                                                $product_per_date[$key_date] = array($qty_info[0]*$divider,$qty_info[1]);   
                                            }
                                        }                               
                                    }
                                }
                                $nw_total_quantity = isset($sd_products_per_date['total_quantity']) ? $sd_products_per_date['total_quantity'] : '';
                                if(is_numeric ($divider) ) {
                                    $new_ptdt_delivery_data = array(
                                        'products_per_date' => $product_per_date,
                                        'total_quantity' => $nw_total_quantity*$divider
                                    );
                                }
                            }
                        }
                    }
                }
                if(!empty($new_ptdt_delivery_data)) {
                    $ptdt_delivery_data = $new_ptdt_delivery_data;
                }
                $product_delivery_info = $this->add_order_item_data($item_id, $ptdt_delivery_data);
            }            
            if(!empty($product_delivery_info)) {
                wc_update_order_item_meta($item_id, THSDF_Utils::ORDER_KEY_DELIVERY_DATE, $product_delivery_info);  
            }
        }

        /**
         * Display delivery date datas to thank-you page(for < version 3.0.0).
         *
         * @param integer $item_id The item id
         * @param array $values The item details
         * @param string $cart_item_key The cart item key
         *
         * @return void
         */
        public function add_order_item_meta($item_id, $values, $cart_item_key) {
            global $woocommerce, $wpdb;
            $product_delivery_info = array();
            if(!empty($values)) {
                $ptdt_delivery_data = isset($values['ptdt_delivery_data']) ? $values['ptdt_delivery_data'] : '';            
                $quantity = isset($values['quantity']) ? $values['quantity'] : '';
                $new_ptdt_delivery_data = array();

                if(!empty($ptdt_delivery_data) && is_array($ptdt_delivery_data)) {
                    foreach ($ptdt_delivery_data as $key => $value) {               
                        $sd_total_quantity = isset($ptdt_delivery_data['total_quantity']) ? $ptdt_delivery_data['total_quantity'] : '';
                        if($quantity != $ptdt_delivery_data['total_quantity']) {
                            if($sd_total_quantity != 0) {
                                $divider = $quantity/$sd_total_quantity;
                                $sd_products_per_date = $ptdt_delivery_data;
                                $product_per_date = array();
                                if(isset($sd_products_per_date['products_per_date']) && is_array($sd_products_per_date['products_per_date'])) {
                                    foreach ($sd_products_per_date['products_per_date'] as $key_date => $qty_info) {
                                        $product_per_date[$key_date] = array($qty_info[0]*$divider,$qty_info[1]);
                                    }
                                    $nw_total_quantity = isset($sd_products_per_date['total_quantity']) ? $sd_products_per_date['total_quantity'] : '';
                                    $new_ptdt_delivery_data = array(
                                        'products_per_date' => $product_per_date,
                                        'total_quantity' => $nw_total_quantity*$divider
                                    );
                                }
                            }
                        }
                    }
                }
                if(!empty($new_ptdt_delivery_data)) {
                    $ptdt_delivery_data = $new_ptdt_delivery_data;
                }
                $product_delivery_info = $this->add_order_item_data($item_id, $ptdt_delivery_data);
            }
            if(!empty($product_delivery_info)) {
                wc_update_order_item_meta($item_id, THSDF_Utils::ORDER_KEY_DELIVERY_DATE, $product_delivery_info);  
            }
        }
        /**
         * Function to update order item meta.
         * 
         * @param integer $item_id The item id
         * @param array $ptdt_delivery_data The product delivery data
         *
         * @return array
         */
        public function add_order_item_data($item_id,$ptdt_delivery_data) {
            $product_delivery_info = array();
            $product_delivery_date = isset($ptdt_delivery_data['products_per_date']) ? $ptdt_delivery_data['products_per_date'] : '';
            
            if(!empty($product_delivery_date) && is_array($product_delivery_date)) {
                foreach($product_delivery_date as $key => $value) {
                    $date_format = apply_filters('thsdf_change_date_format', $format = '');
                    if(!empty($date_format)) {
                        $key = date($date_format, strtotime($key));
                    }

                    if(!is_array($value)) {
                        $product_delivery_info[$key] = array(
                            'quantity' => $value,
                            'status'=> '1'
                        );
                    } else {
                        $product_delivery_info[$key] = array(
                            'quantity' => isset($value[0]) ? $value[0] : '',
                            'status'=> '1'
                        );
                    }
                }
            }
            return $product_delivery_info;
        }

        /**
         * Function for checkout validation.
         * 
         * @param array $data The item details
         * @param array $errors The existing validation errors
         *
         * @return array
         */
        public function checkout_validation($data, $errors) {
            $date_validation = array();
            if(!empty(WC()->cart->get_cart()) && is_array(WC()->cart->get_cart())){
                foreach(WC()->cart->get_cart() as $cart_item) {
                    if(isset($cart_item['ptdt_delivery_data']) && !empty($cart_item['ptdt_delivery_data'])) {
                        $ptdt_delivery_data = isset($cart_item['ptdt_delivery_data']) ? $cart_item['ptdt_delivery_data'] : array();

                        if(!empty($ptdt_delivery_data) && is_array($ptdt_delivery_data)) {
                            $product_delivery_date = isset($ptdt_delivery_data['products_per_date']) ? $ptdt_delivery_data['products_per_date'] : '';

                            if(!empty($product_delivery_date) && is_array($product_delivery_date)) {
                                foreach($product_delivery_date as $key => $value) {
                                    $validate_date = $this->validate_order_date($key);
                                    if($validate_date == false) {
                                        $date_validation[] = 'false';
                                    }
                                }
                                
                            }
                        }
                    }
                }
            }
            if(!empty($date_validation)) {
                if(in_array ('false', $date_validation)) {
                    $errors->add('validation', 'The selected date has expired. Please pick a different date.' );
                }
            }
            return $errors;
        }

        /**
         * Core function for the checkout date validation.
         * 
         * @param array $date The selected date.
         *
         * @return string
         */
        public function validate_order_date($date) {
            $passed = true;
            $now = new DateTime();
            $current_date = $now->format('Y-m-d');
            $date_format = apply_filters('thsdf_change_date_format', $format = '');
            if(!empty($date_format)) {
               // $current_date = date($date_format, strtotime($current_date));
                $current_date = date('Y-m-d', strtotime($current_date));
                $date = date('Y-m-d', strtotime($date));
            } else {
                $current_date = $current_date;
            }
            if($date >= $current_date) {
                $passed = true;
            } else {
                $passed = false;
            }
            return $passed;
        }
        /**
         * Get cart item quentity from session table.
         * 
         * @param string $product_quantity The product quantity
         * @param string $cart_item_key The cart item key
         * @param array $cart_item The cart item datas
         *
         * @return integer
         */
        public function cart_item_quantity($product_quantity, $cart_item_key, $cart_item) {  
            if(!empty($cart_item) && is_array($cart_item)) {
                foreach($cart_item as $item => $key) {
                    if($item == 'ptdt_delivery_data') {
                        $ptdt_delivery_data = isset($cart_item['ptdt_delivery_data']) ? $cart_item['ptdt_delivery_data'] : '';
                        $variation_id = isset($cart_item['variation_id']) ? $cart_item['variation_id'] : '';
                        $product_quantity = isset($cart_item['quantity']) ? $cart_item['quantity'] : '';
                        $product_id = isset($cart_item['product_id']) ? $cart_item['product_id'] : '';
                    }
                }
            }
            return $product_quantity; 
        }

        /**
         * Function for display text on the shop page.
         * 
         * @param string $text The existing text
         * @param array $product The product details
         *
         * @return string
         */
        public function add_to_cart_text($text, $product) {
            $product_id = $product->get_id();
            $product_type = $product->get_type();
            $delivary_checkbox = get_post_meta($product_id, THSDF_Utils::POST_KEY_PLAN_DELIVERY_CHECKBOX,true);
            if($product_type == 'simple') {
                if($delivary_checkbox == 'yes') {
                    $text = $product->is_purchasable() ? esc_html__('Select options', 'schedule-delivery-for-woocommerce-products') : esc_html__('Read more', 'schedule-delivery-for-woocommerce-products');
                }
            }
            return $text;
        }
        /**
         * Hook added for set default cart link datas
         * 
         * @param string $link The link on shop page
         * @param string $product The product details
         * @param string $args The argument passed on shop page link
         *
         * @return string
         */  
        public function loop_add_to_cart_link($link, $product, $args=array()) {
            if(apply_filters('return_thsdf_cart_link_datas', true)) {
                $product_id = $product->get_id();
                $product_sku = $product->get_sku();
                $product_type = $product->get_type();
                $delivary_checkbox = get_post_meta($product_id, THSDF_Utils::POST_KEY_PLAN_DELIVERY_CHECKBOX,true);
                if($product_type == 'simple') {
                    if($delivary_checkbox == 'yes') {
                        if(isset($args['class'])) {
                            $args['class'] = str_replace("ajax_add_to_cart", "", $args['class']);
                        }
                        $link = sprintf('<a rel="nofollow" href="%s" data-quantity="%s" data-product_id="%s" data-product_sku="%s" class="%s">%s</a>',
                            esc_url($product->get_permalink()),
                            esc_attr(isset($args['quantity']) ? $args['quantity'] : 1),
                            esc_attr($product_id),
                            esc_attr($product_sku),
                            esc_attr(isset($args['class']) ? $args['class'] : 'button'),
                            esc_html($product->add_to_cart_text())
                        );
                    }
                }
            }
            return $link;
        }
        /**
         * Function for locate template
         * 
         * @param array $template The template datas
         * @param string $template_name The template name
         * @param string $template_path The template path link
         *
         * @return string
         */  
        function woocommerce_locate_template($template, $template_name, $template_path) {
            global $woocommerce;
            $_template = $template;
            if (! $template_path) $template_path = $woocommerce->template_url;
            $plugin_path  = THSDF_PATH. 'woocommerce/';
            $template = locate_template(
                array(
                    $plugin_path . $template_name,
                    $template_name
                )
            );
            if (! $template && file_exists($plugin_path . $template_name))
              $template = $plugin_path . $template_name;
            if (! $template)
              $template = $_template;

            return $template;
        }
        /**
         * Function order again.
         * 
         * @param array $cart_item_data The cart item data
         * @param array $item The item details
         * @param array $order The order details
         *
         * @return array
         */ 
        public function filter_order_again_cart_item_data($cart_item_data, $item, $order) {
            $ptdt_delivery_date = $this->prepare_order_again_extra_cart_item_data($item, $order);   
            if($ptdt_delivery_date) {
                $cart_item_data['ptdt_delivery_data'] = $ptdt_delivery_date;
            }
            return $cart_item_data;
        }

        /**
         * Function for prepare order again functionality.
         * 
         * @param array $item The item details
         * @param array $order The order details
         *
         * @return array
         */ 
        private function prepare_order_again_extra_cart_item_data($item, $order) {
            $item_id = $item->get_id();
            $meta_data = $item->get_meta_data();
            $ptdt_delivery_date = wc_get_order_item_meta($item_id, THSDF_Utils::ORDER_KEY_DELIVERY_DATE, 'true');
            $thsdf_data = array();

            if(!empty($meta_data) && is_array($meta_data)) {
                foreach($meta_data as $key => $meta) {
                    $thsdf_data[] = $meta->value;
                }
            }
            $quantity_data = array(); 
            if(!empty($thsdf_data) && is_array($thsdf_data)) {
                foreach ($thsdf_data as $sd_key => $sd_value) {
                    if(is_array($sd_value)) {
                        $thsdf_order_data = $sd_value;  
                        $products_per_date = array();

                        if(!empty($thsdf_order_data) && is_array($thsdf_order_data)) {
                            foreach($thsdf_order_data as $sd_odr_key => $sd_odr_value) {    
                                $format = 'Y-m-d';
                                $d = DateTime::createFromFormat($format, $sd_odr_key);
                                $checkdate = $d && $d->format($format) === $sd_odr_key;
                                
                                if($checkdate == true) {
                                        $quantity_data[$sd_odr_key] = isset($sd_odr_value['quantity']) ? $sd_odr_value['quantity'] : '';
                                }                      
                            }
                        }
                    }           
                }
            }

            $total_qty = array_sum($quantity_data);
            $delivery_info_per_date['products_per_date'] = $quantity_data;
            $delivery_info_per_date['total_quantity'] = $total_qty;
            return $delivery_info_per_date;
           
        }
        /**
         * Function for item data add to cart table.
         *
         * @param array $cart_item_data The cart item data
         * @param integer $product_id The current product id
         * @param integer $variation_id The variation id of the current product
         * @param integer $quantity The qunatity of the current product
         *
         * @return array.
         */
        function add_cart_item_data($cart_item_data, $product_id, $variation_id, $quantity) {
            global $woocommerce;
            if($variation_id){
                $variation_id = $variation_id;
            } else {
                $variation_id = 0;
            }
            $delivery_cart_data = $this->prepare_delivery_cart_item_data($product_id,$variation_id);
            if($delivery_cart_data) {          
                // if(apply_filters('ptdt_set_unique_key_for_cart_item', false, $cart_item_data, $product_id, $variation_id)) {
                //     $cart_item_data['unique_key'] = md5(microtime().rand());
                // }
                $cart_item_data['ptdt_delivery_data'] = $delivery_cart_data;
            }
            return $cart_item_data;
        }
         /**
         * Function for Prepare delivery cart item data.
         * 
         * @param integer $product_id The cart data
         * @param integer $variation_id The cart data
         *
         * @return array.
         */
        private function prepare_delivery_cart_item_data($product_id, $variation_id) {
            global $woocommerce;
            $cart_data = $woocommerce->cart->get_cart();
            $calendar_data = isset($_POST['calendar_data']) ? wc_clean($_POST['calendar_data']) : ''; 
            $decoded_calendar_data = '';        
            if(!empty($calendar_data)) {     
                $decoded_calendar_data = json_decode(stripslashes($calendar_data), true);            
            }   
            return $decoded_calendar_data;
        }
        /**
         * Function for display date and time datas on thank you page.
         * 
         * @param array $date The selected date.
         * 
         *
         * @return array
         */
        public function order_item_get_formatted_meta_data($formatted_meta, $order_item) {
            if (is_order_received_page() || get_post_type() != 'shop_order' && !is_account_page()) {
                if(method_exists($order_item,'get_product_id')) {
                    $order_item_id = $order_item->get_id();
                    $item_id = $order_item_id;
                    $ptdt_delivery_date = wc_get_order_item_meta($item_id, THSDF_Utils::ORDER_KEY_DELIVERY_DATE, 'true');
                    $product_id = $order_item->get_product_id();                   
                    $plan_display = $this->plan_delivery_details_display($product_id);
                    $flag= 'false';

                    if(!empty($plan_display)) {
                        if (in_array("thankyou-page-view", $plan_display)) {
                            $flag= 'true';
                        }
                    }
                    if($flag == 'true') {                       
                        if(!empty($ptdt_delivery_date) && is_array($ptdt_delivery_date)) {
                            foreach($ptdt_delivery_date as $date => $delivery_data) {
                                $qty_value = array_key_exists('quantity', $delivery_data) ? $delivery_data['quantity'] : '';
                                $zeros_eliminated_value = ltrim($qty_value, '0');
                                $qty_value = $zeros_eliminated_value;
                                $product = is_callable(array($this, 'get_product')) ? $this->get_product() : false;

                                
                                $display_key   = wc_attribute_label($date, $product);
                                
                                $display_value = wp_kses_post($qty_value);
                                $formatted_meta[$date] = (object) array(
                                    'key'           => $date,
                                    'value'         => $qty_value,
                                    'display_key'   => esc_html__($display_key,'schedule-delivery-for-woocommerce-products'),
                                    'display_value' => $display_value,
                                );
                            }
                        }
                    }
                }
            }
            return $formatted_meta;
        }

        /**
         * Function for display date and time datas on my account page.
         * 
         * @param array $date The selected date.
         * 
         *
         * @return array
         */
        public function order_item_visible_data($formatted_meta, $order_item) {
            if (is_account_page() || get_post_type() != 'shop_order' && !is_order_received_page()) {
                if(method_exists($order_item,'get_product_id')) {
                    $order_item_id = $order_item->get_id();
                    $item_id = $order_item_id;
                    $ptdt_delivery_date = wc_get_order_item_meta($item_id, THSDF_Utils::ORDER_KEY_DELIVERY_DATE, 'true');
                    $product_id = $order_item->get_product_id();                   
                                      
                    if(!empty($ptdt_delivery_date) && is_array($ptdt_delivery_date)) {
                        foreach($ptdt_delivery_date as $date => $delivery_data) {
                            $qty_value = array_key_exists('quantity', $delivery_data) ? $delivery_data['quantity'] : '';
                            $zeros_eliminated_value = ltrim($qty_value, '0');
                            $qty_value = $zeros_eliminated_value;
                            $product = is_callable(array($this, 'get_product')) ? $this->get_product() : false;                            
                            $display_key   = wc_attribute_label($date, $product);
                            
                            $display_value = wp_kses_post($qty_value);
                            $formatted_meta[$date] = (object) array(
                                'key'           => $date,
                                'value'         => $qty_value,
                                'display_key'   => esc_html__($display_key,'schedule-delivery-for-woocommerce-products'),
                                'display_value' => wpautop( make_clickable($display_value) ),
                            );
                        }
                    }
                 }   
            }
            return $formatted_meta;
        }
	}

endif;