<?php
/**
 * The admin display settings page functionality of the plugin.
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

if(!class_exists('THSDF_Admin_Settings_Display')) :

	/**
     * The admin display settings class extends from admin settings class.
     */
	class THSDF_Admin_Settings_Display extends THSDF_Admin_Settings {
		protected static $_instance = null;
		private $settings_fields = NULL;
		private $cell_props = array();

		/**
         * Constructor.
         */
		public function __construct() {
			parent::__construct('display_settings');
			$this->cell_props = array(
				'label_cell_props' => 'class="th_display"', 
				'input_cell_props' => 'style="width: 25%;" class="forminp"', 
				'input_width' => '250px', 'label_cell_th' => true 
			);
			$this->init_constants();
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
         * Function for render page.
         */
		public function render_page() {
			$this->render_tabs();
			$this->render_content();
		}
		/**
         * Function for initialise constants.
         */
		public function init_constants() {
			$this->settings_fields = self::get_field_form_props();
		}

		/**
         * Function for get display settings fields.
         */
		public static function get_field_form_props() {

			return array(
				'calender_colors'	=> array('value'=>'Calender Colors', 'type'=>'separator', 'class' => 'ptdt-display-set-calendar'),
				'calendar_border' 	=> array('type'=>'colorpicker', 'name'=>'calendar_border', 'label'=>esc_html__('Calendar Border','schedule-delivery-for-woocommerce-products'), 'value'=>'#bdb7b7'),
				'day_bg'	  		=> array('type'=>'colorpicker', 'name'=>'day_bg', 'label'=>esc_html__('Day Background','schedule-delivery-for-woocommerce-products'), 'value'=>'#e3f0f8'),
				'nav_arrow'	  		=> array('type'=>'colorpicker', 'name'=>'nav_arrow', 'label'=>esc_html__('Navigation Arrows','schedule-delivery-for-woocommerce-products'), 'value'=>'#a91549'),
				'tooltip_bg'	  	=> array('type'=>'colorpicker', 'name'=>'tooltip_bg', 'label'=>esc_html__('Tooltip Background','schedule-delivery-for-woocommerce-products'), 'value'=>'#ffffff'),
				'text_colors' 		=> array('value'=>esc_html__('Text Colors','schedule-delivery-for-woocommerce-products'), 'type'=>'separator', 'class' => esc_attr('ptdt-display-set-text')),
				'date_color' 		=> array('type'=>'colorpicker', 'name'=>'date_color', 'label'=>esc_html__('Date', 'schedule-delivery-for-woocommerce-products'), 'value'=>'#a91549' ),
				'day_title_color' 	=> array('type'=>'colorpicker', 'name'=>'day_title_color', 'label'=>esc_html__('Day Title', 'schedule-delivery-for-woocommerce-products'), 'value'=>'#6d6d6d' ),
				'day_color' 		=> array('type'=>'colorpicker', 'name'=>'day_color', 'label'=>esc_html__('Day', 'schedule-delivery-for-woocommerce-products'), 'value'=>'#3f415c'),
				'holiday_color'		=> array('type'=>'colorpicker', 'name'=>'holiday_color', 'label'=>esc_html__('Holidays', 'schedule-delivery-for-woocommerce-products'), 'value'=>'#a91549'),
				'tooltip_text'		=> array('type'=>'colorpicker', 'name'=>'tooltip_text', 'label'=>esc_html__('Tooltip Text', 'schedule-delivery-for-woocommerce-products'), 'value'=>'#444444'),
				'input_text'		=> array('type'=>'colorpicker', 'name'=>'input_text', 'label'=>esc_html__('Input Text', 'schedule-delivery-for-woocommerce-products'), 'value'=>'#4a78bc'),
			);
		}

		/**
         * Function for render content.
         */
		public function render_content() {
			if(isset($_POST['save_display_settings'])) {
				$this->save_display_settings();
			}
			if(isset($_POST['default_display_settings'])){
				$this->default_display_settings();
			}
			$fields = $this->get_field_form_props();
			$calendar_border = isset($fields['calendar_border']) ? $fields['calendar_border'] : '';
			$day_bg = isset($fields['day_bg']) ? $fields['day_bg'] : '';
			$nav_arrow = isset($fields['nav_arrow']) ? $fields['nav_arrow'] : '';
			$tooltip_bg = isset($fields['tooltip_bg']) ? $fields['tooltip_bg'] : '';
			$date_color = isset($fields['date_color']) ? $fields['date_color'] : '';
			$day_title_color = isset($fields['day_title_color']) ? $fields['day_title_color'] : '';
			$day_color = isset($fields['day_color']) ? $fields['day_color'] : '';
			$holiday_color = isset($fields['holiday_color']) ? $fields['holiday_color'] : '';
			$tooltip_text = isset($fields['tooltip_text']) ? $fields['tooltip_text'] : '';
			$input_text = isset($fields['input_text']) ? $fields['input_text'] : '';

			$section = 'display_settings';
			$calendar_border = THSDF_Utils::set_values_props($calendar_border,'calendar_border', $section);
			$day_bg = THSDF_Utils::set_values_props($day_bg,'day_bg', $section);
			$nav_arrow = THSDF_Utils::set_values_props($nav_arrow,'nav_arrow', $section);
			$tooltip_bg = THSDF_Utils::set_values_props($tooltip_bg,'tooltip_bg', $section);
			$date_color = THSDF_Utils::set_values_props($date_color,'date_color', $section);
			$day_title_color = THSDF_Utils::set_values_props($day_title_color,'day_title_color', $section);
			$day_color = THSDF_Utils::set_values_props($day_color,'day_color', $section);
			$holiday_color = THSDF_Utils::set_values_props($holiday_color,'holiday_color', $section);
			$tooltip_text = THSDF_Utils::set_values_props($tooltip_text,'tooltip_text', $section);
			$input_text = THSDF_Utils::set_values_props($input_text,'input_text', $section);
            ?>
            <div class="wrap woocommerce">
				<form action="" method="post" name="thsdf_display_settings_form" class="ptdt-display-settings-form">
					<?php if (function_exists('wp_nonce_field')) {
                        wp_nonce_field('display_settings_form', 'display-settings-form'); 
                    } ?>
						<?php $this->render_form_section_separator($fields['calender_colors']); ?>
					<table class="form-table ptdt-display-admin-form-table">
						<?php $this->render_display_settings_elm_row($calendar_border); ?>
						<?php $this->render_display_settings_elm_row($day_bg); ?>
						<?php $this->render_display_settings_elm_row($nav_arrow); ?>
						<?php $this->render_display_settings_elm_row($tooltip_bg); ?>
					</table>
					<?php $this->render_form_section_separator($fields['text_colors']); ?>
					<table class="form-table ptdt-display-admin-form-table">
						<?php $this->render_display_settings_elm_row($date_color); ?>
						<?php $this->render_display_settings_elm_row($day_title_color); ?>
						<?php $this->render_display_settings_elm_row($day_color); ?>
						<?php $this->render_display_settings_elm_row($holiday_color); ?>
						<?php $this->render_display_settings_elm_row($tooltip_text); ?>
						<?php $this->render_display_settings_elm_row($input_text); ?>
						<tr class="general-settings-row">
							<th class="submit">
								<input type="submit" name="save_display_settings" class="button-primary ptdt-general-display-settings-save" value="<?php esc_html_e('Save changes', 'schedule-delivery-for-woocommerce-products'); ?>">
								<input type="submit" name="default_display_settings" class="button default-display-settings" value="<?php esc_html_e('Reset to Default', 'schedule-delivery-for-woocommerce-products'); ?>" onclick="return confirm('Are you sure you want to reset to default settings? all your changes will be deleted.');">
				            </th>
						</tr>
					</table>
				</form>
			</div>
		<?php
		}

		/**
         * Function for save display settings.
         *
         * @return void
         */
		function save_display_settings() {
			if(isset($_POST['display-settings-form']) && $_POST['display-settings-form']){
				$nonce = $_POST['display-settings-form'];
				$capability = THSDF_Utils::thsdf_capability();
				if(!wp_verify_nonce($nonce, 'display_settings_form')|| !current_user_can($capability)){
					die('You are not authorized to perform this action.');
				} else {
					$display_settings = array();
					if(!empty($this->settings_fields) && is_array($this->settings_fields)) {
						foreach($this->settings_fields as $name => $field) {
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
						$result = update_option(THSDF_Utils::OPTION_KEY_DISPLAY_SETTINGS, $settings);
					}
					if($result == true) {
						parent::print_notices('Your changes were saved','updated',false);
					} else {
						parent::print_notices('Your changes were not saved due to an error (or you made none!)','error',false);
					}
				}
			}
		}

		/**
         * Function for update default settings and set reset message.
         *
         */
		function default_display_settings() {
			if(isset($_POST['display-settings-form']) && $_POST['display-settings-form']){
				$nonce = $_POST['display-settings-form'];
				$capability = THSDF_Utils::thsdf_capability();
				if(!wp_verify_nonce($nonce, 'display_settings_form')|| !current_user_can($capability)){
					die('You are not authorized to perform this action.');

				} else {
					$settings = '';

					// Update option data of general settings.
					parent::print_notices('Settings successfully reset.','updated',false);
					$result = delete_option(THSDF_Utils::OPTION_KEY_DISPLAY_SETTINGS, $settings);
				}
			}
		}

        /**
        * Set element row
        *
        */
        public function render_display_settings_elm_row($field){
        	?>
        	<tr>
				<?php $this->render_form_field_element($field, $this->cell_props); ?>
			</tr><?php
        }		
	}
endif;