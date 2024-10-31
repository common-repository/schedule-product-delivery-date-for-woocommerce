<?php
/**
 * Admin Single Product Calendar General Settings Functionality
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

if(!class_exists('THSDF_Product_General_Settings')) :

	/**
     * Admin product general settings class extends from admin settings.
     */ 
	class THSDF_Product_General_Settings extends THSDF_Admin_Settings{
		protected static $_instance = null;
		private $settings_fields = NULL;
		private $cell_props;

		/**
         * Constructor.
         */
		public function __construct() {
			$this->cell_props = array(
				'label_cell_props' => '', 
				'input_cell_props' => '', 
				 'label_cell_th' => true 
			);
			$this->init_constants();
			//add_action('init', array($this, 'define_public_hooks'));
		}

		/**
         * instance.
         *
         * @return void
         */
		public static function instance() {
			if(is_null(self::$_instance)) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
         * Function for call the render page.
         */
		public function render_page() {
			$this->render_content();

		}

		/**
         * Function for initialise constants.
         */
		public function init_constants() {
			$this->settings_fields = $this->get_fields();
		}

		/**
		 * The calendar settings field details of Individual product.
		 *
		 * @return array
		 */
		public function get_fields() {
			$week_start = $this->get_week_start();
			$delivery_day = $this->get_delivery_day();
			$plan_display = $this->get_plan_display();
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
		 * Function for set Calender settings field content.
		 */
		public function render_content() {
			$fields = $this->get_fields();
			$product_id = get_the_ID();
			$current_date = date("Y-m-d");
			$week_start = isset($fields['week_start']) ? $fields['week_start'] : '';
			$delivery_day = isset($fields['delivery_day']) ? $fields['delivery_day'] : '';
			$start_date = isset($fields['start_date']) ? $fields['start_date'] : '';
			$end_date = isset($fields['end_date']) ? $fields['end_date'] : '';
			$holidays = isset($fields['holidays']) ? $fields['holidays'] : '';
			$plan_display = isset($fields['plan_display']) ? $fields['plan_display'] : '';

			$product_id = get_the_ID();
			$section = 'product_general_settings';
			$week_start = THSDF_Utils::set_values_props($week_start,'week_start', $section, $product_id);
			$delivery_day = THSDF_Utils::set_values_props($delivery_day,'delivery_day', $section, $product_id);
			$start_date = THSDF_Utils::set_values_props($start_date,'start_date', $section, $product_id);
			$end_date = THSDF_Utils::set_values_props($end_date,'end_date', $section, $product_id);
			$holidays = THSDF_Utils::set_values_props($holidays,'holidays', $section, $product_id);
			$plan_display = THSDF_Utils::set_values_props($plan_display,'plan_display', $section, $product_id);

		 ?>

			<div class="wrap woocommerce">
				<div class="thsdf_updated_mssg"></div>
					<table class="form-table thpt-form-table thsdf-schedule-setting-table" >
						<?php $product_id = get_the_ID(); ?>
						<input type="hidden" name="g_product_id" value="<?php echo esc_attr($product_id); ?>">
						<?php $this->render_general_settings_elm_row($week_start); ?>
						<?php $this->render_general_settings_elm_row($delivery_day); ?>
						<?php $this->render_general_settings_elm_row($start_date); ?>
						<?php $this->render_general_settings_elm_row($end_date); ?>
						<?php $this->render_general_settings_elm_row($holidays); ?>						
					</table>
					<?php $this->render_form_section_separator($fields['delivery_plan_display']); ?>
					<table class="thpt-form-table thsdf-schedule-setting-table">
						<?php $this->render_general_settings_elm_row($plan_display,false); ?>
						
						<tr class="thpt-general-settings-row thpt-general-settings-submit">
							<td class="thsdf-single-submit-td">
								<div class="submit">
									<input type="submit" name="save_calender_settings" class="button-primary save_calender_settings button-primary-thsdf-save-settings" value="<?php esc_html_e('Save changes', 'schedule-delivery-for-woocommerce-products'); ?>">
									<input type="submit" name="default_general_settings" class="button default-single-general-settings" value="<?php esc_html_e('Reset to Default', 'schedule-delivery-for-woocommerce-products'); ?>" >
				              	</div>
							</td>
						</tr>
					</table>

					<div class="ptdt-setting-loader" style="display:none;">
					    <img src="<?php echo esc_url_raw(plugins_url('assets/img/spinner.gif', __FILE__)); ?>" />
					</div>
			</div>
		<?php }

		/**
        * Set element row
        *
        */
        public function render_general_settings_elm_row($field,$class=true){
        	if($class == true){
	        	?>
	        	<tr class="thpt-general-settings-row">
					<?php $this->render_form_field_element($field, $this->cell_props); ?>
				</tr><?php
			}else{
				?>
	        	<tr>
					<?php $this->render_form_field_element($field, $this->cell_props); ?>
				</tr><?php
			}
        }
    }
endif;