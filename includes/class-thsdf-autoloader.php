<?php
/**
 * Auto-loads the required dependencies for this plugin.
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

if(!class_exists('THSDF_Autoloader')) :

	/**
     * The plugin autoloader class.
     */
	class THSDF_Autoloader {
		private $include_path = '';		
		private $class_path = array();
		
		/**
         * constructer.
         */
		public function __construct() {
			$this->include_path = untrailingslashit(THSDF_PATH);		
			if(function_exists("__autoload")) {
				spl_autoload_register("__autoload");
			}
			spl_autoload_register(array($this, 'autoload'));
		}

		/**
         * Include a class file.
         *
         * @param string $path the path variable
         *
         * @return string
         */
		private function load_file($path) {
			if ($path && is_readable($path)) {
				require_once($path);
				return true;
			}
			return false;
		}
		
		/**
         * Class name to file name.
         *
         * @param string $class the class name
         *
         * @return string
         */
		private function get_file_name_from_class($class) {
			return 'class-' . str_replace('_', '-', $class) . '.php';
		}
		
		/**
         * The autoload function.
         *
         * @param string $class the class name
         *
         * @return void
         */
		public function autoload($class) {
			$class = strtolower($class);
			$file  = $this->get_file_name_from_class($class);
			$path  = '';
			$file_path  = '';

			if (isset($this->class_path[$class])) {
				$file_path = $this->include_path . '/' . $this->class_path[$class];
			} else {
				if (strpos($class, 'thsdf_admin') === 0) {
					$path = $this->include_path . '/admin/';
				} elseif (strpos($class, 'thsdf_public') === 0) {
					$path = $this->include_path . '/public/';
				} elseif (strpos($class, 'thsdf_utils') === 0) {
					$path = $this->include_path . '/includes/utils/';
				}elseif (strpos($class, 'thsdf_page') === 0) {
					$path = $this->include_path . '/includes/model/';
				} else {
					$path = $this->include_path . '/includes/';
				}
				$file_path = $path . $file;
			}
			
			if(empty($file_path) || (!$this->load_file($file_path) && strpos($class, 'thsdf_') === 0)) {
				$this->load_file($this->include_path . $file);
			}
		}
	}
endif;
new THSDF_Autoloader();