<?php
/*
  Plugin Name: WP Page Templates
  Plugin URI: https://wppagetemplates.com
  Description: Hide and Show header and footer of the page
  Version: 1.1.3
  Author: GreenJayMedia
  Author URI:  https://greenjaymedia.com
  Author Email: josevega@vegacorp.me
  License:

  Copyright 2019 JoseVega (josevega@vegacorp.me)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

 */


define('VGPL_ANY_PREMIUM_ADDON', false);
if (!defined('VGPL_MAIN_FILE')) {
	define('VGPL_MAIN_FILE', __FILE__);
}
if (!defined('VGPL_DIR')) {
	define('VGPL_DIR', __DIR__);
}

if (!defined('VGPL_KEY')) {
	define('VGPL_KEY', 'vg_page_layout');
}
if (!defined('VGPL_PLUGIN_NAME')) {
	define('VGPL_PLUGIN_NAME', 'WP Templates');
}

if (!class_exists('VG_Page_Layouts')) {

	class VG_Page_Layouts {

		static private $instance = false;
		var $version = '1.1.5';
		var $textname = 'vg_page_layout';
		var $plugin_dir = __DIR__;
		var $allowed_post_types = null;

		var $demo_link = null;
		var $vg_plugin_sdk = null;
		var $args = null;

		private function __construct() {
			
		}

		function init() {
			// $this->buy_link = 'https://wppagetemplates.com/go-page-builder-wporg';
			$this->args = array(
				'main_plugin_file' => __FILE__,
				'show_welcome_page' => false,
				'welcome_page_file' => $this->plugin_dir . '/views/welcome-page-content.php',
				'upgrade_message_file' => $this->plugin_dir . '/views/upgrade-message.php',
				'logo' => plugins_url('/assets/imgs/logo.png', __FILE__),
				// 'buy_link' => $this->buy_link,
				'plugin_name' => VGPL_PLUGIN_NAME,
				'plugin_prefix' => 'wcpt_',
				'show_whatsnew_page' => false,
				'whatsnew_pages_directory' => $this->plugin_dir . '/views/whats-new/',
				'plugin_version' => $this->version,
				'plugin_options' => get_option('vg_page_layout_in_use', false),
			);

			//$this->vg_plugin_sdk = new VG_Freemium_Plugin_SDK($this->args);
			add_action('init', array($this, 'late_init'), 99);
			//add_action('widgets_init', array($this, 'register_sidebars'));
		}

		function late_init() {

			$this->allowed_post_types = apply_filters('vg_page_layout/allowed_post_types', array(
				'page'
			));

			add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
			add_action('add_meta_boxes', array($this, 'register_metabox'));
			add_action('save_post', array($this, 'save_metabox'), 10, 2);

			// Add placeholder tag to the_content. We´ll use it with JS to detect the entry-content parent.
			add_filter('the_content', array($this, 'add_layout_markers_to_content'));
			add_filter('the_excerpt', array($this, 'add_layout_markers_to_content'));


			// add_action('admin_menu', array($this, 'register_menu'));

			// add_action('vg_page_layout/metabox/after_content', array($this, 'add_upgrade_message_to_metabox'), 30);
		}

		function add_upgrade_message_to_metabox($post) {
			if (defined('VGPL_ANY_PREMIUM_ADDON') && VGPL_ANY_PREMIUM_ADDON) {
				return;
			}
			?>
			<hr/>
			<?php require $this->plugin_dir . '/views/upgrade-message.php'; ?>
			
			<?php if (!empty($this->demo_link)) { ?>
				<a href="<?php echo esc_url($this->demo_link); ?>" class="button" target="_blank"><?php _e('View demo', $this->textname); ?></a>
			<?php } ?>
			<?php
		}

		function register_menu() {
			add_menu_page($this->args['plugin_name'], $this->args['plugin_name'], 'manage_options', 'wcpt_welcome_page', array($this->vg_plugin_sdk, 'render_welcome_page'));
		}

		function register_sidebars() {
			register_sidebar(array(
				'name' => __('Page layout: Left sidebar', 'textdomain'),
				'id' => 'vg-page-layout-left-sidebar',
				'before_widget' => '<div id="%1$s" class="widget %2$s">',
				'after_widget' => '</div>',
				'before_title' => '<h2 class="widgettitle">',
				'after_title' => '</h2>',
			));
			register_sidebar(array(
				'name' => __('Page layout: Right sidebar', 'textdomain'),
				'id' => 'vg-page-layout-right-sidebar',
				'before_widget' => '<div id="%1$s" class="widget %2$s">',
				'after_widget' => '</div>',
				'before_title' => '<h2 class="widgettitle">',
				'after_title' => '</h2>',
			));
			register_sidebar(array(
				'name' => __('Page layout: Above content sidebar', 'textdomain'),
				'id' => 'vg-page-layout-above-content-sidebar',
				'before_widget' => '<div id="%1$s" class="widget %2$s">',
				'after_widget' => '</div>',
				'before_title' => '<h2 class="widgettitle">',
				'after_title' => '</h2>',
			));
			register_sidebar(array(
				'name' => __('Page layout: Below content sidebar', 'textdomain'),
				'id' => 'vg-page-layout-below-content-sidebar',
				'before_widget' => '<div id="%1$s" class="widget %2$s">',
				'after_widget' => '</div>',
				'before_title' => '<h2 class="widgettitle">',
				'after_title' => '</h2>',
			));
		}

		function get_registered_settings() {
			
			$settings['vg_hide_header'] = array(
				'label' => sprintf(__('Hide header.', $this->textname)),
				'description' => '',
				'field_type' => 'checkbox', // checkbox , text , textarea
				'allow_html' => false,
				'section' => 'normal', // normal , advanced
				'is_disabled' => true,
			);
			$settings['vg_hide_footer'] = array(
				'label' => sprintf(__('Hide footer.', $this->textname)),
				'description' => '',
				'field_type' => 'checkbox', // checkbox , text , textarea
				'allow_html' => false,
				'section' => 'normal', // normal , advanced
				'is_disabled' => true,
			);

			$settings['vg_remove_space'] = array(
				'label' => sprintf(__('Remove space.', $this->textname)),
				'description' => '',
				'field_type' => 'text', // checkbox , text , textarea
				'allow_html' => false,
				'section' => 'normal', // normal , advanced
				'is_disabled' => true,
			);

			$meta_keys = apply_filters('vg_page_layouts/post_fields', $settings);

			$defaults = array(
				'label' => '',
				'description' => '',
				'field_type' => 'text', // checkbox , text , textarea
				'allow_html' => false,
				'section' => 'normal', // normal , advanced
				'is_disabled' => false,
			);

			$out = array();

			foreach ($meta_keys as $field_key => $field) {
				$out[$field_key] = wp_parse_args($field, $defaults);
			}

			return $out;
		}

		function get_post_settings($post_id) {
			$meta_keys = array_keys($this->get_registered_settings());

			$out = array();

			foreach ($meta_keys as $meta_key) {
				$out[$meta_key] = get_post_meta($post_id, $meta_key, true);
			}
			return apply_filters('vg_page_layouts/post_settings', $out, $post_id);
		}

		function render_section_fields($post_id, $section = 'normal') {

			$registered_fields = wp_list_filter($this->get_registered_settings(), array(
				'section' => $section,
			));
			$post_settings = $this->get_post_settings($post_id);

			foreach ($registered_fields as $meta_key => $field) {

				if (!isset($post_settings[$meta_key])) {
					$post_settings[$meta_key] = '';
				}
				$disabled = ( $field['is_disabled']) ? ' disabled ' : '';

				if ($field['field_type'] === 'none') {
					?>
					<?php if (!empty($field['description'])) { ?>
						<?php echo $field['description']; ?>
					<?php } ?><br/>
					<?php
				}
				if ($field['field_type'] === 'textarea') {
					?>
					<?php if (!empty($field['label'])) { ?>
						<h3><?php echo $field['label']; ?></h3>
					<?php } ?>
					<?php if (!empty($field['description'])) { ?>
						<p><?php echo $field['description']; ?></p>
					<?php } ?>
					<textarea <?php echo $disabled; ?> name="<?php echo $meta_key; ?>" id="<?php echo $meta_key; ?>" style="width: 100%; height: 200px;"><?php echo $post_settings[$meta_key]; ?></textarea><br/>
					<?php
				}
				if ($field['field_type'] === 'text') {
					?>
					<?php if (!empty($field['label'])) { ?>
						<label for="<?php echo $meta_key; ?>"><?php echo $field['label']; ?></label><br/>
					<?php } ?>
					<input <?php echo $disabled; ?> type="text" name="<?php echo $meta_key; ?>" id="<?php echo $meta_key; ?>" value="<?php echo esc_attr($post_settings[$meta_key]); ?>"><br/>
					<?php
				}
				if ($field['field_type'] === 'checkbox') {
					?>
					<label for="<?php echo $meta_key; ?>">
						<input <?php echo $disabled; ?> type="checkbox" name="<?php echo $meta_key; ?>" id="<?php echo $meta_key; ?>" <?php checked('yes', $post_settings[$meta_key]); ?> value="yes"> 
						<?php echo $field['label']; ?>
					</label>
					<?php if (!empty($field['description'])) { ?>
						<p><?php echo $field['description']; ?></p>
					<?php } ?>
					<br/>
					<?php
				}
			}
		}

		public function add_layout_markers_to_content($content) {
			$post = get_queried_object();
		
			if( !$this->is_page_allowed()){
				return $content;
			}

			$settings = array_filter($this->get_post_settings($post->ID));

			if (empty($settings)) {
				return $content;
			}

			extract($settings);


			if (!empty($vg_full_width)) {
				$vg_left_sidebar = null;
				$vg_right_sidebar = null;
			}


			if (!empty($vg_left_sidebar)) {
				$sidebar_key = 'vg-page-layout-left-sidebar';
				$position = 'left';
				ob_start();
				require $this->plugin_dir . '/views/sidebar.php';
				$left_sidebar_html = ob_get_clean();
			} else {
				$left_sidebar_html = '';
			}


			if (!empty($vg_right_sidebar)) {
				$sidebar_key = 'vg-page-layout-right-sidebar';
				$position = 'right';
				ob_start();
				require $this->plugin_dir . '/views/sidebar.php';
				$right_sidebar_html = ob_get_clean();
			} else {
				$right_sidebar_html = '';
			}


			if (!empty($vg_above_content_sidebar)) {
				$sidebar_key = 'vg-page-layout-above-content-sidebar';
				$position = '';
				ob_start();
				require $this->plugin_dir . '/views/sidebar.php';
				$above_content_sidebar_html = ob_get_clean();
			} else {
				$above_content_sidebar_html = '';
			}


			if (!empty($vg_below_content_sidebar)) {
				$sidebar_key = 'vg-page-layout-below-content-sidebar';
				$position = '';
				ob_start();
				require $this->plugin_dir . '/views/sidebar.php';
				$below_content_sidebar_html = ob_get_clean();
			} else {
				$below_content_sidebar_html = '';
			}

			$final_content = $above_content_sidebar_html . $left_sidebar_html . $content . $right_sidebar_html . $below_content_sidebar_html;
			$settings_to_attrs = '';

			$frontend_settings_exclude = apply_filters('vg_page_layout/frontend_settings_exclude', array(), $settings);
			foreach ($settings as $setting_key => $setting_value) {

				if (in_array($setting_key, $frontend_settings_exclude)) {
					continue;
				}
				$friendly_key = str_replace(array('vg_', '_'), array('', '-'), $setting_key);
				$settings_to_attrs .= ' data-' . esc_attr($friendly_key) . '="' . esc_attr($setting_value) . '" ';
			}

			$final_content .= '<span class="vg-page-layout-placeholder" ' . $settings_to_attrs . ' style="display: none;"></span>';
			return $final_content;
		}

		function register_metabox() {
			$allowed_post_types = $this->allowed_post_types;

			foreach ($allowed_post_types as $post_type) {
				add_meta_box('vg-page-layouts', esc_html__('Page layout', 'text-domain'), array($this, 'render_metabox'), $post_type, 'side');
			}
		}

		function render_metabox($post) {
			require $this->plugin_dir . '/views/metabox.php';
		}
		
		function is_page_allowed() {
    
			$page_object = get_queried_object();
			
			$allowed = true;
			if( !is_object($page_object) || empty($page_object->post_type) || !in_array($page_object->post_type, $this->allowed_post_types)){
				$allowed = false;
			}
			return $allowed;
}

		function enqueue_assets() { 			
			if( !$this->is_page_allowed()){
				return;
			}
			wp_enqueue_script('vg-page-layouts-init-js', plugins_url('/assets/js/init.js', __FILE__), array('jquery'), $this->version, true);
			wp_enqueue_style('vg-page-layouts-styles', plugins_url('/assets/styles.css', __FILE__), null, $this->version);
		}

		public function save_metabox($post_id, $post) {
			// Add nonce for security and authentication.
			$nonce_name = isset($_POST['custom_nonce']) ? $_POST['custom_nonce'] : '';
			$nonce_action = $this->textname;

			// Check if nonce is set.
			if (!isset($nonce_name)) {
				return;
			}

			// Check if nonce is valid.
			if (!wp_verify_nonce($nonce_name, $nonce_action)) {
				return;
			}

			// Check if user has permissions to save data.
			if (!current_user_can('edit_post', $post_id)) {
				return;
			}

			// Check if not an autosave.
			if (wp_is_post_autosave($post_id)) {
				return;
			}

			// Check if not a revision.
			if (wp_is_post_revision($post_id)) {
				return;
			}

			update_option('vg_page_layout_in_use', 'yes');

			$settings = $this->get_registered_settings();

			if (!isset($_REQUEST['vg_full_width'])) {
				$_REQUEST['vg_full_width'] = '';
			}
			$full_width = sanitize_text_field($_REQUEST['vg_full_width']);
			if (!empty($full_width)) {
				$_REQUEST['vg_left_sidebar'] = '';
				$_REQUEST['vg_right_sidebar'] = '';
			}

			foreach ($settings as $setting_key => $field) {

				if (!isset($_REQUEST[$setting_key])) {
					$_REQUEST[$setting_key] = '';
				}

				if ($field['allow_html']) {
					$new_value = ( current_user_can('unfiltered_html')) ? $_REQUEST[$setting_key] : wp_kses_post($_REQUEST[$setting_key]);
				} else {
					$new_value = sanitize_text_field($_REQUEST[$setting_key]);
				}

				update_post_meta($post_id, $setting_key, $new_value);
			}

			do_action('vg_page_layouts/metabox_saved', $post_id, $post);
		}

		/**
		 * Creates or returns an instance of this class.
		 */
		static function get_instance() {
			if (null == VG_Page_Layouts::$instance) {
				VG_Page_Layouts::$instance = new VG_Page_Layouts();
				VG_Page_Layouts::$instance->init();
			}
			return VG_Page_Layouts::$instance;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

}

if (!function_exists('VG_Page_Layouts_Instance')) {

	function VG_Page_Layouts_Instance() {
		return VG_Page_Layouts::get_instance();
	}

}

VG_Page_Layouts_Instance();

require 'inc/hide-page-elements.php';
require 'inc/default-settings.php';