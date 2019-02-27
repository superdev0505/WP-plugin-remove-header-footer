<?php
if (!defined('VGPL_ANY_PREMIUM_ADDON')) {
	define('VGPL_ANY_PREMIUM_ADDON', true);
}
if (!class_exists('VG_Page_Template_Hide_Elements')) {

	class VG_Page_Template_Hide_Elements {

		static private $instance = false;
		var $textname = null;

		private function __construct() {
			
		}

		function init() {
			$main = VG_Page_Layouts_Instance();
			$this->textname = $main->textname;
			add_filter('vg_page_layouts/post_fields', array($this, 'add_post_settings'), 10, 2);
			add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
		}

		function enqueue_assets() {
			$instance = VG_Page_Layouts_Instance();
			if (!$instance->is_page_allowed()) {
				return;
			}
			wp_enqueue_script('vg-page-layouts-premium-elements-js', plugins_url('/assets/js/hide-premium-elements.js', VGPL_MAIN_FILE), array('jquery'), '1.0.0', true);
		}


		function add_post_settings($settings_keys) {
			$settings_keys['vg_hide_header'] = array(
					'label' => __('Hide header', VG_Page_Layouts_Instance()->textname ),
					'description' => '',
					'field_type' => 'checkbox', // checkbox , text , textarea
					'allow_html' => false,
					'section' => 'normal', // normal , advanced
					 'is_disabled' => false,
					);
			$settings_keys['vg_hide_footer'] = array(
					'label' => __('Hide footer', VG_Page_Layouts_Instance()->textname ),
					'description' => '',
					'field_type' => 'checkbox', // checkbox , text , textarea
					'allow_html' => false,
					'section' => 'normal', // normal , advanced
					 'is_disabled' => false,
					);
			$settings_keys['vg_remove_space'] = array(
					'label' => sprintf(__('Remove space.', $this->textname)),
					'description' => '',
					'field_type' => 'text', // checkbox , text , textarea
					'allow_html' => false,
					'section' => 'normal', // normal , advanced
					'is_disabled' => false,
					);
			$settings_keys['vg_header_selector'] = array(
					'label' => __('Header css selector', VG_Page_Layouts_Instance()->textname ),
					'description' => '',
					'field_type' => 'text', // checkbox , text , textarea
					'allow_html' => false,
					'section' => 'advanced', // normal , advanced
					 'is_disabled' => false,
					);
			$settings_keys['vg_footer_selector'] = array(
					'label' => __('Footer css selector', VG_Page_Layouts_Instance()->textname ),
					'description' => '',
					'field_type' => 'text', // checkbox , text , textarea
					'allow_html' => false,
					'section' => 'advanced', // normal , advanced
					 'is_disabled' => false,
					);
			return $settings_keys;
		}

		/**
		 * Creates or returns an instance of this class.
		 *
		 * @return  Foo A single instance of this class.
		 */
		static function get_instance() {
			if (null == VG_Page_Template_Hide_Elements::$instance) {
				VG_Page_Template_Hide_Elements::$instance = new VG_Page_Template_Hide_Elements();
				VG_Page_Template_Hide_Elements::$instance->init();
			}
			return VG_Page_Template_Hide_Elements::$instance;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

}

if (!function_exists('VG_Page_Template_Hide_Elements_Obj')) {

	function VG_Page_Template_Hide_Elements_Obj() {
		return VG_Page_Template_Hide_Elements::get_instance();
	}

}

VG_Page_Template_Hide_Elements_Obj();