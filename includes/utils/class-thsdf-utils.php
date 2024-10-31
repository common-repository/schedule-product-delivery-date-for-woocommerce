<?php
/**
 * The common utility functionalities for the plugin.
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    schedule-delivery-for-woocommerce-products
 * @subpackage schedule-delivery-for-woocommerce-products/includes-free/utils
 */
if(!defined('WPINC')) {	
	die; 
}

if(!class_exists('THSDF_Utils')) :

	/**
     * The Utils class.
     */
	class THSDF_Utils {
		const OPTION_KEY_DISPLAY_SETTINGS         = '_thptdelivery_display_settings';
		const OPTION_KEY_DELIVERY_SETTINGS        = '_thptdelivery_general_settings';
		const POST_KEY_PLAN_DELIVERY_CHECKBOX     = '_plan_delivery_set';
		const POST_KEY_PLAN_DELIVERY_GENERAL      = '_plan_delivery_global_set';
		const POST_KEY_SINGLE_SETTINGS            = '_thptd_single_calendar_settings';
		const POST_KEY_SINGLE_DISPLAY_SETTINGS    = '_thptd_single_display_settings';
		const ORDER_KEY_DELIVERY_DATE             = '_ptdt_delivery_date';
		const ORDER_KEY_USER_DELETE               = '_ptdt_user_deleted_orders';
		const POST_DELIVERY_DATE                  = '_thptd_delivery_date';
		const ORDER_DELETED_ITEM                  = '_order_deleted_items';
		
		/**
         * Function for get settings value.
         *
         * @param string $key The setting key
         * @param string $function_tag The funciton tag
         * @param intiger $product_id The product id
         * @param array $settings The setting datas
         *
         * @return string
         */
        public static function get_setting_value($key, $function_tag, $product_id='', $settings=false) {
            if(!$settings && !empty($function_tag)) {
            	$function_name = 'get_'.$function_tag;
                $settings = self::$function_name($product_id); 
            }
            if(is_array($settings) && isset($settings[$key])) {
                return $settings[$key];
            }
            return '';
        }

        /**
         * The general settings function.
         *
         * @param intiger $product_id The product id
         *
         * @return array
         */
        public static function get_general_settings($product_id) {
            $default_general_settings = self::default_general_settings();
            $settings = get_option(self::OPTION_KEY_DELIVERY_SETTINGS);
            return empty($settings) ? $default_general_settings : $settings;
        }

        /**
         * Function for get the products calendar settings data.
         *
         * @param intiger $product_id The product id
         *
         * @return array
         */
        public static function get_product_general_settings($product_id) {
            $default_general_settings = self::default_general_settings();
            $save_calender_settings = get_post_meta($product_id, self::POST_KEY_SINGLE_SETTINGS, true);
            $save_general_settings = get_option(self::OPTION_KEY_DELIVERY_SETTINGS);
            if(!empty($save_calender_settings)) {
                $settings = $save_calender_settings;
            } else {
                if(!empty($save_general_settings)) {
                    $settings = $save_general_settings;
                }
            }
            return empty($settings) ? $default_general_settings : $settings;
        }

        /**
         * Function for get the products display settings data.
         *
         * @param intiger $product_id The product id
         *
         * @return array
         */
        public static function get_product_display_settings($product_id) {
            $default_display_settings = self::default_display_settings();
            $temp = array(
                'product_id'  => ''
            );
            $saved_display_settings = get_post_meta($product_id, THSDF_Utils::POST_KEY_SINGLE_DISPLAY_SETTINGS, true);
            $saved_settings = get_option(THSDF_Utils::OPTION_KEY_DISPLAY_SETTINGS);
            $general_setting = array();
            if(!empty($saved_settings)) {
                $product_data = array(
                    'product_id'  => ''
                );
                $general_setting = array_merge($product_data, $saved_settings);
            }
            if(!empty($saved_display_settings)) {
                $settings = !empty($saved_display_settings) ? $saved_display_settings : $default_display_settings ;
            } else {
                $settings = !empty($general_setting) ? $general_setting : $default_display_settings ;
            }
            return $settings;

        }

        /**
         * Function for get default display settings.
         *
         * @param intiger $product_id The product id
         *
         * @return array
         */
		public static function get_display_settings($product_id) {		
			$default_display_settings = self::default_display_settings();
			$saved_settings = get_option(THSDF_Utils::OPTION_KEY_DISPLAY_SETTINGS);				
			$settings = !empty($saved_settings) ? $saved_settings : $default_display_settings ;
			return $settings;
		}

        /**
         * Function for get default general settings.
         *
         * @return array
         */
        public static function default_general_settings(){
            return array(
                'week_start'    => array(
                                '0' => 'sunday'
                                ),
                'delivery_day'  => array(
                                '0'=>'7'
                                ),
                'start_date'    => '',
                'end_date'      => '',
                'holiday'       => array(),
                'plan_display'  => array(
                                    '0'=>'4'
                                )                
            );
        }

        /**
         * Function for default display settings info.
         *
         * @return array
         */
        public static function default_display_settings(){
            return array(
                'calendar_border'   => '#bdb7b7',
                'day_bg'            => '#e3f0f8',
                'nav_arrow'         => '#a91549',
                'tooltip_bg'        => '#ffffff',
                'date_color'        => '#a91549',
                'day_title_color'   => '#6d6d6d',
                'day_color'         => '#3f415c',
                'holiday_color'     => '#a91549',
                'tooltip_text'      => '#444444',
                'input_text'        => '#4a78bc',
            );
        }

		/**
         * The write_log function.
         *
         * @param string $log the log string
         *
         * @return string
         */
		public static function write_log ($log)  {
			if (true === WP_DEBUG) {
				if (is_array($log) || is_object($log)) {
					error_log(print_r($log, true));
				} else {
					error_log($log);
				}
			}
		}

        /**
         * Check the quick view plugin active.
         *
         * @return void
         */
        public static function is_quick_view_plugin_active() {
            $quick_view = false;
            if(self::is_flatsome_quick_view_enabled()) {
                $quick_view = 'flatsome';
            }else if(self::is_yith_quick_view_enabled()) {
                $quick_view = 'yith';
            }else if(self::is_astra_quick_view_enabled()) {
                $quick_view = 'astra';
            }
            return apply_filters('thsdf_is_quick_view_plugin_active', $quick_view);
        }
        
        /**
         * Check the theme yith quick view enabled.
         */
        public static function is_yith_quick_view_enabled() {
            return is_plugin_active('yith-woocommerce-quick-view/init.php');
        }
        
        /**
         * Check the theme flatsome quick view enabled.
         */
        public static function is_flatsome_quick_view_enabled() {
            return (get_option('template') === 'flatsome');
        }

        /**
         * Check the theme astra quick view enabled.
         */
        public static function is_astra_quick_view_enabled() {
            return is_plugin_active('astra-addon/astra-addon.php');
        }

        /**
         * Check the woocommerce version.
         *
         * @param string $version the woocommerce version
         *
         * @return string
         */
        public static function woo_version_check($version = '3.0') {
            if(function_exists('is_woocommerce_active') && is_woocommerce_active()) {
                global $woocommerce;
                if(version_compare($woocommerce->version, $version, ">=")) {
                    return true;
                }
            }
            return false;
        }

        /**
         * Function for check the current actived theme.
         *
         * @return string
         */
        public static function check_current_theme() {
            $current_theme = wp_get_theme();
            $current_theme_name = isset($current_theme['Template']) ? $current_theme['Template'] : '';
            $wrapper_class = '';
            $theme_class_name = '';
            if($current_theme_name) {
                $wrapper_class = str_replace(' ', '-', strtolower($current_theme_name));
            }
            return $wrapper_class;
        }

        /**
         * Function to check cfe plugin is active
         *
         * @return int
         */ 
        public static function is_wepo_plugin_active(){
            $flag = false;
            if (is_plugin_active('woocommerce-extra-product-options-pro/woocommerce-extra-product-options-pro.php')) {
                $flag = true;
            }
            return $flag;
        }

        /**
         * Set values props.
         *
         * @param array $settings_props
         * @param string $type
         *
         * @return array
         */
        public static function set_values_props($settings_props, $field_name, $section, $product_id=false) {
            if(!empty($settings_props) && is_array($settings_props)) {
                if($product_id){
                    $settings_props['value'] = self::get_setting_value($field_name, $section, $product_id);
                } else {
                    $settings_props['value'] = self::get_setting_value($field_name, $section);
                }

            }
            return $settings_props;
        }

        /**
        * Function for set capability
        */
        public static function thsdf_capability() {
            $allowed = array('manage_woocommerce','manage_options');
            $capability = apply_filters('thwsd_required_capability', 'manage_woocommerce');
            if(!in_array($capability, $allowed)){
                $capability = 'manage_woocommerce';
            }
            return $capability;
        }

        /**
         * Function for get allowed post tags.
         *
         * @return array
         */
        public static function allowed_html_tags(){
            global $allowedposttags;
            $html_tags = array (
                'input' => array(
                    'data-qty'  => true,
                    'data-date' => true,
                    'disabled'  => true,
                    'value'     => true,
                    'checked'   => 'checked',
                    'type'      => array(),
                    'name'      => array(),
                    'value'     => array(),
                    'class'     => array(),
                    'id'        => array(),
                    'placeholder'   =>true,
                    'autocomplete'  => true,
                    'readonly'      => true,
                    'data-daynum'   =>true,
                    'min'           => true,

                ),
                'bdi'   => array(),
                'a'     => array(               
                    'href'     => array(),
                    'rel'      => true,
                    'rev'      => true,
                    'name'     => true,
                    'target'   => true,                 
                    'data-itemid'   => true,
                    'data-itemdate' => true,
                    'data-itemqty'  => true,
                    'data-price'    => true,
                    'title'         => true,
                    'onclick'       => true,
                    'class'         => array(),
                    'id'            => array(),
                    'download'      => array(
                        'valueless' => 'y',
                    ),
                ),
                'div'   => array(
                    'align'     => true,
                    'dir'       => true,
                    'lang'      => true,
                    'xml:lang'  => true,
                    'style'     => true,
                    'id'        => array(),
                    'class'     => array(),
                ),
                'h3'    => array(
                    'align' => true,
                    'class' => array(),
                ),
                'img'   => array(
                    'alt'      => true,
                    'align'    => true,
                    'border'   => true,
                    'height'   => true,
                    'hspace'   => true,
                    'loading'  => true,
                    'longdesc' => true,
                    'vspace'   => true,
                    'src'      => true,
                    'usemap'   => true,
                    'width'    => true,
                    'class' => array(),
                ),
                'label' => array(
                    'for'   => true,
                    'class' => true,
                    'id'    => true,
                ),
                'span'  => array(
                    'dir'           => true,
                    'align'         => true,
                    'lang'          => true,
                    'xml:lang'      => true,
                    'aria-hidden'   => true,
                    'data-item_id'  => true,
                    'data-item_date'=> true,
                    'data-item_price' => true,
                    'data-item_qty' => true,
                    'class'         => array(),
                ),
                'td'    => array(
                    'abbr'    => true,
                    'align'   => true,
                    'axis'    => true,
                    'bgcolor' => true,
                    'char'    => true,
                    'charoff' => true,
                    'colspan' => true,
                    'dir'     => true,
                    'headers' => true,
                    'height'  => true,
                    'nowrap'  => true,
                    'rowspan' => true,
                    'scope'   => true,
                    'valign'  => true,
                    'width'   => true,
                    'class'   => array(),
                    'id'      => array(),
                    'style'   => array(),
                    'rel'     =>true,
                    'title'   => true,
                ),
                'th'    => array(
                    'abbr'    => true,
                    'align'   => true,
                    'axis'    => true,
                    'bgcolor' => true,
                    'char'    => true,
                    'charoff' => true,
                    'colspan' => true,
                    'headers' => true,
                    'height'  => true,
                    'nowrap'  => true,
                    'rowspan' => true,
                    'scope'   => true,
                    'valign'  => true,
                    'width'   => true,
                    'class'     =>  array(),
                    'id'        => array(),
                ),
                'select' => array(
                    'name'      => array(),
                    'value'     => array(),
                    'class'     => array(),
                ),
                'option' => array(
                    'value'     => array(),
                    'selected'  => array(),
                ),
            );

            $allowed_html_tags = array_merge($allowedposttags, $html_tags);
            return $allowed_html_tags;          
        }
	}
	
endif;