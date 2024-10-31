<?php
/**
 * The admin general settings page functionality of the plugin.
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

if(!class_exists('THSDF_Admin_Settings_General')) :

	/**
     * The admin general settings class extends from admin settings class.
     */
	class THSDF_Admin_Settings_General extends THSDF_Admin_Settings {
		protected static $_instance = null;
		private $settings_fields = NULL;
		private $cell_props;


		/**
         * Constructor.
         */
		public function __construct() {
			parent::__construct('general_settings', '');
			$this->cell_props = array(
				'label_cell_props' => '', 
				'input_cell_props' => 'class=ptdt_general_settings_td', 
				 'label_cell_th' => true 
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
         * Function for initialise instance.
         */
		public function init_constants() {
			$this->settings_fields = $this->get_fields();
		}

				/**
         * Function for get general settings fields.
         */
		public function get_fields() {
			$week_start = $this->get_week_start();
			$delivery_day = $this->get_delivery_day();
			$plan_display = $this->get_plan_display();

			return array(
				'calender_settings'        => array('type'=>'separator', 'value'=>esc_html__('Calendar Settings','schedule-delivery-for-woocommerce-products'),  'class' => esc_attr('general-settings-header')),
				'week_start'		       => array('type'=>'select', 'name'=>'week_start', 'label'=>esc_html__('Start the Week on','schedule-delivery-for-woocommerce-products'), 'options'=>$week_start, 'class' => esc_attr('week_start')),
				'delivery_day'  		   => array('type'=>'multicheckbox', 'name'=>'delivery_day[]', 'label'=>esc_html__('Enable Plan Delivery for Weekdays','schedule-delivery-for-woocommerce-products'), 'check'=>$delivery_day,'required' =>1, 'id' => 'delivery_day','required_tag' => esc_html__('Weekdays are required', 'schedule-delivery-for-woocommerce-products'), 'required_class' => esc_attr('thsdf-weekday-required-mssg'),'label_class' => esc_attr('delivery-day-label')),
				'start_date'   			   => array('type'=>'text', 'name'=>'start_date', 'label'=>esc_html__('Start Date','schedule-delivery-for-woocommerce-products'), 'required'=>1,'id'=>'ptdt_datepicker_start','placeholder' => 'yyyy-mm-dd', 'class' => esc_attr('general-set-date general-start-date'), 'required_class' => esc_attr('thsdf-startdate-required-mssg'), 'required_tag' => esc_html__('Start Date field is required', 'schedule-delivery-for-woocommerce-products')),
				'end_date' 				   => array('type'=>'text', 'name'=>'end_date', 'label'=>esc_html__('End Date','schedule-delivery-for-woocommerce-products'), 'required'=>1,'id'=>'ptdt_datepicker_end','placeholder' => 'yyyy-mm-dd', 'class' => esc_attr('general-set-date'),'required_class' => esc_attr('thsdf-required-mssg'), 'required_tag' => esc_html__('End Date field is required', 'schedule-delivery-for-woocommerce-products')),
				'holidays'   			   => array('type'=>'multidatepicker', 'name'=>'holidays[]', 'label'=>esc_html__('Holiday / Unavailable days','schedule-delivery-for-woocommerce-products'), 'placeholder' => 'yyyy-mm-dd', 'class' => esc_attr('general-set-date_holiday g-holidays')),

				'plan_display'  		   => array('type'=>'multicheckbox', 'name'=>'plan_display[]', 'check'=>$plan_display, 'checked'=>0, 'status'=>1,'label_class' =>esc_attr('plan-display-label')),
				'delivery_plan_display' => array('type'=>'separator', 'value'=>esc_html__('Delivery Plan Display', 'schedule-delivery-for-woocommerce-products'), 'class' => esc_attr('general-settings-header')),
				
			);
		}

		/**
         * Function for render page.
         */
		public function render_page() {
			$this->render_tabs();
			$this->render_content();
		}

		/**
         * Function for render content.
         */
		private function render_content() {
			if(isset($_POST['save_general_settings'])) {
				$result = $this->save_general_settings();
			}
			if(isset($_POST['default_general_settings'])) {
				$this->default_general_settings();
			}
			$fields = $this->get_fields();

			$week_start = isset($fields['week_start']) ? $fields['week_start'] : '';
			$delivery_day = isset($fields['delivery_day']) ? $fields['delivery_day'] : '';
			$start_date = isset($fields['start_date']) ? $fields['start_date'] : '';
			$end_date = isset($fields['end_date']) ? $fields['end_date'] : '';
			$holidays = isset($fields['holidays']) ? $fields['holidays'] : '';
			$plan_display = isset($fields['plan_display']) ? $fields['plan_display'] : '';

			$section = 'general_settings';
			$week_start = THSDF_Utils::set_values_props($week_start,'week_start', $section);
			$delivery_day = THSDF_Utils::set_values_props($delivery_day,'delivery_day', $section);

			$start_date = THSDF_Utils::set_values_props($start_date,'start_date', $section);
			$end_date = THSDF_Utils::set_values_props($end_date,'end_date', $section);
			$holidays = THSDF_Utils::set_values_props($holidays,'holidays', $section);
			$plan_display = THSDF_Utils::set_values_props($plan_display,'plan_display', $section);
			?>
			
			<div class="wrap woocommerce">
				<form action="" method="post" name="thsdf_general_settings_form" class="general-settings-form ptdelivery-admin-settings-form">
					<?php if (function_exists('wp_nonce_field')) {
                        wp_nonce_field('general_settings_form', 'general-settings-form'); 
                    } ?>
                    <?php $this->render_form_section_separator($fields['calender_settings']); ?>
					<table class="thsdf-schedule-setting-table calendar-settings-table">
						<?php $this->render_general_settings_elm_row($week_start); ?>
						<?php $this->render_general_settings_elm_row($delivery_day); ?>
						<?php $this->render_general_settings_elm_row($start_date); ?>
						<?php $this->render_general_settings_elm_row($end_date); ?>
						<?php $this->render_general_settings_elm_row($holidays); ?>						
					</table>
					<?php $this->render_form_section_separator($fields['delivery_plan_display']); ?>
					<table class="thsdf-schedule-setting-table delivery-plan-display">
						<?php $this->render_general_settings_elm_row($plan_display); ?>				
						<tr class="general-settings-row">
							<td class="submit">
								<input type="submit" name="save_general_settings" class="button-primary button-primary-thsdf-save-settings" value="<?php esc_html_e('Save changes', 'schedule-delivery-for-woocommerce-products'); ?>">
			              		<input type="submit" name="default_general_settings" class="button default_general_settings" value="<?php esc_html_e('Reset to Default', 'schedule-delivery-for-woocommerce-products'); ?>" onclick="return confirm('Are you sure you want to reset to default settings? all your changes will be deleted.');">
			              	</td>
			            </tr>
		        	</table>
	        	</form>
			</div>
		<?php
		}

		/**
         * Settings saving function.
         */
		function save_general_settings() {
			if(isset($_POST['general-settings-form']) && $_POST['general-settings-form']){
				$nonce = $_POST['general-settings-form'];
				$capability = THSDF_Utils::thsdf_capability();
				if(!wp_verify_nonce($nonce, 'general_settings_form') || !current_user_can($capability)) {
					die('You are not authorized to perform this action.');
				} else {
					$general_settings = array();
					if(!empty($this->settings_fields) && is_array($this->settings_fields)) {
						foreach($this->settings_fields as $name => $field) {
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
					$existing_settings = get_option(THSDF_Utils::OPTION_KEY_DELIVERY_SETTINGS);

					// Update option data of general settings.
					$result = false;
					if(!empty($settings)) {
						if(($settings['start_date'] != '') && !empty($settings['delivery_day']) && ($settings['end_date'] != '')) {
							$result = update_option(THSDF_Utils::OPTION_KEY_DELIVERY_SETTINGS, $settings);
						}
					}
					if($result == true) {
						parent::print_notices('Your changes were saved','updated',false);
					}else {
						parent::print_notices('Your changes were not saved due to an error (or you made none!)','error',false);
					}
				}
			}
		}

		/**
         * Default general setting funciton.
         */
		function default_general_settings() {
			if(isset($_POST['general-settings-form']) && $_POST['general-settings-form']){
				$nonce = $_POST['general-settings-form'];
				$capability = THSDF_Utils::thsdf_capability();
				if(!wp_verify_nonce($nonce, 'general_settings_form') || !current_user_can($capability)){
					die('You are not authorized to perform this action.');

				} else {
					$result = delete_option(THSDF_Utils::OPTION_KEY_DELIVERY_SETTINGS);
					parent::print_notices('Settings successfully reset.','updated',false);
				}
			}
		}

        /**
        * Set element row
        *
        */
        public function render_general_settings_elm_row($field){
        	?>
        	<tr class="general-settings-row">
				<?php $this->render_form_field_element($field, $this->cell_props); ?>
			</tr><?php
        }

	}
	
endif;