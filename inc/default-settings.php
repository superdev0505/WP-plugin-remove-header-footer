<?php

if (!defined('VGPL_ANY_PREMIUM_ADDON')) {
	define('VGPL_ANY_PREMIUM_ADDON', true);
}
if (!class_exists('VG_Page_Template_Default_Settings')) {

	class VG_Page_Template_Default_Settings {

		static private $instance = false;
		var $textname = null;

		private function __construct() {
			
		}

		function init() {
			$main = VG_Page_Layouts_Instance();
			$this->textname = $main->textname;
			add_filter('vg_page_layouts/post_fields', array($this, 'add_post_settings'), 10, 2);
			add_filter('vg_page_layouts/post_settings', array($this, 'filter_post_settings'), 10, 2);
		}

		function filter_post_settings($settings, $post_id) {
			$post_settings = array_filter($settings);
			$all_meta = get_post_meta($post_id);
			// If the post has ANY setting and it´s currently version 1.1.0 and the post doesn´t have the 
			// meta key vg_custom_settings, enable custom settings.
			if( count( $post_settings) > 0 && !isset($all_meta['vg_custom_settings']) &&  version_compare(VG_Page_Layouts_Instance()->version, '1.1.0', '>=') === 0 ){
				update_post_meta($post_id, 'vg_custom_settings', 'yes');
			}
			
			
			$custom_settings = get_post_meta($post_id, 'vg_custom_settings', true);

			if ($custom_settings) {
				return $settings;
			}
			$post_type = get_post_type($post_id);
			$options = get_option(VGPL_KEY, array());

			$out = array();
			foreach ($options as $key => $value) {
				if (strpos($key, $post_type) === 0) {
					$out[str_replace($post_type . '_', '', $key)] = $value;
				}
			}
			return $out;
		}

		function add_post_settings($settings_keys) {
			$new_settings = array();
			$new_settings['vg_custom_settings'] = array(
				'label' => __('Use custom settings for this page?', VG_Page_Layouts_Instance()->textname),
				'description' => 'If this option is disabled, we will use the values from the settings page and ignore the fields below.',
				'field_type' => 'checkbox', // checkbox , text , textarea
				'allow_html' => false,
				'section' => 'normal', // normal , advanced
				'is_disabled' => false,
			);

			$settings_keys = wp_parse_args($settings_keys, $new_settings);
			return $settings_keys;
		}

		/**
		 * Creates or returns an instance of this class.
		 *
		 * @return  Foo A single instance of this class.
		 */
		static function get_instance() {
			if (null == VG_Page_Template_Default_Settings::$instance) {
				VG_Page_Template_Default_Settings::$instance = new VG_Page_Template_Default_Settings();
				VG_Page_Template_Default_Settings::$instance->init();
			}
			return VG_Page_Template_Default_Settings::$instance;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

}

if (!function_exists('VG_Page_Template_Default_Settings_Obj')) {

	function VG_Page_Template_Default_Settings_Obj() {
		return VG_Page_Template_Default_Settings::get_instance();
	}

}

VG_Page_Template_Default_Settings_Obj();
