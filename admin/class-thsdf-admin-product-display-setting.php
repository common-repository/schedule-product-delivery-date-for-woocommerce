<?php
/**
 * Admin Single Product Calendar Display Settings Functionality
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

if(!class_exists('THSDF_Product_Display_Settings')) :

	/**
     * Admin product display settings class extends from admin settings.
     */ 
	class THSDF_Product_Display_Settings extends THSDF_Admin_Settings {
		protected static $_instance = null;
		private $settings_fields = NULL;
		private $cell_props = array();

		/**
         * Constructor.
         */
		public function __construct() {
			$this->cell_props = array(
				'label_cell_props' => 'class="th_display_pro"', 
				'input_cell_props' => 'style=width: 50%; class=forminp', 
				'input_width' => '250px', 'label_cell_th' => true 
			);
			$this->init_constants();
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
			$this->settings_fields = self::get_single_display_settings_fields();
		}

		/**
		 * The display settings field details of Individual product.
		 *
		 * @return array
		 */
		public static function get_single_display_settings_fields() {
			return array(
			'product_id'        	=> array('type'=>'hidden', 'name'=>'product_id'),
			'calender_colors'	=> array('value'=>'Calender Colors', 'type'=>'separator', 'class' => 'ptdt-display-set-calendar'),
			'calendar_border' 	=> array('type'=>'colorpicker', 'name'=>'calendar_border', 'label'=>esc_html__('
				Calendar Border','schedule-delivery-for-woocommerce-products'), 'value'=>'#bdb7b7'),
			'day_bg'	  		=> array('type'=>'colorpicker', 'name'=>'day_bg', 'label'=>esc_html__('
				Day Background','schedule-delivery-for-woocommerce-products'), 'value'=>'#e3f0f8'),
			'nav_arrow'	  		=> array('type'=>'colorpicker', 'name'=>'nav_arrow', 'label'=>esc_html__('
				Navigation Arrows','schedule-delivery-for-woocommerce-products'), 'value'=>'#a91549'),
			'tooltip_bg'	  	=> array('type'=>'colorpicker', 'name'=>'tooltip_bg', 'label'=>esc_html__('
				Tooltip Background','schedule-delivery-for-woocommerce-products'), 'value'=>'#ffffff'),
			'text_colors' 		=> array('value'=>esc_html__('Text Colors','schedule-delivery-for-woocommerce-products'), 'type'=>'separator', 'class' => esc_attr('ptdt-display-set-text')),
			'date_color' 		=> array('type'=>'colorpicker', 'name'=>'date_color', 'label'=>esc_html__('Date', 'schedule-delivery-for-woocommerce-products'), 'value'=>'#a91549' ),
			'day_title_color' 	=> array('type'=>'colorpicker', 'name'=>'day_title_color', 'label'=>esc_html__('Day Title', 'schedule-delivery-for-woocommerce-products'), 'value'=>'#6d6d6d' ),
			'day_color' 		=> array('type'=>'colorpicker', 'name'=>'day_color', 'label'=>esc_html__('Day', 'schedule-delivery-for-woocommerce-products'), 'value'=>'#3f415c'),
			'holiday_color'		=> array('type'=>'colorpicker', 'name'=>'holiday_color', 'label'=>esc_html__('Holidays', 'schedule-delivery-for-woocommerce-products'), 'value'=>'#a91549'),
			'tooltip_text'		=> array('type'=>'colorpicker', 'name'=>'tooltip_text', 'label'=>esc_html__('Tooltip Text', 'schedule-delivery-for-woocommerce-products'), 'value'=>'#444444'),
			'input_text'		=> array('type'=>'colorpicker', 'name'=>'input_text', 'label'=>esc_html__('Input Text', 'sschedule-delivery-for-woocommerce-products'), 'value'=>'#4a78bc'),
			);
		}

		/**
		 * Function for set display settings field content.
		 */
		public function render_content() {
			$fields = $this->get_single_display_settings_fields();
			$product_id = isset($fields['product_id']) ? $fields['product_id'] : '';
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

			$section = 'product_display_settings';

			$product_id = get_the_ID();
			$calendar_border = THSDF_Utils::set_values_props($calendar_border,'calendar_border', $section, $product_id);
			$day_bg = THSDF_Utils::set_values_props($day_bg,'day_bg', $section, $product_id);
			$nav_arrow = THSDF_Utils::set_values_props($nav_arrow,'nav_arrow', $section, $product_id);
			$tooltip_bg = THSDF_Utils::set_values_props($tooltip_bg,'tooltip_bg', $section, $product_id);
			$date_color = THSDF_Utils::set_values_props($date_color,'date_color', $section, $product_id);
			$day_title_color = THSDF_Utils::set_values_props($day_title_color,'day_title_color', $section, $product_id);
			$day_color = THSDF_Utils::set_values_props($day_color,'day_color', $section, $product_id);
			$holiday_color = THSDF_Utils::set_values_props($holiday_color,'holiday_color', $section, $product_id);
			$tooltip_text = THSDF_Utils::set_values_props($tooltip_text,'tooltip_text', $section, $product_id);
			$input_text = THSDF_Utils::set_values_props($input_text,'input_text', $section, $product_id);

			?>
			<div class="wrap woocommerce">
				<div class="thsdf_display_updated_mssg"></div>
				<table class="form-table ptdt-single-display-form-table">
					<tr class= ptdt-single-display-tr>
						<th>
							<?php
							$calender_colors = isset($fields['calender_colors']) ? $fields['calender_colors'] : '';
							$this->render_form_section_separator($calender_colors); ?>
						</th>
					</tr>
					
					<?php $this->render_display_settings_elm_row($product_id,false); ?>
					<?php $this->render_display_settings_elm_row($calendar_border); ?>
					<?php $this->render_display_settings_elm_row($day_bg); ?>
					<?php $this->render_display_settings_elm_row($nav_arrow); ?>
					<?php $this->render_display_settings_elm_row($tooltip_bg); ?>
					<tr class= ptdt-single-display-tr>
						<th>
							<?php 
							$text_colors = isset($fields['text_colors']) ? $fields['text_colors'] : '';
							$this->render_form_section_separator($text_colors); ?>
						</th>
					</tr>
						<?php $this->render_display_settings_elm_row($date_color); ?>
						<?php $this->render_display_settings_elm_row($day_title_color); ?>
						<?php $this->render_display_settings_elm_row($day_color); ?>
						<?php $this->render_display_settings_elm_row($holiday_color); ?>
						<?php $this->render_display_settings_elm_row($tooltip_text); ?>
						<?php $this->render_display_settings_elm_row($input_text); ?>
						
					<tr class= ptdt-single-display-tr>
						<td class="ptdt-single-display-submit-td">
							<div class="submit ptdt-single-display-set-submit">
								<input type="submit" name="save_product_display_settings" class="button-primary ptdt-single-display-settings-save" value="<?php esc_html_e('Save changes', 'schedule-delivery-for-woocommerce-products'); ?>">
								<input type="submit" name="default_product_display_settings" class="button default-single-display-settings" value="<?php esc_html_e('Reset to Default', 'schedule-delivery-for-woocommerce-products'); ?>">
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
        public function render_display_settings_elm_row($field,$class=true){
        	if($class == true){
	        		?>
	        	<tr class= ptdt-single-display-tr>
					<?php $this->render_form_field_element($field, $this->cell_props); ?>
				</tr>
					<?php
			}else{
				?>
				<tr style="display: none;">
					<?php $this->render_form_field_element($field, $this->cell_props); ?>
				</tr>
				<?php
			}
        }
	}
endif;