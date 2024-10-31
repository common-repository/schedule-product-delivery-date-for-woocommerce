<?php
/**
 * The admin settings page specific functionality of the plugin.
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

if(!class_exists('THSDF_Admin_Settings')) :
	/**
     * Admin settings class.
    */ 
	abstract class THSDF_Admin_Settings {
		protected $page_id = '';	
		protected $tabs = '';

		// $this->get_tabs();
		/**
         * Constructor function.
         *
         * @param string $page The page id info
         * @param string $section The section name info
         */
		public function __construct($page, $section = '') {
			$this->page_id = $page;
			$this->tabs = array('general_settings' => esc_html__('General Settings','schedule-delivery-for-woocommerce-products'), 'display_settings' => esc_html__('Display Settings','schedule-delivery-for-woocommerce-products'));
			add_action('init', array($this, 'write_log'));
		}

		/**
         * Function for get tab.
         */
		public function get_tabs() {
			return $this->tabs;
		}

		/**
         * Function for get current tab.
         */
		public function get_current_tab() {
			return $this->page_id;
		}

		/**
         * Function for get render tab.
         */
		public function render_tabs() {
			$current_tab = $this->get_current_tab();
			$tabs = $this->get_tabs();
			if(empty($tabs)) {
				return;
			}			
			echo '<h2 class="nav-tab-wrapper woo-nav-tab-wrapper">';
			if(!empty($tabs) && is_array($tabs)){
				foreach($tabs as $id => $label) {
					$active = ($current_tab == $id) ? 'nav-tab-active' : '';
					$label = esc_html__($label, 'schedule-delivery-for-woocommerce-products');
					echo '<a class="nav-tab ' . esc_attr($active).'" href="'. esc_url($this->get_admin_url($id)) .'">'.esc_attr($label).'</a>';
				}
			}
			echo '</h2>';		
		}
	
		/**
         * Function for get admin url.
         *
         * @param string $tab The tab name on delivery settings section
         *
         * @return string
         */
		public function get_admin_url($tab = false) {
			$url = 'edit.php?post_type=product&page=th_schedule_delivery_free';
			if($tab && !empty($tab)) {
				$url .= '&tab='. $tab;
			}
			return admin_url($url);
		}

		/**
         * Function for get admin url.
         *
         * @param string $tooltip The tooltip info
         *
         * 
         */
		public function render_form_fragment_tooltip($tooltip = false) {
			if($tooltip) { 
                $src = THSDF_ASSETS_URL_ADMIN . '/img/help.png';
                $tooltip = '<span class="thsdf_tooltip_data"><a href="javascript:void(0)" title="' . esc_html__($tooltip, "schedule-delivery-for-woocommerce-products") .'" class="thsdf_tooltip"><img src="'.esc_url($src).'" title=""/></a></span>';
				return $tooltip;
			} else { ?>

			<?php }
		}
        /**
         * Function for display the notices.
         *
         * @param $msg The message
         * @param $class_name The class  name
         * 
         * @return notice
         */
		public static function print_notices($msg, $class_name='updated',$return=true){
			$notice = '<div class="'. $class_name .'"><p>'. esc_html__($msg, 'schedule-delivery-for-woocommerce-products') .'</p></div>';
			if($return == false){
				echo wp_kses_post($notice);
			}
			return $notice;
		}
        /**
         * Function for getting the week start.
         * 
         * 
         */
        public static function get_week_start(){
          return  array(
                    'sunday' => esc_html__('Sunday', 'schedule-delivery-for-woocommerce-products'),
                    'monday' => esc_html__('Monday', 'schedule-delivery-for-woocommerce-products'),
                    'tuesday' => esc_html__('Tuesday', 'schedule-delivery-for-woocommerce-products'),
                    'wednesday' => esc_html__('Wednesday', 'schedule-delivery-for-woocommerce-products'),
                    'thursday' => esc_html__('Thursday', 'schedule-delivery-for-woocommerce-products'),
                    'friday' => esc_html__('Friday', 'schedule-delivery-for-woocommerce-products'),
                    'saturday' => esc_html__('Saturday', 'schedule-delivery-for-woocommerce-products')

                );
        }
        /**
         * Function for getting get delivery day.
         * 
         * @return array
         */
        public static function get_delivery_day(){
            return array(
                    1 => esc_html__('M', 'schedule-delivery-for-woocommerce-products'),
                    2 => esc_html__('T', 'schedule-delivery-for-woocommerce-products'),
                    3 => esc_html__('W', 'schedule-delivery-for-woocommerce-products'),
                    4 => esc_html__('T', 'schedule-delivery-for-woocommerce-products'),
                    5 => esc_html__('F', 'schedule-delivery-for-woocommerce-products'),
                    6 => esc_html__('S', 'schedule-delivery-for-woocommerce-products'),
                    0 => esc_html__('S', 'schedule-delivery-for-woocommerce-products')             
                );
        }
        /**
         * Function for getting plan display.
         * 
         * @return array
         */
        public static function get_plan_display(){
            return array(
                    'cart-page-view' => esc_html__('Display Delivery Details on the Cart', 'schedule-delivery-for-woocommerce-products'),
                    'checkout-page-view' =>esc_html__('Display Delivery details on the Checkout page', 'schedule-delivery-for-woocommerce-products'),
                    'thankyou-page-view' => esc_html__('Display Delivery details on the Thank you page', 'schedule-delivery-for-woocommerce-products')

                );
        }
		/**
         * Render form field element.
         *
         * @param array $field the field datas
         * @param array $atts field style array attribute
         * @param array $render_cell render cell information
         *
         * @return array
         */
        public function render_form_field_element($field, $atts = array(), $render_cell = true) {
        	if($field && is_array($field)) {
                $args = shortcode_atts(array(
                    'label_cell_props' => '',
                    'input_cell_props' => '',
                    'label_cell_colspan' => '',
                    'input_cell_colspan' => '',
                ), $atts);
                $ftype     = isset($field['type']) ? $field['type'] : 'text';
                $flabel    = isset($field['label']) && !empty($field['label']) ? $field['label'] : '';
                $sub_label = isset($field['sub_label']) && !empty($field['sub_label']) ? $field['sub_label'] : '';
                $tooltip   = isset($field['hint_text']) && !empty($field['hint_text']) ? ($field['hint_text']) : '';
                
                $field_html = '';
                
                if($ftype == 'text') {
                    $field_html = $this->_render_form_field_element_inputtext($field, $atts);                   
                }
                else if($ftype == 'number') {
                    $field_html = $this->_render_form_field_element_inputnumber($field, $atts);
                    
                }else if($ftype == 'textarea') {
                    $field_html = $this->_render_form_field_element_textarea($field, $atts);
                       
                }else if($ftype == 'select') {
                    $field_html = $this->_render_form_field_element_select($field, $atts);     
                    
                }else if($ftype == 'multicheckbox') {
                    $field_html = $this->_render_form_field_element_multicheckbox($field, $atts);     
                    
                }else if($ftype == 'colorpicker') {
                    $field_html = $this->_render_form_field_element_colorpicker($field, $atts);              
                
                }else if($ftype == 'checkbox') {
                    $field_html = $this->_render_form_field_element_checkbox($field, $atts, $render_cell);   
                    $flabel     = '&nbsp;';  
                }else if($ftype == 'multidatepicker') {
                    $field_html = $this->_render_form_field_element_multidatepicker($field, $atts, $render_cell); 
                }else if($ftype == 'hidden') {
                    $field_html = $this->_render_form_field_element_hidden($field, $atts); 
                }
                
                // Allowed html tags.
                $html_tags = THSDF_Utils::allowed_html_tags();

                if($render_cell) {
                    $required_html = isset($field['required']) && $field['required'] ? '<span class="thsdf-required">*<span>' : '';
                    $required_tag = (!empty($required_html) && isset($field['required_tag']) && isset($field['required_class'])) ? '<span class="'.esc_attr($field['required_class']).'"><p>'. $field['required_tag'] .'</p></span>' : '';
                    $label_cell_props = !empty($args['label_cell_props']) ? $args['label_cell_props'] : '';
                    $input_cell_props = (!empty($args['input_cell_props'])) ? $args['input_cell_props'] : '';
                    // $input = ($ftype != 'multicheckbox') ? $args['input_cell_props'] : '';
                    if($flabel){ 
                        if($tooltip){
                            $tooltip = $this->render_form_fragment_tooltip($tooltip);
                            ?>
                            <th <?php echo esc_attr($label_cell_props); ?>><?php esc_html_e($flabel,'schedule-delivery-for-woocommerce-products'); echo wp_kses_post($required_html); ?><?php echo esc_attr($tooltip); ?></th>
                            <td <?php echo esc_attr($input_cell_props); ?> ><?php echo wp_kses($field_html, $html_tags);?><?php echo esc_attr($required_tag); ?> </td>
                        <?php
                        } else {
                        ?>
                            <th <?php echo wp_kses_post($label_cell_props); ?>><?php  esc_html_e($flabel,'schedule-delivery-for-woocommerce-products'); echo wp_kses_post($required_html); ?></th>
                            <?php
                            $input_cell_props = (($ftype == 'multicheckbox') && !empty($input_cell_props)) ? ('class="ptdt_general_settings_td ptdt_general_weekdays_td"') : esc_html__($input_cell_props);
                            ?>
                            <td <?php echo esc_attr($input_cell_props); ?> ><?php echo wp_kses($field_html, $html_tags);?><?php echo wp_kses_post($required_tag); ?> </td>
                        <?php
                        }
                    } else {
                        ?>
                        <td <?php esc_html_e($input_cell_props) ?> ><?php echo wp_kses($field_html, $html_tags); ?><?php echo wp_kses_post($required_tag); ?> </td>
                    <?php } 
                } else {
                    echo wp_kses($field_html, $html_tags);
                }
            }

        }

        /**
         * Function prepare field props.
         *
         * @param array $field the field data
         * @param array $atts the input info
         *
         * @return array
         */
        private function _prepare_form_field_props($field, $atts = array()) {
            $field_props = '';
            $args = shortcode_atts(array(
                'input_width' => '',
                'input_name_prefix' => 'g_',
                'input_name_suffix' => '',
            ), $atts);
            
            $ftype = isset($field['type']) ? $field['type'] : 'text';
            
            $fname  = $args['input_name_prefix'].$field['name'].$args['input_name_suffix'];
            $fvalue = (isset($field['value']) && !is_array($field['value']) && !empty($field['value'])) ? ' value=' . esc_attr($field['value']) . '' : '';
            $input_width  = $args['input_width'] && ($ftype != 'multicheckbox') ? 'width:'.$args['input_width'].';' : '';
            $style = isset($input_width) && !empty($input_width) ? ' style="'. esc_attr($input_width) . '"' : '';
            $class_name = isset($field['class']) && !empty($field['class']) ? ' class="'.esc_attr($field['class']).'"' : '';
            $fid = isset($field['id']) && !empty($field['id']) ? ' id="'.esc_attr($field['id']).'"' : '';
            $field_props  = 'name="'. esc_attr($fname) . '" ' . esc_attr($fvalue) . $style . $class_name . $fid ;
            $field_props .= (isset($field['placeholder']) && !empty($field['placeholder'])) ? ' placeholder="'.esc_attr($field['placeholder']).'"' : '';
            $field_props .= (isset($field['onchange']) && !empty($field['onchange'])) ? ' onchange="'.$field['onchange'].'"' : '';
            if($ftype == 'number') {
                $fmin = isset($field['min']) ? $field['min'] : '';
                $fmax = isset($field['max']) ? $field['max'] : '';
                $field_props .= 'min="'. $fmin .'"max="'.$fmax.'"';
            }
            return $field_props;
        }
        /**
         * Render form field for text input element.
         *
         * @param string $field the field data
         * @param string $atts the attribute information
         *
         * @return void
         */
        private function _render_form_field_element_inputtext($field, $atts = array()) {
            $field_html = '';
            if($field && is_array($field)) {
                $field_props = $this->_prepare_form_field_props($field, $atts);
                $field_html = '<input type="text" '. $field_props .' autocomplete="off" readonly />';
            }
            return $field_html;
        }
        /**
         * Render form field for text input element.
         *
         * @param string $field the field data
         * @param string $atts the attribute information
         *
         * @return void
         */
        private function _render_form_field_element_hidden($field, $atts = array()) {
            $field_html = '';
            if($field && is_array($field)) {
                $field_props = $this->_prepare_form_field_props($field, $atts);
                $field_html = '<input type="hidden" '. $field_props .' autocomplete="off" readonly />';
            }
            return $field_html;
        }
        /**
         * Render form field for colorpicker element.
         *
         * @param string $field the field data
         * @param string $atts the attribute information
         *
         * @return void
         */
        private function _render_form_field_element_colorpicker($field, $atts = array()) {
            $field_html = '';
            if($field && is_array($field)) {
                $field_props = $this->_prepare_form_field_props($field, $atts);
                $label_class = isset($field['label_class']) && !empty($field['label_class']) ? $field['label_class'] : '';
                $field_html  .= '<span class="ptdt-display-admin-colorpickpreview ' . esc_attr($field['name']) . '_preview '.$label_class.' " style="background:'.($field['value']).';"></span>';
		        $field_html .= '<input type="text" '. $field_props .' class="ptdt-display-admin-colorpick input" autocomplete="off"/>';
		        
            }
            return $field_html;
        }

        /**
         * Render form field for select element.
         *
         * @param string $field the field data
         * @param string $atts the attribute information
         *
         * @return void
         */
        private function _render_form_field_element_select($field, $atts = array()) {
            $field_html = '';
            if($field && is_array($field)) {
                $fvalue = isset($field['value']) ? $field['value'] : '';
                $field_props = $this->_prepare_form_field_props($field, $atts);
                
                $field_html = '<select '. $field_props .'>';
                if(!empty($field['options']) && is_array($field['options'])){
                    foreach($field['options'] as $value => $label){
                        $selected = $value === $fvalue ? 'selected' : '';
                        $field_html .= '<option value="'. trim($value) .'" '.esc_attr($selected).'>'. esc_html__($label,'schedule-delivery-for-woocommerce-products') .'</option>';
                    }
                }
                $field_html .= '</select>';
            }
            return $field_html;
        }

         /**
         * Render form field for multi select element.
         *
         * @param string $field the field data
         * @param string $atts the attribute information
         *
         * @return void
         */
        private function _render_form_field_element_multicheckbox($field, $atts = array()) {
            // $field_html = '';

            $field_html = '';
            if($field && is_array($field)) {
                $args = shortcode_atts(array(
                    'label_props' => '',
                    'cell_props'  => 3,
                    'render_input_cell' => false,
                ), $atts);
            
                $fid  =  isset($field['id']) ? 'id="'.$field['id'].'"' : '';
                $fvalue = isset($field['value']) ? $field['value'] : '';
                $field_props  = $this->_prepare_form_field_props($field, $atts);
                // $field_props .= isset($field['checked']) && $field['checked'] === 1  ? ' checked' : '';
                
                // $field_html = '<td class="ptdt_general_settings_td ptdt_general_weekdays_td">';
                if(!empty($field['check']) && is_array($field['check']) && isset($field['check'])){
                    foreach ($field['check'] as $key => $value){

                        $checked = !empty($fvalue) && in_array($key,$fvalue) ? ' checked' : '';
                        $label = '<label class= "'.$field['label_class'].'">'.$value .'</label>';
                        $input  = '<input type="checkbox" '. $fid .' '. $field_props .' value="'.esc_attr($key).'" '.esc_attr($checked).' />';
                       // $input  = '<input type="checkbox" '. esc_attr($fid) . esc_attr($field_props) .' value="'.esc_attr($key).'" '.esc_attr($checked).'/>';

                        if($field['name'] == 'plan_display[]'){
                            $field_html .= '<div class="plan_display_notf">' . $input . $label . '</div>';
                        }else{
                            $field_html .= $label . $input;
                        }
                    }
                }
                // $field_html .= '</td>';
            }
            return $field_html;
        }

        /**
         * Render form field for multi datepicker.
         *
         * @param string $field the field data
         * @param string $atts the attribute information
         *
         * @return void
         */
        public function _render_form_field_element_multidatepicker($field, $atts = array()) {
            $field_html = '<table border="0" cellpadding="0" cellspacing="0" class="holidays-list ptdt-admin-dynamic-row-table"><tbody>';
            if($field && is_array($field)){
                $field_props = $this->_prepare_form_field_props($field, $atts);
                $default = '<tr>
                                <td style="width:260px;">
                                    <input type="text" name="g_holidays[]" placeholder="yyyy-mm-dd" value="" class="general-set-date_holiday g-holidays" style="width:250px;" autocomplete="off" readonly/>
                                </td>
                                <td class="action-cell">
                                    <a href="#" onclick="holidaysAddClassRow(this)" class="btn btn-green" title="Add new class"><span class="dashicons dashicons-plus"></span></a>
                                </td>
                                <td class="action-cell">
                                    <a href="#" onclick="holidaysRemoveClassRow(this)" class="btn btn-red" title="Remove class"><span class="dashicons dashicons-no-alt"></span></a>
                                </td>                                       
                            </tr>';
                if(!empty($field['value'])&& is_array($field['value'])){
                    foreach ($field['value'] as $key => $value){
                        if(!empty($value)){
                            $field_html .= '<tr>';
                            $field_html .= '<td style="width:260px;">';
                            $field_html .= '<input type="text" '. $field_props .' autocomplete="off" readonly value="'.esc_attr($value).'"/>';
                            $field_html .= '</td>';
                            $field_html .= '<td class="thsdf-action-cell action-cell">
                                                <a href="#" onclick="holidaysAddClassRow(this)" class="btn btn-green thsdf-add-class" title="Add new class"><span class="dashicons dashicons-plus"></span></a>
                                            </td>
                                            <td class="thsdf-action-cell action-cell">
                                                <a href="#" onclick="holidaysRemoveClassRow(this)" class="btn btn-red thsdf-remove-class" title="Remove class"><span class="dashicons dashicons-no-alt"></span></a>
                                            </td>                                           
                                            <td class="break"></td>';
                            $field_html .= '</tr>';
                        } else if($key == 0){
                            $field_html .= $default;
                        }

                    }
                } else{
                    $field_html .= $default;
                }
            }
            $field_html .= '</tbody></table>';
            return $field_html;
        }

        /**
         * Render form field separator
         *
         * @param string $props the props data
         *
         * @return void
         */
		public function render_form_section_separator($props) { 
        ?>
			<h3 class='<?php  esc_attr_e($props['class']);?> '><?php esc_html_e($props['value']); ?></h3>            
        <?php 
    	}
	}
endif;
