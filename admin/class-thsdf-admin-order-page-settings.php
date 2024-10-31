<?php
/**
 * The admin order page functionality of the plugin
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

if(!class_exists('THSDF_Order_Page_Settings')) :

	/**
     * Admin order page settings class extends from admin settings.
     */
	class THSDF_Admin_Order_Page_Settings extends THSDF_Admin_Settings {
		public $email_delivery_date;
		public $product_name;
		public $product_qty;
		public $product_price;
		public $delivery_time;

		/**
         * Constructor.
         */
		public function __construct() {
			$this->define_order_page_hook();
			$this->email_delivery_date ='';
			$this->product_name = ''; 
			$this->product_qty = '';
			$this->product_price = '';
			$this->delivery_time = '';
		}

		/**
         * Function for define order page hook.
         */
		public function define_order_page_hook() {
			add_action('woocommerce_after_order_itemmeta', array($this,'date_after_order_itemmeta'), 10, 3); 
			add_action('woocommerce_admin_order_data_after_order_details', array($this, 'ptdt_update_woo_order_status'), 10, 1);
			add_action('wp_ajax_order_status_completed', array($this, 'order_status_completed'), 10);
			add_action('wp_ajax_order_status_deleted', array($this, 'order_status_deleted'), 10);
			add_action('wp_ajax_order_date_specific_refund', array($this, 'order_date_specific_refund'), 10);
    		add_action('woocommerce_refund_created', array($this, 'woocommerce_refund_created'), 10, 2);
    		add_action('wp_ajax_ordered_item_delete_from_cancelled_table', array($this, 'ordered_item_delete_from_cancelled_table'), 10);
  		}

  		/**
		 * Function for get colorpicker.
		 *
         * @param integer $item_id The item id
         * @param array $item The item details
         * @param array $product The attribute data
         *
         * @return array
		 */
  		function date_after_order_itemmeta($item_id, $item, $product) {
			$product_array = (array) $product;
			$capability = THSDF_Utils::thsdf_capability();
			if(!current_user_can($capability)){
				wp_die();
			}
			$price_value = '';
			if(!empty($product_array) && is_array($product_array)) {
				foreach($product_array as $product_key => $product_data) {
					if(!empty($product_data)) {
						if(is_array($product_data)) {
							foreach ($product_data as $key => $value) {
								if($key === 'price' && $value !== '') {
									$price_value = $value;
								}
							}
						}
					}
				}
			}
			$updated_qty = '';
			$ptdt_delivery_date = wc_get_order_item_meta($item_id, THSDF_Utils::ORDER_KEY_DELIVERY_DATE, 'true');
			if(!empty($ptdt_delivery_date)) {
				$order_date = '';
				$ptdt_order_disable_quantity_field = apply_filters('ptdt_order_disable_quantity_field', true);
				if($ptdt_order_disable_quantity_field) {
					$order_date .= '<input type="hidden" name="have_delivery_calendar" class="have_ptdt_calendar" value="'.esc_attr($item_id).'">';
				}
				$order_cancelled_table_view_mode1 = apply_filters('ptdt_order_cancelled_table_view_mode1', true);
				if($order_cancelled_table_view_mode1) {
					$order_date .= '<div class="plan_delivery_wrapper">';
						$order_date .= '<h3 class="plan_delivery_toggle">'. esc_html__('Planned Delivery Details', 'schedule-delivery-for-woocommerce-products').'<span class="dashicons dashicons-controls-play"></span></h3>';

						$order_date .= '<div class="plan_delivery_details">';
						    $order_date .= '<table class="plan_delivery_dls_table plan_delivery_dls_table_'.esc_attr($item_id).'">';
						    	$order_date .='<tr class="plan_delivery_dls_tr">';
						    		$order_date .='<th class="plan_delivery_dls_th">'. esc_html__('Date', 'schedule-delivery-for-woocommerce-products').'</th>';
						    		$order_date .='<th class="plan_delivery_dls_th">'. esc_html__('Quantity', 'schedule-delivery-for-woocommerce-products').'</th>';
						    		$order_date .='<th class="plan_delivery_dls_th">'. esc_html__('Status', 'schedule-delivery-for-woocommerce-products').'</th>';
						    		$order_date .='<th class="plan_delivery_dls_th">'. esc_html__('Action', 'schedule-delivery-for-woocommerce-products').'</th>';
						    	$order_date .='</tr>';

						    	if(is_array($ptdt_delivery_date) && is_array($ptdt_delivery_date)) {
								    foreach($ptdt_delivery_date as $date => $delivery_data) {
								    	$date_create = date_create($date);
								    	$new_date = date_format($date_create,"d M Y ");

								    	$d_status = isset($delivery_data['status']) ? $delivery_data['status'] : '';
								    	$d_qty = isset($delivery_data['quantity']) ? $delivery_data['quantity'] : '';
										$odr_status = '';
								    	$odr_status = $this->get_ptdt_order_status($d_status);							    	
								    	if($d_status == '1') {
								    		$status_class = 'status_blue';
								    		$completed = 'Complete';
								    		$cancelled = 'Cancel';
								    		$not_active = '';
								    	} else if($d_status == '4') {
								    		$status_class = 'status_green';
								    		$completed = '-';
								    		$cancelled = '-';
								    		$not_active = 'not-active';
								    	} else if($d_status == '3') {
								    		$status_class = 'status_red';
								    		$completed = '-';
								    		$cancelled = '-';
								    		$not_active = 'not-active';
								    	} else if($d_status == '2') {
								    		$status_class = 'status_orange';
								    		$completed = '-';
								    		$cancelled = '-';
								    		$not_active = 'not-active';
								    	}
								    	
							    		if($d_status == '1' || $d_status == '4') {
								    		$order_date .='<tr class="plan_delivery_dls_tr plan_delivery_dls_tr_'.esc_attr($date).'_'. esc_attr($item_id).'">';
											    $order_date .='<td class="plan_delivery_dls_td">'.esc_html__($new_date, 'schedule-delivery-for-woocommerce-products').'</td>';
											    $order_date .='<td class="plan_delivery_dls_td">'.esc_attr($d_qty).'</td>';
											    $order_date .='<td class="plan_delivery_dls_td"><span class="plan_delivery_dls_status '.esc_attr($status_class).' ptdt_delivery_status_'.esc_attr($date).'_'. esc_attr($item_id).'">'.esc_html__($odr_status, 'schedule-delivery-for-woocommerce-products').'</span></td>';
											   
											    if($d_status != '4' || $d_status != '3') {
													$order_date .= '<td class="plan_delivery_dls_td"><a href="" data-itemid ="'. esc_attr($item_id) .'" data-itemdate="'. esc_attr($date) .'" data-itemqty="'. esc_attr($d_qty) .'" data-price = "'.esc_attr($price_value).'" class="ptdt_delivery_completed delivery_completed_'.esc_attr($date).'_'.esc_attr($item_id).' '.esc_attr($not_active) .' "> '.esc_html__($completed, 'schedule-delivery-for-woocommerce-products') .'</a>'. '/';

													$order_date .= '<a href="" data-itemid ="'.esc_attr($item_id).'" data-itemdate="'.esc_attr($date).'" data-itemqty="'. esc_attr($d_qty) .'" data-price = "'.esc_attr($price_value).'" class="ptdt_delivery_cancelled delivery_cancelled_'.esc_attr($date).'_'. esc_attr($item_id).' '.esc_attr($not_active).'"> '.esc_html__($cancelled, 'schedule-delivery-for-woocommerce-products').'</a>
													</td>';	
												}
											$order_date .='</tr>';
										}
								    	
									}
								}	
							$order_date .= '</table>';
							$order_date .= '<div class="ptdt-setting-loader-order-edit ptdt-setting-loader_'.esc_attr($date).'_'. esc_attr($item_id).' " style="display:none;">';
							    $order_date .= '<img src=';
							    $order_date .= esc_url_raw(plugins_url('assets/img/spinner.gif', __FILE__));
							    $order_date .=' />';
							$order_date .= '</div>';
						$order_date .= '</div>';

						/*. Cancelled Table. */
						$d_order_status = array();
						if(is_array($ptdt_delivery_date) && is_array($ptdt_delivery_date)) {
							foreach($ptdt_delivery_date as $date => $delivery_data) {
								$d_status = isset($delivery_data['status']) ? $delivery_data['status'] : '';
								$hide_table = '';
								$d_order_status[] = $d_status;
							}
						}
						$target = array('2', '3', '5', '6');
						if(count(array_intersect($d_order_status, $target)) == 0) {
							$hide_table = 'thwd_hide_cancel_table';
						} else{
							$hide_table = '';
						}
						$order_date .= '<div class="plan_delivery_cancelled_items ptdt_cancelled_items_'. esc_attr($item_id).' ' .esc_attr($hide_table).'">';
							$order_date .= '<h3 class="ptdt_cancelled_header"> Cancelled Items</h3>';

							$order_date .= '<table class="delivery_cancelled_item_table">';
								$order_date .= '<tr class="delivery_cancelled_header">';
									$order_date .= '<td id="cb" class="manage-column column-cb check-column ptdt_order_cancelled_checkbox"><label class="screen-reader-text" for="cb-select-all-1">Select All</label>';
									$order_date .= '<input id="ptdt-select-all" type="checkbox" class="ptdt-select-all" checked></td>';
									$order_date .= '<th class="ptdt_cancelled_item_table_th">'. esc_html__('Date', 'schedule-delivery-for-woocommerce-products').'</th>';
									$order_date .= '<th class="ptdt_cancelled_item_table_th">'. esc_html__('Quantity', 'schedule-delivery-for-woocommerce-products').'</th>';
									$order_date .= '<th class="ptdt_cancelled_item_table_th">'. esc_html__('Refund Status', 'schedule-delivery-for-woocommerce-products').'</th>';
									$order_date .= '<th class="ptdt_cancelled_item_table_th">'. esc_html__('Price', 'schedule-delivery-for-woocommerce-products').'</th>';
									$order_date .= '<th class="ptdt_cancelled_item_table_th">'. esc_html__('Action', 'schedule-delivery-for-woocommerce-products').'</th>';
								$order_date .= '</tr>';	

								if(!empty($ptdt_delivery_date) && is_array($ptdt_delivery_date)) {
									foreach($ptdt_delivery_date as $date => $delivery_data) {
										$date_create = date_create($date);
						    			$new_date = date_format($date_create,"d M Y ");
						    			$qty = isset($delivery_data['quantity']) ? $delivery_data['quantity'] : '';
						    			if(is_numeric($qty) && is_numeric($price_value)) {
						    				$product_price = $price_value * $qty;
						    			} else {
						    				$product_price = '0';
						    			}
						    			$formated_price = wc_price($product_price);

						    			$d_status = isset($delivery_data['status']) ? $delivery_data['status'] : '';
						    	
								    	if($d_status == '3') {
								    		$status_class = 'status_red';
								    	} else if($d_status == '2' || $d_status == '5') {
								    		$status_class = 'status_orange';
								    	}else if($d_status == '6') {
								    		$status_class = 'status_yellow';
								    	}

								    	$odr_status = '';
						    			$odr_status = $this->get_ptdt_order_status($d_status);
						    			$current_time = date("M d, Y @ h:i A");
						    			$class = '';
						    			$cancel_disable = '';
						    			$user_cancelled_item = array(); 
						    			if($d_status == '6') {
						    				$user_item_id = $item_id;
						    				$user_date = $date;
						    				$user_cancelled_item = array("item_id"=>$user_item_id, "date"=>$user_date);						    				
						    				wc_update_order_item_meta($user_item_id,  THSDF_Utils::ORDER_KEY_USER_DELETE,  $user_cancelled_item);
						    			}

										if($d_status == '3' || $d_status == '2' || $d_status == '5' || $d_status == '6') {
											$checked = '';
											$disabled = '';
											if($d_status == '3') {
												$checked = '';
												$disabled = 'disabled';
												$cancel_disable = 'cancel_disabled';
												
											} else if($d_status == '2' || $d_status == '5'|| $d_status == '6') {
												$checked = 'checked';
												$class = 'order_checkbox_display';
												
											}

											$order_date .= '<input type="hidden" name="sd_order_item_id" value="'.esc_attr($item_id).'" class="sd_order_item_id">';
											$order_date .= '<input type="hidden" name="order_item_date" value="'.esc_attr($new_date).'">';
											$order_date .= '<tr class="delivery_cancelled_row delivery_cancelled_row_'. esc_attr($item_id).' delivery_cancelled_tr_'.esc_attr($date).'_'. esc_attr($item_id).'">';
												$order_date .= '<th scope="row" class="check-column">			
													<label class="screen-reader-text" for="cb-select-'.esc_attr($item_id).'">
														Select Order – '.esc_attr($current_time).'
													</label>
													<input id="cb-select-'.esc_attr($item_id).'" class="ptdt_order_checkbox '.esc_attr($class).' cb-select-'.esc_attr($item_id).'" type="checkbox" name="product_order[]" value="'.esc_attr($item_id).'" data-qty="'.esc_attr($qty).'" data-date="'.esc_attr($date).'"  '.esc_attr($checked).' '.esc_attr($disabled).'>
													<div class="locked-indicator">
														<span class="locked-indicator-icon" aria-hidden="true"></span>
														<span class="screen-reader-text">
														“Order – '.esc_attr($current_time).'” is locked				</span>
													</div>
												</th>';
												
												// if(!empty($d_time)) {
											 //    	$order_date .='<td class="ptdt_cancelled_item_table_td">'.$new_date.'<br/>('.$d_time.')</td>';
											 //    } else{
											    	$order_date .='<td class="ptdt_cancelled_item_table_td">'.esc_attr($new_date).'</td>';
											    // }
												$order_date .= '<td class="ptdt_cancelled_item_table_td item_qty_'.esc_attr($item_id).'">'.esc_attr($qty).'<input type="hidden" name="cancel_item_qty" value="'.esc_attr($qty).'"></td>';
												$order_date .= '<td class="ptdt_cancelled_item_table_td"><span class="plan_delivery_dls_status '.esc_attr($status_class).' ptdt_delivery_status_'.esc_attr($date).'_'. esc_attr($item_id).'">'.esc_html__($odr_status, 'schedule-delivery-for-woocommerce-products').'</span></td>';
												$order_date .= '<td class="ptdt_cancelled_item_table_td">'.$formated_price.'</td>';
												$order_date .= '<td class="ptdt_cancelled_item_table_td"><span class="dashicons dashicons-dismiss ptdt-dlt-fm-cancelled '.esc_attr($cancel_disable).'" data-item_id="'.esc_attr($item_id).'" data-item_date="'.esc_attr($date).'" data-item_price="'.esc_attr($price_value).'" data-item_qty="'.esc_attr($qty).'"></span></td>';
											$order_date .= '</tr>';
										} 
									}
								}

							$order_date .= '</table>';
							$order_date .= '<button type="button" class="button date_specific_refund date_specific_refund_'. esc_attr($item_id).'">'.esc_html__('Refund', 'schedule-delivery-for-woocommerce-products').'</button>';
							$order_date .= '<button type="button" class="button date_specific_cancel date_specific_cancel_'. esc_attr($item_id).'">'.esc_html__('Cancel', 'schedule-delivery-for-woocommerce-products').'</button>';
						$order_date .= '</div>';
					$order_date .= '</div>';
				}
				
				$html_tags = $this->allowed_html_tags();
				echo wp_kses($order_date, $html_tags);
			}
		    return $item_id;
		}

		/**
		 * Function for order status completed(Ajax function).
		 */
		public function order_status_completed() {
			if(check_ajax_referer('order-status-completed','nonce')) {
				$capability = THSDF_Utils::thsdf_capability();
				if(!current_user_can($capability)){
					wp_die();
				}
				global $current_user;
				$delivery_date = isset($_POST['item_date']) ? sanitize_text_field($_POST['item_date']) : '';
				$delivery_item_id = isset($_POST['item_id']) ? absint($_POST['item_id']) : '';
				$blog_title = get_bloginfo();
				$order_id = $this->get_order_id_by_order_item_id($delivery_item_id);
				$order = new WC_Order($order_id);
				$current_email = $current_user->user_email;
				$billing_email = $order->get_billing_email();
				$product_delivery_info = array();
				$ptdt_delivery_date = wc_get_order_item_meta($delivery_item_id, THSDF_Utils::ORDER_KEY_DELIVERY_DATE, 'true');
				if(!empty($ptdt_delivery_date) && is_array($ptdt_delivery_date)) {
					foreach ($ptdt_delivery_date as $key => $value) {
						if($key == $delivery_date) {
								$product_delivery_info[$key] = array(
									'quantity' 		=> isset($value['quantity']) ? $value['quantity'] : '',
									'status'		=> '4'
								);
							$quantity = $value['quantity'];
							//$this->send_date_specific_item_delivery_email($order_id, $delivery_item_id, $key, $quantity);
						} else {
								$product_delivery_info[$key] = array(
									'quantity' 		=> isset($value['quantity']) ? $value['quantity'] : '',
									'status'		=> isset($value['status']) ? $value['status'] : ''
								);
						}
					}
				}
				if(!empty($product_delivery_info)) {
				    wc_update_order_item_meta($delivery_item_id,  THSDF_Utils::ORDER_KEY_DELIVERY_DATE,  $product_delivery_info);   
				}
	        	exit;
	        }
        }

        /**
		 * Function for order status deleted(Ajax function).
		 */
        public function order_status_deleted() {
        	if(check_ajax_referer('order-status-canceled','nonce')) {
        		$capability = THSDF_Utils::thsdf_capability();
				if(!current_user_can($capability)){
					wp_die();
				}
				$new_qty = '';
				$delivery_item_id = isset($_POST['item_id']) ? absint($_POST['item_id']) : '';
				$delivery_date = isset($_POST['item_date']) ? sanitize_text_field($_POST['item_date']) : '';
				$date_create = date_create($delivery_date);
				$new_format_date = date_format($date_create,"d M Y ");

				$item_qty = isset($_POST['itemqty']) ? sanitize_text_field($_POST['itemqty']) : '';
				$item_price = isset($_POST['item_price']) ? sanitize_text_field($_POST['item_price']) : '';

				if(is_numeric($item_qty) && is_numeric($item_price)) {
					$refund_price = $item_price * $item_qty;
				} else{
					$refund_price = '0';
				}			
				$current_time = date("M d, Y @ h:i a");

				// Update db.
				$product_delivery_info = array();
				$ptdt_delivery_date = wc_get_order_item_meta($delivery_item_id, THSDF_Utils::ORDER_KEY_DELIVERY_DATE, 'true');

				if(!empty($ptdt_delivery_date) && is_array($ptdt_delivery_date)) {
					foreach ($ptdt_delivery_date as $key => $value) {
						if($key == $delivery_date) {
							$product_delivery_info[$key] = array(
								'quantity' 			=> isset($value['quantity']) ? $value['quantity'] : '',
								'status'			=> '2'
							);
						} else {
							$product_delivery_info[$key] = array(
								'quantity' 			=> isset($value['quantity']) ? $value['quantity'] : '',
								'status'			=> isset($value['status']) ? $value['status'] : ''
							);
						}
					}
				}
				if(!empty($product_delivery_info)) {
				    wc_update_order_item_meta($delivery_item_id,THSDF_Utils::ORDER_KEY_DELIVERY_DATE, $product_delivery_info);  
				}

				$d_status = '2';
				$order_status = $this->get_ptdt_order_status($d_status);
				if($d_status == '3') {
					$status_class = 'status_red';
					$completed = '-';
					$cancelled = '-';
					$not_active = 'not-active';
				} else if($d_status == '2') {
					$status_class = 'status_orange';
					$completed = '-';
					$cancelled = '-';
					$not_active = 'not-active';
				}

				$order_cancelled_table_view_mode1 = apply_filters('ptdt_order_cancelled_table_view_mode1', true);
				$cancelled_table  = '';
				if($order_cancelled_table_view_mode1) {
					$cancelled_table .= '<tr><input type="hidden" name="sd_order_item_id" value="'.esc_attr($delivery_item_id).'" class="sd_order_item_id">';
					$cancelled_table .= '<input type="hidden" name="order_item_date" value="'.esc_attr($delivery_date).'"></tr>';

					$cancelled_table .= '<tr class="delivery_cancelled_row delivery_cancelled_row_'. esc_attr($delivery_item_id).' delivery_cancelled_tr_'.esc_attr($delivery_date).'_'. esc_attr($delivery_item_id).'" >';

						$cancelled_table .= '<th scope="row" class="check-column">			
							<label class="screen-reader-text" for="cb-select-'.esc_attr($delivery_item_id).'">
								Select Order – '.esc_attr($current_time).'
							</label>
							<input id="cb-select-'.esc_attr($delivery_item_id).'" class="ptdt_order_checkbox order_checkbox_display cb-select-'.esc_attr($delivery_item_id).'" type="checkbox" name="product_order[]" value="'.esc_attr($delivery_item_id).'" data-qty="'.esc_attr($item_qty).'" data-date="'.esc_attr($delivery_date).'" checked>
							<div class="locked-indicator">
								<span class="locked-indicator-icon" aria-hidden="true"></span>
								<span class="screen-reader-text">
									“Order – '.esc_attr($current_time).'” is locked				
								</span>
							</div>
						</th>';

						$cancelled_table .= '<td class="ptdt_cancelled_item_table_td">'.esc_html__($new_format_date, 'schedule-delivery-for-woocommerce-products').'</td>';
						$cancelled_table .= '<td class="ptdt_cancelled_item_table_td item_qty_'.esc_attr($delivery_item_id).'">'.esc_attr($item_qty).'<input type="hidden" name="cancel_item_qty" value="'.esc_attr($item_qty).'"></td>';
						$cancelled_table .= '<td class="ptdt_cancelled_item_table_td"><span class="plan_delivery_dls_status '.esc_attr($status_class).' ptdt_delivery_status_'.esc_attr($delivery_date).'_'. esc_attr($delivery_item_id).'">'.esc_html__($order_status, 'schedule-delivery-for-woocommerce-products').'</span></td>';
						$cancelled_table .= '<td class="ptdt_cancelled_item_table_td">'.wc_price($refund_price).'</td>';
						$cancelled_table .= '<td class="ptdt_cancelled_item_table_td"><span class="dashicons dashicons-dismiss ptdt-dlt-fm-cancelled" data-item_id="'.esc_attr($delivery_item_id).'" data-item_date="'.esc_attr($delivery_date).'" data-item_price="'.esc_attr($item_price).'" data-item_qty="'.esc_attr($item_qty).'"></span></td>';
					$cancelled_table .= '</tr>';
				}

				$html_tags = $this->allowed_html_tags();
				echo wp_kses($cancelled_table, $html_tags);
	        	exit;
	        }
		}

		/**
		 * Function for order date specific refund(Ajax function).
		 */
		public function order_date_specific_refund() {
			if(check_ajax_referer('order-date-specific-refund','nonce')) {
				$capability = THSDF_Utils::thsdf_capability();
				if(!current_user_can($capability)){
					wp_die();
				}
				$delivery_item_id = isset($_POST['item_id']) ?  absint($_POST['item_id']) : '';
				$delivery_date = isset($_POST['item_date']) ? wc_clean($_POST['item_date']) : array();
				$current_time = date('Y-m-d H:i A');		
				$product_delivery_info = array();
				$ptdt_delivery_date = wc_get_order_item_meta($delivery_item_id, THSDF_Utils::ORDER_KEY_DELIVERY_DATE, 'true');
				$deleted_items = array();
				if(!empty($ptdt_delivery_date) && is_array($ptdt_delivery_date)) {
					foreach ($ptdt_delivery_date as $key => $value) {
						if(in_array($key, $delivery_date)) {
							$product_delivery_info[$key] = array(
								'quantity' 		=> isset($value['quantity']) ? $value['quantity'] : '',
								'status'		=> '5'
							);
							$deleted_items[] = array(
								'date' 			=> $key,
								'quantity'		=> isset($value['quantity']) ? $value['quantity'] : '',
								'deleted_time'	=> $current_time
							); 
						} else if($value['status'] === '5') {
							$product_delivery_info[$key] = array(
								'quantity' 		=> isset($value['quantity']) ? $value['quantity'] : '',
								'status'		=> '2'
							);
						} else {
							$product_delivery_info[$key] = array(
								'quantity' 		=> isset($value['quantity']) ? $value['quantity'] : '',
								'status'		=> isset($value['status']) ? $value['status'] : ''
							);					
						}
					}
				}
				if(!empty($deleted_items)) {
					wc_update_order_item_meta($delivery_item_id,THSDF_Utils::ORDER_DELETED_ITEM,$deleted_items); 
				}
				if(!empty($product_delivery_info)) {
				    wc_update_order_item_meta($delivery_item_id,THSDF_Utils::ORDER_KEY_DELIVERY_DATE,$product_delivery_info);  
				}
				exit();
			}
		}

		/**
		 * Refund creation function.
		 *		 
         * @param Integer $refund_id The refund id
         * @param array $args The passing arguments
		 */
		public function woocommerce_refund_created($refund_id, $args) {
			$line_items = array();
			$capability = THSDF_Utils::thsdf_capability();
			if(!current_user_can($capability)){
				wp_die();
			}
			if(!empty($args) && is_array($args)) {
				foreach ($args as $key => $value) {
					if($key == 'line_items') {
						$line_items[] = $value;
					}
				}
			}
			// Update db.
			if(!empty($line_items) && is_array($line_items)) {
				foreach($line_items as $item_id => $items_data) {
					if(!empty($items_data) && is_array($items_data)) {
						foreach($items_data as $data_k => $data_v) {
							if($data_v['refund_total'] !== '0') {
								$ptdt_delivery_date = wc_get_order_item_meta($data_k, THSDF_Utils::ORDER_KEY_DELIVERY_DATE, 'true');								
								$product_delivery_info = array();
								if(!empty($ptdt_delivery_date) && is_array($ptdt_delivery_date)) {
									foreach ($ptdt_delivery_date as $key => $value) {
										if($value['status'] == '5') {
											$product_delivery_info[$key] = array(
												'quantity' 	=> isset($value['quantity']) ? $value['quantity'] : '',
												'status'	=> '3'
											);
										} else {
											$product_delivery_info[$key] = array(
												'quantity' 		=> isset($value['quantity']) ? $value['quantity'] : '',
												'status'		=> isset($value['status']) ? $value['status'] : ''
											);
										}
									}
								}						
								if(!empty($product_delivery_info)) {
									wc_update_order_item_meta($data_k,THSDF_Utils::ORDER_KEY_DELIVERY_DATE,$product_delivery_info);  
								}
							}					
						}
					}
				}
			}
		}

		/**
		 * Function for get the status values.
		 *
         * @param string $status_value The status value
         *
         * @return string
		 */
		public function get_ptdt_order_status($status_value) {
			$all_status = array(
				'1' => "In Progress",
				'2' => "Pending",
				'3' => "Refunded",
				'4' => "Completed",
				'5' => "Pending",
				'6'	=> "User Hold",
			);
			$order_status = '';
			if(!empty($all_status) && is_array($all_status)) {
				foreach($all_status as $s_key => $status) {
					if($status_value == $s_key) {
					 $order_status = $status; 
					}
				}
			}
			return $order_status;
		}

		/**
		 * Function for the ordered item delete from the cancelled table(Ajax function).
		 */
		public function ordered_item_delete_from_cancelled_table() {
			if(check_ajax_referer('delete-from-cancelled-table','nonce')) {
				$capability = THSDF_Utils::thsdf_capability();
				if(!current_user_can($capability)) {
					wp_die();
				}
				$item_id = isset($_POST['item_id']) ? absint($_POST['item_id']) : '';
				$delivery_date = isset($_POST['item_date']) ? sanitize_text_field($_POST['item_date']) : '';
				$item_price = isset($_POST['item_price']) ? sanitize_text_field($_POST['item_price']) : '';
				$item_qty = isset($_POST['item_qty']) ? absint($_POST['item_qty']) : '';
				$date_create = date_create($delivery_date);
				$new_format_date = date_format($date_create,"d M Y ");

				// Update db.
				if(($item_id != '') && ($delivery_date != '')) {
					$ptdt_delivery_data = wc_get_order_item_meta($item_id, THSDF_Utils::ORDER_KEY_DELIVERY_DATE, 'true');
					if(!empty($ptdt_delivery_data) && is_array($ptdt_delivery_data)) {
						foreach ($ptdt_delivery_data as $key => $value) {
							if($delivery_date == $key) {
								$product_delivery_info[$key] = array(
									'quantity' 		=> isset($value['quantity']) ? $value['quantity'] : '',
									'status'		=> '1'
								);
							} else {
								$product_delivery_info[$key] = array(
									'quantity' 		=> isset($value['quantity']) ? $value['quantity'] : '',
									'status'		=> isset($value['status']) ? $value['status'] : ''
								);
							}
						}
					}
					if(!empty($product_delivery_info)) {
						wc_update_order_item_meta($item_id,THSDF_Utils::ORDER_KEY_DELIVERY_DATE,$product_delivery_info);  
					}
				}
				$d_status = '1';
				$order_status = $this->get_ptdt_order_status($d_status);
				$status_class = '';
				$completed = '';
				$cancelled = '';
				$not_active = '';

				if($d_status == '1') {
					$status_class = 'status_blue';
					$completed = 'Complete';
					$cancelled = 'Cancel';
					$not_active = '';
				} else if($d_status == '4') {
					$status_class = 'status_green';
					$completed = '-';
					$cancelled = '-';
					$not_active = 'not-active';
				}
				$order_date = '';
				$order_date .='<tr class="plan_delivery_dls_tr plan_delivery_dls_tr_'.esc_attr($delivery_date).'_'. esc_attr($item_id).'">';
					$order_date .='<td class="plan_delivery_dls_td">'.esc_html__($new_format_date, 'schedule-delivery-for-woocommerce-products').'</td>';
					$order_date .='<td class="plan_delivery_dls_td">'.esc_attr($item_qty).'</td>';

					$order_date .='<td class="plan_delivery_dls_td"><span class="plan_delivery_dls_status '.esc_attr($status_class).' ptdt_delivery_status_'.esc_attr($delivery_date).'_'. esc_attr($item_id).'">'.esc_html__($order_status, 'schedule-delivery-for-woocommerce-products').'</span></td>';

					if($d_status != '4' || $d_status != '3') {
						$order_date .= '<td class="plan_delivery_dls_td"><a href="" data-itemid ="'. esc_attr($item_id) .'" data-itemdate="'. esc_attr($delivery_date) .'" data-itemqty="'. esc_attr($item_qty) .'" data-price = "'.esc_attr($item_price).'" class="ptdt_delivery_completed delivery_completed_'.esc_attr($delivery_date).'_'. esc_attr($item_id).' '.esc_attr($not_active) .' "> '. esc_html__($completed, 'schedule-delivery-for-woocommerce-products').'</a>'. '/';

						$order_date .= '<a href="" data-itemid ="'. esc_attr($item_id) .'" data-itemdate="'. esc_attr($delivery_date) .'" data-itemqty="'. esc_attr($item_qty) .'" data-price = "'.esc_attr($item_price).'" class="ptdt_delivery_cancelled delivery_cancelled_'.esc_attr($delivery_date).'_'. esc_attr($item_id).' '.esc_attr($not_active) .'"> '.esc_html__($cancelled, 'schedule-delivery-for-woocommerce-products').'</a>
											</td>';										
					}
				$order_date .='</tr>';

				$html_tags = $this->allowed_html_tags();
				echo wp_kses($order_date, $html_tags);
				exit;
			}
		}

		/**
		 * Function for update order satus.
		 *
         * @param array $order The order details
         *
         * @return void
		 */
			
		public function ptdt_update_woo_order_status($order) {
			$capability = THSDF_Utils::thsdf_capability();
			if(!current_user_can($capability)) {
				wp_die();
			}
			$finished = 0;
			if(!empty($order->get_items()) && is_array($order->get_items())) {
				foreach($order->get_items() as $item_id => $item) {
					$ptdt_delivery_data = wc_get_order_item_meta($item_id, THSDF_Utils::ORDER_KEY_DELIVERY_DATE, 'true');
					$finished_key = array();
					$not_finished = array();
					if(!empty($ptdt_delivery_data) && is_array($ptdt_delivery_data)) {
						foreach ($ptdt_delivery_data as $key => $value) {
							$status_value = isset($value['status']) ? $value['status'] : '';
							if($status_value == 3 || $status_value == 4) {
								$finished_key[] = $key;
							} else{
								$not_finished[] = $key;
							}
						}
						if(!empty($not_finished)) {
							$finished++;
						}
					}			
				}
			}
			if($finished == 0) {
				if(!empty($order->get_items()) && is_array($order->get_items())) {
					foreach($order->get_items() as $item_id => $item) {
						$ptdt_delivery_data = wc_get_order_item_meta($item_id, THSDF_Utils::ORDER_KEY_DELIVERY_DATE, 'true');
						if(!empty($ptdt_delivery_data)) {
							$order->update_status('completed', '');
						}
					}
				}
			}		
		}

		/**
		 * Function for get the order id from the order item id.
		 *
         * @param integer $item_id The item id
         *
         * @return void
		 */
		public function get_order_id_by_order_item_id($item_id) {
		    global $wpdb;
		    return (int) $wpdb->get_var(
		   		$wpdb->prepare(
		        "SELECT order_id FROM {$wpdb->prefix}woocommerce_order_items WHERE order_item_id = %d",
		        $item_id
		     	)
		    );
		}

		/**
		 * Function for get allowed post tags.
         *
         * @return array
		 */
		public function allowed_html_tags(){
			global $allowedposttags;
			$html_tags = array (
				'input' => array(
			        'data-qty' 	=> true,
			        'data-date' => true,
			        'disabled' 	=> true,
			        'type'      => array(),
			        'name'      => array(),
			        'value'     => array(),
			        'checked'   => array(),
			        'class' 	=> array(),
			        'id' 		=> array(),
			    ),
			    'bdi' 	=> array(),
			    'a'   	=> array(			    
					'href'     => true,
					'rel'      => true,
					'rev'      => true,
					'name'     => true,
					'target'   => true,			    	
					'data-itemid' 	=> true,
					'data-itemdate' => true,
					'data-itemqty' 	=> true,
					'data-price' 	=> true,
					'class' 		=> array(),
					'download' => array(
						'valueless' => 'y',
					),
				),
				'div'	=> array(
					'align'    => true,
					'dir'      => true,
					'lang'     => true,
					'xml:lang' => true,
					'style'	   => true,
					'id' 	=> array(),
	    			'class' => array()
				),
				'h3'	=> array(
					'align' => true,
					'class' => array(),
				),
				'img'	=> array(
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
				'label'	=> array(
					'for' => true,
					'class' => true,
				),
				'span' 	=> array(
					'dir'      		=> true,
					'align'    		=> true,
					'lang'     		=> true,
					'xml:lang' 		=> true,
					'aria-hidden'	=> true,
					'data-item_id'	=> true,
					'data-item_date'=> true,
					'data-item_price' => true,
					'data-item_qty' => true,
					'class' 		=> array(),
				),
				'td' 	=> array(
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
					'class' 	=> array(),
					'id' 		=> array(),
				),
				'th'	=> array(
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
					'class' 	=>  array(),
					'id' 		=> array(),
				),
			);

			$allowed_html_tags = array_merge($allowedposttags, $html_tags);
			return $allowed_html_tags;			
		}
	}
endif;